-- =====================================================
-- UPGRADE DATABASE V2: SMART CLINIC MANAGEMENT SYSTEM
-- =====================================================

-- 1. SMART APPOINTMENT SYSTEM
-- =====================================================

-- Tabel appointment scheduling
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_number VARCHAR(20) UNIQUE NOT NULL,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    ruang_id INT,
    appointment_date DATETIME NOT NULL,
    estimated_duration INT DEFAULT 30, -- dalam menit
    actual_start_time DATETIME NULL,
    actual_end_time DATETIME NULL,
    status ENUM('scheduled', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    priority ENUM('emergency', 'urgent', 'normal', 'follow_up') DEFAULT 'normal',
    keluhan TEXT,
    notes TEXT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    FOREIGN KEY (dokter_id) REFERENCES dokter(id),
    FOREIGN KEY (ruang_id) REFERENCES ruang(id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_dokter_date (dokter_id, appointment_date),
    INDEX idx_status (status)
);

-- Tabel queue management
CREATE TABLE queue_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dokter_id INT NOT NULL,
    appointment_id INT,
    queue_position INT NOT NULL,
    estimated_wait_time INT DEFAULT 0, -- dalam menit
    status ENUM('waiting', 'called', 'in_progress', 'completed') DEFAULT 'waiting',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_dokter_position (dokter_id, queue_position)
);

-- Tabel doctor schedules
CREATE TABLE doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dokter_id INT NOT NULL,
    day_of_week TINYINT NOT NULL, -- 1=Monday, 7=Sunday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_start TIME,
    break_end TIME,
    max_patients_per_hour INT DEFAULT 4,
    slot_duration INT DEFAULT 30, -- menit per slot
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id),
    INDEX idx_dokter_day (dokter_id, day_of_week)
);

-- 2. COMPREHENSIVE MEDICAL RECORDS
-- =====================================================

-- Tabel vital signs
CREATE TABLE vital_signs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rekam_medis_id INT,
    appointment_id INT,
    blood_pressure_systolic INT,
    blood_pressure_diastolic INT,
    heart_rate INT,
    temperature DECIMAL(4,2),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    oxygen_saturation INT,
    respiratory_rate INT,
    bmi DECIMAL(4,2) GENERATED ALWAYS AS (
        CASE 
            WHEN height > 0 THEN weight / ((height/100) * (height/100))
            ELSE NULL 
        END
    ) STORED,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by INT, -- user_id perawat/dokter
    notes TEXT,
    FOREIGN KEY (rekam_medis_id) REFERENCES rekam_medis(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_rekam_medis (rekam_medis_id),
    INDEX idx_recorded_date (recorded_at)
);

-- Tabel medical history
CREATE TABLE medical_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pasien_id INT NOT NULL,
    condition_name VARCHAR(255) NOT NULL,
    icd10_code VARCHAR(10),
    diagnosed_date DATE,
    status ENUM('active', 'resolved', 'chronic', 'monitoring') DEFAULT 'active',
    severity ENUM('mild', 'moderate', 'severe', 'critical') DEFAULT 'mild',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    INDEX idx_pasien_condition (pasien_id, status)
);

-- Tabel allergies
CREATE TABLE patient_allergies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pasien_id INT NOT NULL,
    allergen VARCHAR(255) NOT NULL,
    reaction_type VARCHAR(255),
    severity ENUM('mild', 'moderate', 'severe', 'life_threatening') DEFAULT 'mild',
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    INDEX idx_pasien_allergy (pasien_id, is_active)
);

-- Tabel medical images
CREATE TABLE medical_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rekam_medis_id INT,
    appointment_id INT,
    image_type ENUM('xray', 'ct_scan', 'mri', 'ultrasound', 'lab_result', 'photo', 'document') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (rekam_medis_id) REFERENCES rekam_medis(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_rekam_medis_type (rekam_medis_id, image_type)
);

-- 3. FINANCIAL MODULE
-- =====================================================

-- Tabel service prices
CREATE TABLE service_prices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_type ENUM('consultation', 'procedure', 'lab_test', 'room_charge') NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_code VARCHAR(50),
    price DECIMAL(12,2) NOT NULL,
    dokter_id INT NULL, -- untuk consultation fee per dokter
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id),
    INDEX idx_service_type (service_type, is_active)
);

-- Tabel invoices
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    rekam_medis_id INT,
    appointment_id INT,
    pasien_id INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_percentage DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft', 'sent', 'partial_paid', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    due_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (rekam_medis_id) REFERENCES rekam_medis(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    INDEX idx_invoice_status (status),
    INDEX idx_pasien_invoice (pasien_id, status)
);

-- Tabel invoice items
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    item_type ENUM('consultation', 'procedure', 'medication', 'room_charge', 'lab_test', 'other') NOT NULL,
    item_id INT, -- reference ke dokter_id, obat_id, service_id, etc
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(8,2) DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_invoice_type (invoice_id, item_type)
);

-- Tabel payments
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_id INT NOT NULL,
    payment_method ENUM('cash', 'debit_card', 'credit_card', 'bank_transfer', 'insurance', 'installment') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATETIME NOT NULL,
    reference_number VARCHAR(100),
    notes TEXT,
    processed_by INT, -- user_id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_method (payment_method)
);

-- 4. NOTIFICATION SYSTEM
-- =====================================================

-- Tabel notification preferences
CREATE TABLE notification_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pasien_id INT NOT NULL,
    sms_enabled BOOLEAN DEFAULT TRUE,
    email_enabled BOOLEAN DEFAULT TRUE,
    whatsapp_enabled BOOLEAN DEFAULT FALSE,
    push_enabled BOOLEAN DEFAULT TRUE,
    reminder_24h BOOLEAN DEFAULT TRUE,
    reminder_2h BOOLEAN DEFAULT TRUE,
    reminder_30m BOOLEAN DEFAULT FALSE,
    language ENUM('id', 'en') DEFAULT 'id',
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    UNIQUE KEY unique_pasien (pasien_id)
);

-- Tabel notification templates
CREATE TABLE notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('appointment_reminder', 'appointment_confirmation', 'queue_update', 'payment_reminder', 'lab_result') NOT NULL,
    channel ENUM('sms', 'email', 'whatsapp', 'push') NOT NULL,
    language ENUM('id', 'en') DEFAULT 'id',
    subject VARCHAR(255),
    message_template TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_template_type (template_type, channel, language)
);

-- Tabel notification logs
CREATE TABLE notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT,
    pasien_id INT NOT NULL,
    notification_type ENUM('reminder_24h', 'reminder_2h', 'reminder_30m', 'confirmation', 'delay_alert', 'queue_update', 'payment_reminder', 'lab_result') NOT NULL,
    channel ENUM('sms', 'email', 'whatsapp', 'push') NOT NULL,
    recipient VARCHAR(255) NOT NULL, -- phone/email
    subject VARCHAR(255),
    message_content TEXT NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'failed', 'opened') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (pasien_id) REFERENCES pasien(id),
    INDEX idx_notification_status (status, created_at),
    INDEX idx_pasien_type (pasien_id, notification_type)
);

-- 5. SYSTEM CONFIGURATION
-- =====================================================

-- Tabel system settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE, -- bisa diakses tanpa login
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT
);

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('clinic_name', 'Klinik Sehat Bersama', 'string', 'Nama klinik', TRUE),
('clinic_address', 'Jl. Kesehatan No. 123, Jakarta', 'string', 'Alamat klinik', TRUE),
('clinic_phone', '021-12345678', 'string', 'Telepon klinik', TRUE),
('clinic_email', 'info@kliniksehat.com', 'string', 'Email klinik', TRUE),
('appointment_slot_duration', '30', 'number', 'Durasi slot appointment (menit)', FALSE),
('max_appointments_per_day', '50', 'number', 'Maksimal appointment per hari', FALSE),
('reminder_24h_enabled', 'true', 'boolean', 'Aktifkan reminder 24 jam', FALSE),
('reminder_2h_enabled', 'true', 'boolean', 'Aktifkan reminder 2 jam', FALSE),
('sms_provider', 'twilio', 'string', 'Provider SMS (twilio/nexmo)', FALSE),
('tax_percentage', '10', 'number', 'Persentase pajak (%)', FALSE),
('currency', 'IDR', 'string', 'Mata uang', FALSE);

-- 6. AUDIT LOG SYSTEM
-- =====================================================

-- Tabel audit logs
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('CREATE', 'UPDATE', 'DELETE', 'VIEW') NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user_action (user_id, action, created_at)
);

-- 7. UPDATE EXISTING TABLES
-- =====================================================

-- Update tabel users untuk role management
ALTER TABLE users ADD COLUMN role ENUM('admin', 'doctor', 'nurse', 'receptionist', 'cashier') DEFAULT 'admin' AFTER group_id;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role;
ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL AFTER last_login;
ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER password;
ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER phone;

-- Update tabel dokter
ALTER TABLE dokter ADD COLUMN email VARCHAR(255) AFTER telephone;
ALTER TABLE dokter ADD COLUMN license_number VARCHAR(50) AFTER spesialis;
ALTER TABLE dokter ADD COLUMN consultation_fee DECIMAL(10,2) DEFAULT 0 AFTER license_number;
ALTER TABLE dokter ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER consultation_fee;

-- Update tabel pasien
ALTER TABLE pasien ADD COLUMN email VARCHAR(255) AFTER telephone;
ALTER TABLE pasien ADD COLUMN tanggal_lahir DATE AFTER jenis_kelamin;
ALTER TABLE pasien ADD COLUMN pekerjaan VARCHAR(100) AFTER alamat;
ALTER TABLE pasien ADD COLUMN emergency_contact_name VARCHAR(255) AFTER email;
ALTER TABLE pasien ADD COLUMN emergency_contact_phone VARCHAR(20) AFTER emergency_contact_name;
ALTER TABLE pasien ADD COLUMN insurance_provider VARCHAR(100) AFTER emergency_contact_phone;
ALTER TABLE pasien ADD COLUMN insurance_number VARCHAR(100) AFTER insurance_provider;

-- Update tabel rekam_medis
ALTER TABLE rekam_medis ADD COLUMN appointment_id INT AFTER ruang_id;
ALTER TABLE rekam_medis ADD COLUMN chief_complaint TEXT AFTER keluhan; -- keluhan utama
ALTER TABLE rekam_medis ADD COLUMN physical_examination TEXT AFTER diagnosa;
ALTER TABLE rekam_medis ADD COLUMN treatment_plan TEXT AFTER physical_examination;
ALTER TABLE rekam_medis ADD COLUMN follow_up_date DATE AFTER treatment_plan;
ALTER TABLE rekam_medis ADD COLUMN status ENUM('draft', 'completed', 'follow_up_needed') DEFAULT 'draft' AFTER follow_up_date;

-- Add foreign key untuk appointment_id
ALTER TABLE rekam_medis ADD FOREIGN KEY (appointment_id) REFERENCES appointments(id);

-- 8. INDEXES FOR PERFORMANCE
-- =====================================================

-- Indexes untuk pencarian yang sering dilakukan
CREATE INDEX idx_pasien_nama ON pasien(nama_pasien);
CREATE INDEX idx_pasien_nomor_identitas ON pasien(nomor_identitas);
CREATE INDEX idx_dokter_nama ON dokter(nama_dokter);
CREATE INDEX idx_rekam_medis_tanggal ON rekam_medis(tanggal);
CREATE INDEX idx_obat_nama ON obat(nama_obat);

-- 9. SAMPLE DATA FOR TESTING
-- =====================================================

-- Insert sample doctor schedules
INSERT INTO doctor_schedules (dokter_id, day_of_week, start_time, end_time, break_start, break_end, max_patients_per_hour) VALUES
(1, 1, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Monday
(1, 2, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Tuesday
(1, 3, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Wednesday
(1, 4, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4), -- Thursday
(1, 5, '08:00:00', '17:00:00', '12:00:00', '13:00:00', 4); -- Friday

-- Insert sample service prices
INSERT INTO service_prices (service_type, service_name, service_code, price, dokter_id) VALUES
('consultation', 'Konsultasi Umum', 'CONS_001', 150000.00, 1),
('consultation', 'Konsultasi Spesialis', 'CONS_002', 250000.00, 1),
('procedure', 'Pemeriksaan Darah Lengkap', 'LAB_001', 75000.00, NULL),
('procedure', 'Rontgen Dada', 'RAD_001', 200000.00, NULL),
('room_charge', 'Biaya Ruang VIP', 'ROOM_001', 50000.00, NULL);

-- Insert sample notification templates
INSERT INTO notification_templates (template_name, template_type, channel, language, subject, message_template) VALUES
('appointment_reminder_24h_sms', 'appointment_reminder', 'sms', 'id', NULL, 'Halo {patient_name}, Anda memiliki appointment dengan Dr. {doctor_name} besok {appointment_date} pukul {appointment_time}. Mohon datang 15 menit sebelumnya. Info: {clinic_phone}'),
('appointment_reminder_2h_sms', 'appointment_reminder', 'sms', 'id', NULL, 'Reminder: Appointment Anda dengan Dr. {doctor_name} dalam 2 jam lagi ({appointment_time}). Lokasi: {clinic_address}. Terima kasih.'),
('appointment_confirmation_email', 'appointment_confirmation', 'email', 'id', 'Konfirmasi Appointment - {clinic_name}', 'Yth. {patient_name},\n\nAppointment Anda telah dikonfirmasi:\nDokter: Dr. {doctor_name}\nTanggal: {appointment_date}\nWaktu: {appointment_time}\nRuang: {room_name}\n\nMohon datang 15 menit sebelumnya.\n\nSalam,\n{clinic_name}');

-- =====================================================
-- END OF UPGRADE SCRIPT
-- =====================================================