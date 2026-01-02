<?php
require_once 'app/functions/MY_model.php';

echo "<h2>🔍 Database Test - Smart Clinic v2.0</h2>";
echo "<hr>";

// Test database connection
if ($conn) {
    echo "<p>✅ Database connection: <strong>SUCCESS</strong></p>";
} else {
    echo "<p>❌ Database connection: <strong>FAILED</strong></p>";
    die("Cannot proceed without database connection.");
}

// Test required tables
$requiredTables = [
    'appointments' => 'Smart Appointment System',
    'queue_status' => 'Queue Management',
    'doctor_schedules' => 'Doctor Scheduling',
    'dokter' => 'Doctor Data (Original)',
    'pasien' => 'Patient Data (Original)',
    'ruang' => 'Room Data (Original)'
];

echo "<h3>📋 Table Verification:</h3>";
foreach ($requiredTables as $table => $description) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p>✅ <strong>$table</strong> - $description</p>";
        
        // Show record count
        $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
        $count = mysqli_fetch_assoc($countResult)['count'];
        echo "<p style='margin-left: 20px; color: #666;'>Records: $count</p>";
    } else {
        echo "<p>❌ <strong>$table</strong> - $description (MISSING)</p>";
    }
}

// Test functions
echo "<h3>🔧 Function Test:</h3>";

// Test get() function
try {
    $doctors = get("SELECT * FROM dokter LIMIT 1");
    echo "<p>✅ <strong>get() function</strong> - Working</p>";
    echo "<p style='margin-left: 20px; color: #666;'>Sample doctor: " . ($doctors[0]['nama_dokter'] ?? 'No doctors found') . "</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>get() function</strong> - Error: " . $e->getMessage() . "</p>";
}

// Test AppointmentModel
try {
    require_once 'app/functions/AppointmentModel.php';
    $appointmentModel = new AppointmentModel();
    echo "<p>✅ <strong>AppointmentModel</strong> - Loaded successfully</p>";
    
    // Test method
    $todayAppointments = $appointmentModel->getTodayAppointments();
    echo "<p style='margin-left: 20px; color: #666;'>Today's appointments: " . count($todayAppointments) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ <strong>AppointmentModel</strong> - Error: " . $e->getMessage() . "</p>";
}

// Test sample data
echo "<h3>📊 Sample Data Check:</h3>";

// Check if we have doctors
$doctorCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM dokter"))['count'];
if ($doctorCount > 0) {
    echo "<p>✅ Doctors available: <strong>$doctorCount</strong></p>";
} else {
    echo "<p>⚠️ No doctors found. Please add doctors first.</p>";
}

// Check if we have patients
$patientCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pasien"))['count'];
if ($patientCount > 0) {
    echo "<p>✅ Patients available: <strong>$patientCount</strong></p>";
} else {
    echo "<p>⚠️ No patients found. Please add patients first.</p>";
}

// Check if we have rooms
$roomCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM ruang"))['count'];
if ($roomCount > 0) {
    echo "<p>✅ Rooms available: <strong>$roomCount</strong></p>";
} else {
    echo "<p>⚠️ No rooms found. Please add rooms first.</p>";
}

echo "<hr>";

// Recommendations
echo "<h3>💡 Recommendations:</h3>";

if ($doctorCount == 0) {
    echo "<p>🔸 Add doctors via: <a href='?page=tambah-dokter'>Add Doctor</a></p>";
}

if ($patientCount == 0) {
    echo "<p>🔸 Add patients via: <a href='?page=tambah-pasien'>Add Patient</a></p>";
}

// Check if appointments table exists and is empty
$appointmentTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'appointments'")) > 0;
if ($appointmentTableExists) {
    $appointmentCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
    if ($appointmentCount == 0) {
        echo "<p>🔸 Ready to create appointments via: <a href='?page=appointment'>Appointment System</a></p>";
    }
} else {
    echo "<p>🔸 Run database upgrade: <a href='upgrade_database.php'>Upgrade Database</a></p>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Dashboard</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
</style>