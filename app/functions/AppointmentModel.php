<?php

class AppointmentModel {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Generate appointment number
     */
    private function generateAppointmentNumber() {
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        $sequence = str_pad($row['count'] + 1, 3, '0', STR_PAD_LEFT);
        return "APT{$date}{$sequence}";
    }
    
    /**
     * Create new appointment
     */
    public function createAppointment($data) {
        try {
            // Validate required fields
            $required = ['pasien_id', 'dokter_id', 'appointment_date', 'keluhan'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }
            
            // Check doctor availability
            if (!$this->isDoctorAvailable($data['dokter_id'], $data['appointment_date'])) {
                throw new Exception("Dokter tidak tersedia pada waktu tersebut");
            }
            
            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();
            
            // Set default values
            $priority = $data['priority'] ?? 'normal';
            $estimatedDuration = $data['estimated_duration'] ?? 30;
            $ruangId = $data['ruang_id'] ?? null;
            $notes = $data['notes'] ?? '';
            $createdBy = $_SESSION['user']['id'] ?? 1;
            
            $query = "INSERT INTO appointments (
                appointment_number, pasien_id, dokter_id, ruang_id, 
                appointment_date, estimated_duration, priority, 
                keluhan, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "siiisisssi", 
                $appointmentNumber, $data['pasien_id'], $data['dokter_id'], 
                $ruangId, $data['appointment_date'], $estimatedDuration, 
                $priority, $data['keluhan'], $notes, $createdBy
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $appointmentId = mysqli_insert_id($this->conn);
                
                // Add to queue
                $this->addToQueue($appointmentId, $data['dokter_id']);
                
                // Send confirmation notification
                $this->scheduleNotification($appointmentId, 'confirmation');
                
                return [
                    'success' => true,
                    'appointment_id' => $appointmentId,
                    'appointment_number' => $appointmentNumber,
                    'message' => 'Appointment berhasil dibuat'
                ];
            } else {
                throw new Exception("Gagal membuat appointment: " . mysqli_error($this->conn));
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check doctor availability
     */
    public function isDoctorAvailable($doctorId, $appointmentDate) {
        $date = date('Y-m-d', strtotime($appointmentDate));
        $time = date('H:i:s', strtotime($appointmentDate));
        $dayOfWeek = date('N', strtotime($appointmentDate)); // 1=Monday, 7=Sunday
        
        // Check doctor schedule
        $scheduleQuery = "SELECT * FROM doctor_schedules 
                         WHERE dokter_id = ? AND day_of_week = ? AND is_active = 1";
        $stmt = mysqli_prepare($this->conn, $scheduleQuery);
        mysqli_stmt_bind_param($stmt, "ii", $doctorId, $dayOfWeek);
        mysqli_stmt_execute($stmt);
        $scheduleResult = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($scheduleResult) == 0) {
            return false; // Doctor doesn't work on this day
        }
        
        $schedule = mysqli_fetch_assoc($scheduleResult);
        
        // Check if time is within working hours
        if ($time < $schedule['start_time'] || $time > $schedule['end_time']) {
            return false;
        }
        
        // Check if time is during break
        if ($schedule['break_start'] && $schedule['break_end']) {
            if ($time >= $schedule['break_start'] && $time <= $schedule['break_end']) {
                return false;
            }
        }
        
        // Check existing appointments
        $appointmentQuery = "SELECT COUNT(*) as count FROM appointments 
                           WHERE dokter_id = ? AND DATE(appointment_date) = ? 
                           AND TIME(appointment_date) = ? AND status NOT IN ('cancelled', 'no_show')";
        $stmt = mysqli_prepare($this->conn, $appointmentQuery);
        mysqli_stmt_bind_param($stmt, "iss", $doctorId, $date, $time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        return $row['count'] == 0; // Available if no existing appointment
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots($doctorId, $date) {
        $dayOfWeek = date('N', strtotime($date)); // 1=Monday, 7=Sunday
        
        // Get doctor schedule
        $scheduleQuery = "SELECT * FROM doctor_schedules 
                         WHERE dokter_id = ? AND day_of_week = ? AND is_active = 1";
        $stmt = mysqli_prepare($this->conn, $scheduleQuery);
        mysqli_stmt_bind_param($stmt, "ii", $doctorId, $dayOfWeek);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            return []; // No schedule for this day
        }
        
        $schedule = mysqli_fetch_assoc($result);
        
        // Generate time slots
        $slots = [];
        $currentTime = strtotime($schedule['start_time']);
        $endTime = strtotime($schedule['end_time']);
        $slotDuration = $schedule['slot_duration'] * 60; // Convert to seconds
        
        while ($currentTime < $endTime) {
            $timeSlot = date('H:i:s', $currentTime);
            
            // Skip break time
            if ($schedule['break_start'] && $schedule['break_end']) {
                if ($timeSlot >= $schedule['break_start'] && $timeSlot < $schedule['break_end']) {
                    $currentTime += $slotDuration;
                    continue;
                }
            }
            
            // Check if slot is available
            if ($this->isDoctorAvailable($doctorId, $date . ' ' . $timeSlot)) {
                $slots[] = [
                    'time' => $timeSlot,
                    'formatted_time' => date('H:i', $currentTime),
                    'available' => true
                ];
            } else {
                $slots[] = [
                    'time' => $timeSlot,
                    'formatted_time' => date('H:i', $currentTime),
                    'available' => false
                ];
            }
            
            $currentTime += $slotDuration;
        }
        
        return $slots;
    }
    
    /**
     * Add appointment to queue
     */
    private function addToQueue($appointmentId, $doctorId) {
        // Get current queue position
        $queueQuery = "SELECT COALESCE(MAX(queue_position), 0) + 1 as next_position 
                      FROM queue_status WHERE dokter_id = ? AND status = 'waiting'";
        $stmt = mysqli_prepare($this->conn, $queueQuery);
        mysqli_stmt_bind_param($stmt, "i", $doctorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $nextPosition = $row['next_position'];
        
        // Calculate estimated wait time (30 minutes per patient)
        $estimatedWaitTime = ($nextPosition - 1) * 30;
        
        $insertQuery = "INSERT INTO queue_status (dokter_id, appointment_id, queue_position, estimated_wait_time) 
                       VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "iiii", $doctorId, $appointmentId, $nextPosition, $estimatedWaitTime);
        mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get appointments for today
     */
    public function getTodayAppointments($doctorId = null) {
        // Check if appointments table exists
        $tableCheck = mysqli_query($this->conn, "SHOW TABLES LIKE 'appointments'");
        if (mysqli_num_rows($tableCheck) == 0) {
            // Table doesn't exist, return empty array
            return [];
        }
        
        $query = "SELECT a.*, p.nama_pasien, p.telephone, d.nama_dokter, r.nama_ruang,
                         q.queue_position, q.estimated_wait_time, q.status as queue_status
                  FROM appointments a
                  LEFT JOIN pasien p ON a.pasien_id = p.id
                  LEFT JOIN dokter d ON a.dokter_id = d.id
                  LEFT JOIN ruang r ON a.ruang_id = r.id
                  LEFT JOIN queue_status q ON a.id = q.appointment_id
                  WHERE DATE(a.appointment_date) = CURDATE()";
        
        if ($doctorId) {
            $query .= " AND a.dokter_id = ?";
        }
        
        $query .= " ORDER BY a.appointment_date ASC, q.queue_position ASC";
        
        if ($doctorId) {
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                return [];
            }
            mysqli_stmt_bind_param($stmt, "i", $doctorId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($this->conn, $query);
            if (!$result) {
                return [];
            }
        }
        
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        
        return $appointments;
    }
    
    /**
     * Update appointment status
     */
    public function updateAppointmentStatus($appointmentId, $status) {
        $query = "UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $appointmentId);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update queue status if needed
            if ($status == 'checked_in') {
                $this->updateQueueStatus($appointmentId, 'called');
            } elseif ($status == 'in_progress') {
                $this->updateQueueStatus($appointmentId, 'in_progress');
            } elseif ($status == 'completed') {
                $this->updateQueueStatus($appointmentId, 'completed');
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update queue status
     */
    private function updateQueueStatus($appointmentId, $status) {
        $query = "UPDATE queue_status SET status = ? WHERE appointment_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $appointmentId);
        mysqli_stmt_execute($stmt);
    }
    
    /**
     * Schedule notification
     */
    private function scheduleNotification($appointmentId, $type) {
        // Get appointment details
        $query = "SELECT a.*, p.nama_pasien, p.telephone, p.email, d.nama_dokter 
                  FROM appointments a
                  JOIN pasien p ON a.pasien_id = p.id
                  JOIN dokter d ON a.dokter_id = d.id
                  WHERE a.id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $appointmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $appointment = mysqli_fetch_assoc($result);
        
        if (!$appointment) return;
        
        // Insert notification log
        $message = $this->generateNotificationMessage($appointment, $type);
        
        $logQuery = "INSERT INTO notification_logs (appointment_id, pasien_id, notification_type, channel, recipient, message_content, status) 
                     VALUES (?, ?, ?, 'sms', ?, ?, 'pending')";
        $stmt = mysqli_prepare($this->conn, $logQuery);
        mysqli_stmt_bind_param($stmt, "iisss", 
            $appointmentId, $appointment['pasien_id'], $type, 
            $appointment['telephone'], $message
        );
        mysqli_stmt_execute($stmt);
    }
    
    /**
     * Generate notification message
     */
    private function generateNotificationMessage($appointment, $type) {
        $templates = [
            'confirmation' => "Halo {patient_name}, appointment Anda dengan Dr. {doctor_name} pada {appointment_date} telah dikonfirmasi. No. Appointment: {appointment_number}",
            'reminder_24h' => "Reminder: Anda memiliki appointment dengan Dr. {doctor_name} besok {appointment_date}. Mohon datang 15 menit sebelumnya.",
            'reminder_2h' => "Reminder: Appointment Anda dengan Dr. {doctor_name} dalam 2 jam lagi. Terima kasih."
        ];
        
        $template = $templates[$type] ?? $templates['confirmation'];
        
        $replacements = [
            '{patient_name}' => $appointment['nama_pasien'],
            '{doctor_name}' => $appointment['nama_dokter'],
            '{appointment_date}' => date('d/m/Y H:i', strtotime($appointment['appointment_date'])),
            '{appointment_number}' => $appointment['appointment_number']
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Get queue status for doctor
     */
    public function getQueueStatus($doctorId) {
        // Check if queue_status table exists
        $tableCheck = mysqli_query($this->conn, "SHOW TABLES LIKE 'queue_status'");
        if (mysqli_num_rows($tableCheck) == 0) {
            // Table doesn't exist, return empty array
            return [];
        }
        
        $query = "SELECT q.*, a.appointment_number, p.nama_pasien, a.keluhan, a.priority
                  FROM queue_status q
                  JOIN appointments a ON q.appointment_id = a.id
                  JOIN pasien p ON a.pasien_id = p.id
                  WHERE q.dokter_id = ? AND q.status IN ('waiting', 'called', 'in_progress')
                  ORDER BY q.queue_position ASC";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $doctorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $queue = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $queue[] = $row;
        }
        
        return $queue;
    }
}