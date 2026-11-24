# Sistem Informasi Alamat Rumah Berbasis GPS

Aplikasi web (PHP + MySQL + Leaflet/OpenStreetMap) untuk mengelola data alamat rumah
dan menampilkan lokasinya di peta digital.

## Fitur
- CRUD data rumah: nama pemilik, alamat lengkap, latitude, longitude
- Peta OpenStreetMap berisi penanda (marker) semua rumah, popup info + link rute Google Maps
- Ambil koordinat otomatis dari GPS perangkat (HTML5 Geolocation API)
- Tentukan koordinat dengan klik peta / geser penanda
- Pencarian berdasarkan nama atau alamat, tombol "Lihat" untuk zoom ke rumah tertentu
- Validasi input dan prepared statement (aman dari SQL injection)

## Struktur
| File | Fungsi |
|---|---|
| `config.php` | Koneksi database (PDO) |
| `database.sql` | Skema tabel `rumah` + data contoh |
| `index.php` | Peta semua rumah + tabel data + pencarian |
| `form.php` | Form tambah/edit dengan peta & tombol GPS |
| `hapus.php` | Hapus data |
| `assets/style.css` | Tampilan |

## Cara Menjalankan (XAMPP)
1. Salin folder `alamat-gps` ke `C:\xampp\htdocs\`.
2. Nyalakan Apache dan MySQL di XAMPP Control Panel.
3. Buka phpMyAdmin → tab **SQL** → jalankan isi `database.sql`.
4. Sesuaikan user/password di `config.php` jika perlu.
5. Buka `http://localhost/alamat-gps/`.

> Tombol GPS hanya berfungsi di `localhost` atau situs HTTPS (aturan keamanan browser).
> Peta membutuhkan koneksi internet untuk memuat tile OpenStreetMap.

### Tanpa MySQL (opsional)
```bash
DB_DRIVER=sqlite php -S localhost:8000
```
