<?php
/**
 * Bootstrap file untuk Smart Clinic Management System v2.0
 * File ini memuat semua dependencies yang diperlukan
 */

// Load core functions
require_once 'MY_model.php';
require_once 'function.php';
require_once 'config.php';

// Load appointment system
if (file_exists('AppointmentModel.php')) {
    require_once 'AppointmentModel.php';
}

// Global helper functions
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y') {
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}

// Check if database tables exist
function checkTableExists($tableName) {
    global $conn;
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// System status check
function getSystemStatus() {
    $status = [
        'database_connected' => false,
        'appointments_table_exists' => false,
        'doctors_available' => false,
        'patients_available' => false,
        'ready_for_appointments' => false
    ];
    
    global $conn;
    
    // Check database connection
    if ($conn) {
        $status['database_connected'] = true;
        
        // Check appointments table
        if (checkTableExists('appointments')) {
            $status['appointments_table_exists'] = true;
        }
        
        // Check doctors
        $doctorCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM dokter"))['count'];
        if ($doctorCount > 0) {
            $status['doctors_available'] = true;
        }
        
        // Check patients
        $patientCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pasien"))['count'];
        if ($patientCount > 0) {
            $status['patients_available'] = true;
        }
        
        // Overall readiness
        $status['ready_for_appointments'] = $status['appointments_table_exists'] && 
                                          $status['doctors_available'] && 
                                          $status['patients_available'];
    }
    
    return $status;
}