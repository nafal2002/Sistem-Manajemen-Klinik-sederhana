<?php
require_once 'app/functions/MY_model.php';
require_once 'app/appointment/AppointmentController.php';

$controller = new AppointmentController();
$controller->create();

$_SESSION['title'] = 'Buat Appointment Baru';

// Get data for form
$patients = get("SELECT * FROM pasien ORDER BY nama_pasien");
$doctors = get("SELECT * FROM dokter ORDER BY nama_dokter");
$rooms = get("SELECT * FROM ruang ORDER BY nama_ruang");
?>

<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0">Buat Appointment Baru</h2>
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="?page=appointment">Appointment</a></li>
                            <li class="breadcrumb-item active">Buat Baru</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Form Appointment Baru</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger">
                                    <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                </div>
                            <?php endif; ?>

                            <form action="?page=tambah-appointment" method="POST" id="appointmentForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pasien_id">Pasien *</label>
                                            <select class="form-control select2" name="pasien_id" id="pasien_id" required>
                                                <option value="">Pilih Pasien</option>
                                                <?php foreach ($patients as $patient): ?>
                                                    <option value="<?= $patient['id']; ?>">
                                                        <?= $patient['nama_pasien']; ?> - <?= $patient['nomor_identitas']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dokter_id">Dokter *</label>
                                            <select class="form-control" name="dokter_id" id="dokter_id" required onchange="loadAvailableSlots()">
                                                <option value="">Pilih Dokter</option>
                                                <?php foreach ($doctors as $doctor): ?>
                                                    <option value="<?= $doctor['id']; ?>">
                                                        Dr. <?= $doctor['nama_dokter']; ?> - <?= $doctor['spesialis']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="appointment_date">Tanggal *</label>
                                            <input type="date" class="form-control" name="appointment_date" id="appointment_date" 
                                                   min="<?= date('Y-m-d'); ?>" required onchange="loadAvailableSlots()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="appointment_time">Waktu *</label>
                                            <select class="form-control" name="appointment_time" id="appointment_time" required>
                                                <option value="">Pilih tanggal dan dokter terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="priority">Prioritas</label>
                                            <select class="form-control" name="priority" id="priority">
                                                <option value="normal">Normal</option>
                                                <option value="urgent">Mendesak</option>
                                                <option value="emergency">Darurat</option>
                                                <option value="follow_up">Follow Up</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ruang_id">Ruang</label>
                                            <select class="form-control" name="ruang_id" id="ruang_id">
                                                <option value="">Pilih Ruang (Opsional)</option>
                                                <?php foreach ($rooms as $room): ?>
                                                    <option value="<?= $room['id']; ?>"><?= $room['nama_ruang']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="keluhan">Keluhan *</label>
                                    <textarea class="form-control" name="keluhan" id="keluhan" rows="4" 
                                              placeholder="Jelaskan keluhan pasien secara detail..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes">Catatan Tambahan</label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3" 
                                              placeholder="Catatan tambahan untuk dokter atau staff (opsional)"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="feather icon-save"></i> Buat Appointment
                                    </button>
                                    <a href="?page=appointment" class="btn btn-secondary">
                                        <i class="feather icon-x"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load available time slots
function loadAvailableSlots() {
    const doctorId = document.getElementById('dokter_id').value;
    const date = document.getElementById('appointment_date').value;
    const timeSelect = document.getElementById('appointment_time');
    
    if (!doctorId || !date) {
        timeSelect.innerHTML = '<option value="">Pilih dokter dan tanggal terlebih dahulu</option>';
        return;
    }
    
    // Show loading
    timeSelect.innerHTML = '<option value="">Loading slot waktu...</option>';
    
    fetch(`?page=appointment&action=get_slots&dokter_id=${doctorId}&date=${date}`)
        .then(response => response.json())
        .then(slots => {
            timeSelect.innerHTML = '<option value="">Pilih Waktu</option>';
            
            if (slots.length === 0) {
                timeSelect.innerHTML = '<option value="">Dokter tidak tersedia pada tanggal ini</option>';
                return;
            }
            
            slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.time;
                option.textContent = slot.formatted_time;
                option.disabled = !slot.available;
                
                if (!slot.available) {
                    option.textContent += ' (Sudah Terisi)';
                    option.style.color = '#999';
                }
                
                timeSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading slots:', error);
            timeSelect.innerHTML = '<option value="">Error loading slots. Silakan refresh halaman.</option>';
        });
}

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Cari pasien...",
        allowClear: true
    });
});
</script>