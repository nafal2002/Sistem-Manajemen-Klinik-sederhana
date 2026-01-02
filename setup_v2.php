<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Smart Clinic v2.0</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
            line-height: 1.6;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background: #dc3545;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .feature-card {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .feature-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Smart Clinic Management System v2.0</h1>
            <p>Upgrade sistem klinik Anda dengan fitur-fitur canggih</p>
        </div>

        <?php
        require_once 'app/functions/MY_model.php';
        
        // Check database connection
        if (!$conn) {
            echo '<div class="alert alert-warning">';
            echo '<h3>❌ Database Connection Failed</h3>';
            echo '<p>Tidak dapat terhubung ke database. Pastikan MySQL sudah running dan konfigurasi database benar.</p>';
            echo '</div>';
            exit;
        }
        
        // Check if upgrade needed
        $appointmentTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'appointments'")) > 0;
        $queueTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'queue_status'")) > 0;
        
        if ($appointmentTableExists && $queueTableExists) {
            echo '<div class="alert alert-info">';
            echo '<h3>✅ Smart Clinic v2.0 Already Installed!</h3>';
            echo '<p>Sistem sudah diupgrade ke versi 2.0. Anda dapat langsung menggunakan fitur-fitur baru.</p>';
            echo '<a href="index.php" class="btn btn-success">🏠 Go to Dashboard</a>';
            echo '<a href="?page=appointment" class="btn">📅 Appointment System</a>';
            echo '</div>';
        } else {
            ?>
            
            <div class="alert alert-info">
                <h3>🎯 Selamat Datang di Smart Clinic v2.0!</h3>
                <p>Sistem Anda akan diupgrade dengan fitur-fitur canggih untuk manajemen klinik modern.</p>
            </div>

            <h2>✨ Fitur Baru yang Akan Ditambahkan:</h2>
            <div class="feature-list">
                <div class="feature-card">
                    <div class="feature-icon">🕐</div>
                    <h4>Smart Appointment</h4>
                    <p>Sistem appointment cerdas dengan queue management</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏥</div>
                    <h4>Medical Records</h4>
                    <p>Rekam medis lengkap dengan vital signs</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h4>Financial Module</h4>
                    <p>Sistem billing dan payment tracking</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h4>Notifications</h4>
                    <p>Sistem notifikasi multi-channel</p>
                </div>
            </div>

            <div class="step">
                <h3>📋 Langkah Instalasi:</h3>
                <ol>
                    <li><strong>Backup Database</strong> - Sistem akan otomatis backup data existing</li>
                    <li><strong>Upgrade Database</strong> - Menambah tabel dan fitur baru</li>
                    <li><strong>Setup Configuration</strong> - Konfigurasi awal sistem</li>
                    <li><strong>Test Features</strong> - Verifikasi fitur baru berfungsi</li>
                </ol>
            </div>

            <div class="alert alert-warning">
                <h4>⚠️ Penting Sebelum Upgrade:</h4>
                <ul>
                    <li>Pastikan tidak ada user lain yang sedang menggunakan sistem</li>
                    <li>Backup database akan dibuat otomatis</li>
                    <li>Proses upgrade membutuhkan waktu 1-2 menit</li>
                    <li>Jangan tutup browser selama proses upgrade</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="upgrade_database.php" class="btn btn-success" style="font-size: 18px; padding: 15px 30px;">
                    🚀 Mulai Upgrade ke v2.0
                </a>
            </div>

            <div style="text-align: center;">
                <a href="test_database.php" class="btn btn-warning">🔍 Test Database</a>
                <a href="index.php" class="btn">🏠 Back to Dashboard</a>
            </div>
            
            <?php
        }
        ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d;">
            <p>Smart Clinic Management System v2.0 | Developed with ❤️</p>
        </div>
    </div>
</body>
</html>