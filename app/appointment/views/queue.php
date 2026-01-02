<?php
require_once 'app/functions/MY_model.php';
require_once 'app/functions/AppointmentModel.php';

// Check if appointment tables exist
$appointmentTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'appointments'")) > 0;
$queueTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'queue_status'")) > 0;

if (!$appointmentTableExists || !$queueTableExists) {
    // Show upgrade notice
    ?>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h4 class="card-title text-white">
                                <i class="feather icon-alert-triangle"></i> Queue Management Not Available
                            </h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <i class="feather icon-users font-large-2 text-warning"></i>
                                </div>
                                <h3>Queue Management System Belum Terinstall</h3>
                                <p class="text-muted mb-4">
                                    Fitur Queue Management membutuhkan upgrade database ke Smart Clinic v2.0
                                </p>
                                
                                <div class="mt-4">
                                    <a href="setup_v2.php" class="btn btn-primary btn-lg">
                                        <i class="feather icon-arrow-up"></i> Upgrade to v2.0
                                    </a>
                                    <a href="?page=appointment" class="btn btn-secondary">
                                        <i class="feather icon-calendar"></i> Back to Appointments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return;
}

$appointmentModel = new AppointmentModel();
$_SESSION['title'] = 'Queue Management';

// Get all doctors
$doctors = get("SELECT * FROM dokter ORDER BY nama_dokter");

// Get selected doctor or default to first doctor
$selectedDoctorId = $_GET['dokter_id'] ?? ($doctors[0]['id'] ?? null);

// Get queue for selected doctor
$queueData = [];
if ($selectedDoctorId) {
    $queueData = $appointmentModel->getQueueStatus($selectedDoctorId);
}
?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0">Queue Management</h2>
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="?page=appointment">Appointment</a></li>
                            <li class="breadcrumb-item active">Queue Management</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
            <div class="form-group breadcrum-right">
                <button class="btn btn-primary btn-sm" onclick="refreshQueue()">
                    <i class="feather icon-refresh-cw"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="content-body">
        <!-- Doctor Selection -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pilih Dokter</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($doctors as $doctor): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card <?= ($selectedDoctorId == $doctor['id']) ? 'bg-primary' : 'bg-light'; ?> text-center">
                                            <div class="card-body py-3">
                                                <a href="?page=queue-management&dokter_id=<?= $doctor['id']; ?>" 
                                                   class="text-decoration-none <?= ($selectedDoctorId == $doctor['id']) ? 'text-white' : 'text-dark'; ?>">
                                                    <h5 class="mb-1">Dr. <?= $doctor['nama_dokter']; ?></h5>
                                                    <small><?= $doctor['spesialis']; ?></small>
                                                    <div class="mt-2">
                                                        <?php
                                                        $doctorQueue = $appointmentModel->getQueueStatus($doctor['id']);
                                                        $waitingCount = count(array_filter($doctorQueue, function($q) { return $q['status'] == 'waiting'; }));
                                                        ?>
                                                        <span class="badge <?= ($selectedDoctorId == $doctor['id']) ? 'badge-light text-primary' : 'badge-primary'; ?>">
                                                            <?= $waitingCount; ?> Antrian
                                                        </span>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($selectedDoctorId): ?>
            <?php
            $selectedDoctor = array_filter($doctors, function($d) use ($selectedDoctorId) {
                return $d['id'] == $selectedDoctorId;
            });
            $selectedDoctor = reset($selectedDoctor);
            ?>
            
            <!-- Queue Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                Antrian Dr. <?= $selectedDoctor['nama_dokter']; ?> - <?= date('d/m/Y'); ?>
                            </h4>
                            <div class="card-header-toolbar">
                                <span class="badge badge-info">
                                    Total Antrian: <?= count($queueData); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <?php if (empty($queueData)): ?>
                                    <div class="text-center py-5">
                                        <i class="feather icon-users font-large-2 text-muted"></i>
                                        <h4 class="mt-3 text-muted">Tidak Ada Antrian</h4>
                                        <p class="text-muted">Belum ada pasien dalam antrian untuk dokter ini hari ini.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No. Antrian</th>
                                                    <th>No. Appointment</th>
                                                    <th>Pasien</th>
                                                    <th>Keluhan</th>
                                                    <th>Prioritas</th>
                                                    <th>Status</th>
                                                    <th>Estimasi Tunggu</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($queueData as $queue): ?>
                                                    <tr class="<?= ($queue['status'] == 'in_progress') ? 'table-warning' : ''; ?>">
                                                        <td>
                                                            <span class="badge badge-primary font-medium-2">
                                                                <?= $queue['queue_position']; ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $queue['appointment_number']; ?></td>
                                                        <td>
                                                            <strong><?= $queue['nama_pasien']; ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="text-truncate" style="max-width: 200px; display: inline-block;" 
                                                                  title="<?= htmlspecialchars($queue['keluhan']); ?>">
                                                                <?= substr($queue['keluhan'], 0, 50); ?>
                                                                <?= strlen($queue['keluhan']) > 50 ? '...' : ''; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $priorityClass = [
                                                                'emergency' => 'badge-danger',
                                                                'urgent' => 'badge-warning',
                                                                'normal' => 'badge-info',
                                                                'follow_up' => 'badge-secondary'
                                                            ];
                                                            $priorityText = [
                                                                'emergency' => 'Darurat',
                                                                'urgent' => 'Mendesak',
                                                                'normal' => 'Normal',
                                                                'follow_up' => 'Follow Up'
                                                            ];
                                                            ?>
                                                            <span class="badge <?= $priorityClass[$queue['priority']]; ?>">
                                                                <?= $priorityText[$queue['priority']]; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $statusClass = [
                                                                'waiting' => 'badge-secondary',
                                                                'called' => 'badge-primary',
                                                                'in_progress' => 'badge-warning',
                                                                'completed' => 'badge-success'
                                                            ];
                                                            $statusText = [
                                                                'waiting' => 'Menunggu',
                                                                'called' => 'Dipanggil',
                                                                'in_progress' => 'Berlangsung',
                                                                'completed' => 'Selesai'
                                                            ];
                                                            ?>
                                                            <span class="badge <?= $statusClass[$queue['status']]; ?>">
                                                                <?= $statusText[$queue['status']]; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($queue['estimated_wait_time'] > 0): ?>
                                                                <span class="text-muted">
                                                                    ~<?= $queue['estimated_wait_time']; ?> menit
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-success">Sekarang</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <?php if ($queue['status'] == 'waiting'): ?>
                                                                    <button class="btn btn-sm btn-primary" 
                                                                            onclick="callPatient(<?= $queue['appointment_id']; ?>)">
                                                                        <i class="feather icon-phone"></i> Panggil
                                                                    </button>
                                                                <?php elseif ($queue['status'] == 'called'): ?>
                                                                    <button class="btn btn-sm btn-warning" 
                                                                            onclick="startConsultation(<?= $queue['appointment_id']; ?>)">
                                                                        <i class="feather icon-play"></i> Mulai
                                                                    </button>
                                                                <?php elseif ($queue['status'] == 'in_progress'): ?>
                                                                    <button class="btn btn-sm btn-success" 
                                                                            onclick="completeConsultation(<?= $queue['appointment_id']; ?>)">
                                                                        <i class="feather icon-check"></i> Selesai
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Patient Info -->
            <?php
            $currentPatient = array_filter($queueData, function($q) {
                return $q['status'] == 'in_progress';
            });
            $currentPatient = reset($currentPatient);
            ?>
            
            <?php if ($currentPatient): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-warning">
                            <div class="card-header">
                                <h4 class="card-title text-white">
                                    <i class="feather icon-user"></i> Pasien Sedang Dilayani
                                </h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row text-white">
                                        <div class="col-md-3">
                                            <strong>Nama:</strong><br>
                                            <?= $currentPatient['nama_pasien']; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>No. Antrian:</strong><br>
                                            <?= $currentPatient['queue_position']; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Appointment:</strong><br>
                                            <?= $currentPatient['appointment_number']; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Prioritas:</strong><br>
                                            <?= $priorityText[$currentPatient['priority']]; ?>
                                        </div>
                                    </div>
                                    <div class="row mt-3 text-white">
                                        <div class="col-12">
                                            <strong>Keluhan:</strong><br>
                                            <?= $currentPatient['keluhan']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Call patient
function callPatient(appointmentId) {
    if (confirm('Panggil pasien ini?')) {
        updateAppointmentStatus(appointmentId, 'checked_in');
    }
}

// Start consultation
function startConsultation(appointmentId) {
    if (confirm('Mulai konsultasi dengan pasien ini?')) {
        updateAppointmentStatus(appointmentId, 'in_progress');
    }
}

// Complete consultation
function completeConsultation(appointmentId) {
    if (confirm('Selesaikan konsultasi dengan pasien ini?')) {
        updateAppointmentStatus(appointmentId, 'completed');
    }
}

// Update appointment status
function updateAppointmentStatus(appointmentId, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '?page=appointment&action=update_status';
    
    const appointmentInput = document.createElement('input');
    appointmentInput.type = 'hidden';
    appointmentInput.name = 'appointment_id';
    appointmentInput.value = appointmentId;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = status;
    
    form.appendChild(appointmentInput);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

// Refresh queue
function refreshQueue() {
    location.reload();
}

// Auto refresh every 15 seconds
setInterval(() => {
    refreshQueue();
}, 15000);

// Show notification for new patients (if any)
document.addEventListener('DOMContentLoaded', function() {
    // You can add WebSocket or polling logic here for real-time updates
});
</script>