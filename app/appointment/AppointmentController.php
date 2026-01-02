<?php

require_once 'app/functions/AppointmentModel.php';

class AppointmentController {
    private $appointmentModel;
    
    public function __construct() {
        $this->appointmentModel = new AppointmentModel();
    }
    
    /**
     * Handle appointment creation
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'pasien_id' => $_POST['pasien_id'],
                'dokter_id' => $_POST['dokter_id'],
                'ruang_id' => $_POST['ruang_id'] ?? null,
                'appointment_date' => $_POST['appointment_date'] . ' ' . $_POST['appointment_time'],
                'priority' => $_POST['priority'] ?? 'normal',
                'keluhan' => $_POST['keluhan'],
                'notes' => $_POST['notes'] ?? ''
            ];
            
            $result = $this->appointmentModel->createAppointment($data);
            
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'] . ' (No: ' . $result['appointment_number'] . ')';
                header('Location: ?page=appointment');
                exit;
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }
    }
    
    /**
     * Get available slots via AJAX
     */
    public function getAvailableSlots() {
        if (isset($_GET['dokter_id']) && isset($_GET['date'])) {
            $doctorId = $_GET['dokter_id'];
            $date = $_GET['date'];
            
            $slots = $this->appointmentModel->getAvailableSlots($doctorId, $date);
            
            header('Content-Type: application/json');
            echo json_encode($slots);
            exit;
        }
    }
    
    /**
     * Update appointment status
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = $_POST['appointment_id'];
            $status = $_POST['status'];
            
            if ($this->appointmentModel->updateAppointmentStatus($appointmentId, $status)) {
                $_SESSION['success_message'] = 'Status appointment berhasil diupdate';
            } else {
                $_SESSION['error_message'] = 'Gagal mengupdate status appointment';
            }
            
            // Redirect back to the referring page
            $referer = $_SERVER['HTTP_REFERER'] ?? '?page=appointment';
            header('Location: ' . $referer);
            exit;
        }
    }
    
    /**
     * Get queue status for doctor
     */
    public function getQueueStatus() {
        if (isset($_GET['dokter_id'])) {
            $doctorId = $_GET['dokter_id'];
            $queue = $this->appointmentModel->getQueueStatus($doctorId);
            
            header('Content-Type: application/json');
            echo json_encode($queue);
            exit;
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && isset($_GET['page']) && $_GET['page'] == 'appointment') {
    $controller = new AppointmentController();
    
    switch ($_GET['action']) {
        case 'get_slots':
            $controller->getAvailableSlots();
            break;
        case 'update_status':
            $controller->updateStatus();
            break;
        case 'get_queue':
            $controller->getQueueStatus();
            break;
        case 'create':
            $controller->create();
            break;
    }
}