# 🚀 Installation Guide - Smart Clinic Management System v2.0

## 📋 Overview

Panduan ini akan membantu Anda mengupgrade sistem klinik dari versi basic ke **Smart Clinic Management System v2.0** dengan fitur-fitur canggih:

- 🕐 **Smart Appointment System** dengan Queue Management
- 🏥 **Comprehensive Medical Records** dengan Vital Signs
- 💰 **Financial Module** dengan Billing System  
- 🔔 **Real-time Notifications**
- 📊 **Enhanced Analytics & Reporting**

## 🔧 Prerequisites

Pastikan sistem Anda memenuhi requirements:

- ✅ PHP 7.4+ (disarankan PHP 8.0+)
- ✅ MySQL 5.7+ atau MariaDB 10.3+
- ✅ Apache/Nginx Web Server
- ✅ Sistem klinik versi 1.0 sudah terinstall

## 📦 Installation Steps

### Step 1: Backup Database

**PENTING:** Backup database Anda terlebih dahulu!

```sql
-- Via phpMyAdmin: Export database test_klinik
-- Via command line:
mysqldump -u root -p test_klinik > backup_klinik_v1.sql
```

### Step 2: Update Project Files

Pastikan semua file baru sudah ada di project Anda:

```
📁 Project Structure (New Files):
├── database/
│   └── upgrade_v2.sql ✨ NEW
├── app/
│   ├── functions/
│   │   └── AppointmentModel.php ✨ NEW
│   └── appointment/ ✨ NEW
│       ├── AppointmentController.php
│       └── views/
│           ├── index.php
│           ├── create.php
│           └── queue.php
├── upgrade_database.php ✨ NEW
├── INSTALLATION_GUIDE_V2.md ✨ NEW
├── DEVELOPMENT_ROADMAP.md ✨ NEW
└── FEATURE_CONCEPTS.md ✨ NEW
```

### Step 3: Run Database Upgrade

1. **Akses upgrade script** melalui browser:
   ```
   http://localhost/klinik-management/upgrade_database.php
   ```

2. **Monitor proses upgrade** - script akan:
   - ✅ Membuat 15+ tabel baru
   - ✅ Menambah kolom ke tabel existing
   - ✅ Insert sample data dan settings
   - ✅ Membuat indexes untuk performance

3. **Verifikasi hasil** - pastikan semua tabel berhasil dibuat:
   - `appointments` - Smart appointment system
   - `queue_status` - Queue management
   - `doctor_schedules` - Doctor scheduling
   - `vital_signs` - Vital signs tracking
   - `medical_history` - Medical history
   - `invoices` & `payments` - Billing system
   - `notification_logs` - Notification system

### Step 4: Configure Doctor Schedules

Setup jadwal dokter untuk appointment system:

```sql
-- Example: Setup schedule untuk Dr. Johan (ID=1)
INSERT INTO doctor_schedules (dokter_id, day_of_week, start_time, end_time, break_start, break_end, max_patients_per_hour) VALUES
(1, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Monday
(1, 2, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Tuesday
(1, 3, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Wednesday
(1, 4, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Thursday
(1, 5, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4); -- Friday
```

### Step 5: Test New Features

1. **Login ke sistem** dengan kredensial existing
2. **Test Appointment System**:
   - Klik menu "Appointment" → "Daftar Appointment"
   - Buat appointment baru
   - Test available time slots
3. **Test Queue Management**:
   - Klik menu "Appointment" → "Queue Management"
   - Test status updates (Check-in, Start, Complete)
4. **Verify Database**:
   - Check appointment records di database
   - Verify queue status updates

## 🎯 New Features Overview

### 1. Smart Appointment System

**Features:**
- ✅ Intelligent time slot booking
- ✅ Doctor availability checking
- ✅ Priority-based scheduling (Emergency, Urgent, Normal, Follow-up)
- ✅ Automatic appointment numbering
- ✅ Conflict prevention

**Usage:**
- Navigate to **Appointment** → **Daftar Appointment**
- Click "Buat Appointment" 
- Select patient, doctor, date, and available time slot
- System automatically prevents double booking

### 2. Queue Management System

**Features:**
- ✅ Real-time queue status
- ✅ Estimated waiting time calculation
- ✅ Queue position tracking
- ✅ Status updates (Waiting → Called → In Progress → Completed)
- ✅ Doctor-specific queue management

**Usage:**
- Navigate to **Appointment** → **Queue Management**
- Select doctor to view their queue
- Update patient status as they progress through consultation

### 3. Enhanced Medical Records

**New Fields:**
- ✅ Vital signs integration
- ✅ Medical history tracking
- ✅ Allergy management
- ✅ Medical image uploads
- ✅ Treatment plans and follow-ups

### 4. Financial Module Foundation

**Features:**
- ✅ Service pricing management
- ✅ Invoice generation structure
- ✅ Payment tracking system
- ✅ Multiple payment methods support

### 5. Notification System

**Features:**
- ✅ Notification preferences per patient
- ✅ Template-based messaging
- ✅ Multi-channel support (SMS, Email, WhatsApp)
- ✅ Notification logging and tracking

## 🔧 Configuration

### System Settings

Update system settings via database:

```sql
-- Update clinic information
UPDATE system_settings SET setting_value = 'Nama Klinik Anda' WHERE setting_key = 'clinic_name';
UPDATE system_settings SET setting_value = 'Alamat Klinik Anda' WHERE setting_key = 'clinic_address';
UPDATE system_settings SET setting_value = '021-12345678' WHERE setting_key = 'clinic_phone';

-- Configure appointment settings
UPDATE system_settings SET setting_value = '30' WHERE setting_key = 'appointment_slot_duration';
UPDATE system_settings SET setting_value = '50' WHERE setting_key = 'max_appointments_per_day';
```

### Doctor Information

Update doctor information for better appointment system:

```sql
-- Add consultation fees
UPDATE dokter SET consultation_fee = 150000 WHERE id = 1;

-- Add email addresses
UPDATE dokter SET email = 'doctor@klinik.com' WHERE id = 1;

-- Add license numbers
UPDATE dokter SET license_number = 'STR-12345678' WHERE id = 1;
```

## 🚨 Troubleshooting

### Common Issues

**1. "Table already exists" errors**
- ✅ Normal jika menjalankan upgrade multiple times
- ✅ Check apakah tabel sudah ada di database

**2. "Column already exists" errors**  
- ✅ Normal jika ada kolom yang sudah ditambahkan sebelumnya
- ✅ Verify struktur tabel sudah benar

**3. Appointment slots tidak muncul**
- ❌ Check doctor_schedules table ada data
- ❌ Verify dokter_id sesuai dengan data dokter
- ❌ Check day_of_week (1=Monday, 7=Sunday)

**4. Queue management tidak update**
- ❌ Check appointments table ada data
- ❌ Verify queue_status table terisi otomatis
- ❌ Test dengan browser refresh

### Database Verification

Jalankan query ini untuk verify installation:

```sql
-- Check new tables
SHOW TABLES LIKE 'appointments';
SHOW TABLES LIKE 'queue_status';
SHOW TABLES LIKE 'doctor_schedules';

-- Check sample data
SELECT COUNT(*) as appointment_count FROM appointments;
SELECT COUNT(*) as schedule_count FROM doctor_schedules;
SELECT COUNT(*) as settings_count FROM system_settings;

-- Check updated columns
DESCRIBE dokter;
DESCRIBE pasien;
DESCRIBE rekam_medis;
```

## 📊 Performance Optimization

### Recommended Indexes

Database upgrade sudah include indexes, tapi verify dengan:

```sql
-- Check indexes
SHOW INDEX FROM appointments;
SHOW INDEX FROM queue_status;
SHOW INDEX FROM doctor_schedules;
```

### Caching (Optional)

Untuk performance yang lebih baik:

```php
// Add to config.php
$cache_enabled = true;
$cache_duration = 300; // 5 minutes
```

## 🔒 Security Considerations

### Post-Installation Security

1. **Delete upgrade file**:
   ```bash
   rm upgrade_database.php
   ```

2. **Update database credentials** (jika diperlukan):
   ```php
   // app/functions/MY_model.php
   $conn = mysqli_connect('localhost', 'new_user', 'strong_password', 'test_klinik');
   ```

3. **Enable error logging**:
   ```php
   // Add to config.php
   error_reporting(E_ALL);
   ini_set('log_errors', 1);
   ini_set('error_log', 'logs/php_errors.log');
   ```

## 🎉 Success Verification

Setelah installation berhasil, Anda harus bisa:

- ✅ Login dengan kredensial existing
- ✅ Melihat menu "Appointment" di sidebar
- ✅ Membuat appointment baru dengan time slot selection
- ✅ Mengakses Queue Management
- ✅ Melihat stats cards di appointment dashboard
- ✅ Update status appointment (Check-in → Start → Complete)

## 📞 Support

Jika mengalami masalah:

1. **Check error logs** di browser console dan PHP error log
2. **Verify database structure** dengan queries di atas
3. **Test dengan sample data** untuk isolate masalah
4. **Backup & restore** jika diperlukan rollback

## 🚀 Next Steps

Setelah v2.0 berhasil diinstall:

1. **Setup doctor schedules** untuk semua dokter
2. **Configure notification settings** 
3. **Test appointment workflow** end-to-end
4. **Train staff** menggunakan fitur baru
5. **Monitor performance** dan optimize jika diperlukan

---

**Selamat! Sistem klinik Anda sekarang sudah upgrade ke Smart Clinic Management System v2.0! 🎉**