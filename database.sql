-- Jalankan file ini di phpMyAdmin (tab SQL) atau: mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS db_alamat_gps CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_alamat_gps;

CREATE TABLE IF NOT EXISTS rumah (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nama_pemilik VARCHAR(100)   NOT NULL,
    alamat       TEXT           NOT NULL,
    latitude     DECIMAL(10, 8) NOT NULL,
    longitude    DECIMAL(11, 8) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data contoh
INSERT INTO rumah (nama_pemilik, alamat, latitude, longitude) VALUES
('Budi Santoso',  'Jl. Malioboro No. 52, Sosromenduran, Gedong Tengen, Kota Yogyakarta', -7.79277000, 110.36588000),
('Siti Aminah',   'Jl. Kaliurang Km 5, Caturtunggal, Depok, Sleman, DI Yogyakarta',       -7.75987000, 110.38057000),
('Andi Wijaya',   'Jl. Parangtritis No. 120, Mantrijeron, Kota Yogyakarta',                -7.82191000, 110.36531000);
