<?php
/**
 * Database Upgrade Script
 * Jalankan file ini untuk mengupgrade database ke versi 2.0
 */

require_once 'app/functions/MY_model.php';

echo "<h2>🚀 Database Upgrade to Smart Clinic Management System v2.0</h2>";
echo "<hr>";

try {
    // Read upgrade SQL file
    $upgradeSQL = file_get_contents('database/upgrade_v2.sql');
    
    if (!$upgradeSQL) {
        throw new Exception("Tidak dapat membaca file upgrade_v2.sql");
    }
    
    // Split SQL statements
    $statements = array_filter(
        array_map('trim', explode(';', $upgradeSQL)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "<p>📋 Total statements to execute: " . count($statements) . "</p>";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;'>";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        echo "<p><strong>Statement " . ($index + 1) . ":</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 5px; font-size: 12px;'>" . htmlspecialchars(substr($statement, 0, 200)) . (strlen($statement) > 200 ? '...' : '') . "</pre>";
        
        $result = mysqli_query($conn, $statement);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Success</p>";
            $successCount++;
        } else {
            $error = mysqli_error($conn);
            echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($error) . "</p>";
            $errorCount++;
            
            // Continue with non-critical errors (like table already exists)
            if (strpos($error, 'already exists') === false && 
                strpos($error, 'Duplicate column') === false &&
                strpos($error, 'Duplicate key') === false) {
                // Only stop for critical errors
                // throw new Exception("Critical error: " . $error);
            }
        }
        
        echo "<hr style='margin: 10px 0;'>";
    }
    
    echo "</div>";
    
    echo "<h3>📊 Upgrade Summary:</h3>";
    echo "<ul>";
    echo "<li>✅ Successful statements: <strong>$successCount</strong></li>";
    echo "<li>❌ Failed statements: <strong>$errorCount</strong></li>";
    echo "</ul>";
    
    if ($errorCount == 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🎉 Database Upgrade Completed Successfully!</h4>";
        echo "<p>Your database has been upgraded to Smart Clinic Management System v2.0</p>";
        echo "<p><strong>New Features Available:</strong></p>";
        echo "<ul>";
        echo "<li>🕐 Smart Appointment System with Queue Management</li>";
        echo "<li>🏥 Comprehensive Medical Records with Vital Signs</li>";
        echo "<li>💰 Financial Module with Billing System</li>";
        echo "<li>🔔 Notification System</li>";
        echo "<li>📊 Enhanced Reporting</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📋 Next Steps:</h4>";
        echo "<ol>";
        echo "<li>Delete this upgrade file for security: <code>upgrade_database.php</code></li>";
        echo "<li>Access the new Appointment System: <a href='?page=appointment'>Appointment Management</a></li>";
        echo "<li>Setup Doctor Schedules in the database</li>";
        echo "<li>Configure notification settings</li>";
        echo "<li>Test the new features</li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>⚠️ Upgrade Completed with Some Errors</h4>";
        echo "<p>Some statements failed, but this might be normal (e.g., tables already exist).</p>";
        echo "<p>Please check the errors above and verify that the new features work correctly.</p>";
        echo "</div>";
    }
    
    // Test database structure
    echo "<h3>🔍 Database Structure Verification:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    
    $tables = [
        'appointments' => 'Smart Appointment System',
        'queue_status' => 'Queue Management',
        'doctor_schedules' => 'Doctor Scheduling',
        'vital_signs' => 'Vital Signs Tracking',
        'medical_history' => 'Medical History',
        'patient_allergies' => 'Allergy Management',
        'invoices' => 'Billing System',
        'payments' => 'Payment Tracking',
        'notification_logs' => 'Notification System'
    ];
    
    foreach ($tables as $table => $description) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p>✅ <strong>$table</strong> - $description</p>";
        } else {
            echo "<p>❌ <strong>$table</strong> - $description (NOT FOUND)</p>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>❌ Upgrade Failed</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Back to Dashboard</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>