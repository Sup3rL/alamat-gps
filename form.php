<?php
require 'config.php';

$data = array('id' => '', 'nama_pemilik' => '', 'alamat' => '', 'latitude' => '', 'longitude' => '');
$error = '';

if (isset($_GET['id'])) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM rumah WHERE id = ?');
    $stmt->execute(array((int) $_GET['id']));
    $data = $stmt->fetch() ?: die('Data tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array(
        'id'           => isset($_POST['id']) ? (int) $_POST['id'] : 0,
        'nama_pemilik' => isset($_POST['nama_pemilik']) ? trim($_POST['nama_pemilik']) : '',
        'alamat'       => isset($_POST['alamat']) ? trim($_POST['alamat']) : '',
        'latitude'     => isset($_POST['latitude']) ? trim($_POST['latitude']) : '',
        'longitude'    => isset($_POST['longitude']) ? trim($_POST['longitude']) : '',
    );

    if ($data['nama_pemilik'] === '' || $data['alamat'] === '') {
        $error = 'Nama pemilik dan alamat wajib diisi.';
    } elseif (!is_numeric($data['latitude']) || $data['latitude'] < -90 || $data['latitude'] > 90) {
        $error = 'Latitude harus berupa angka antara -90 sampai 90.';
    } elseif (!is_numeric($data['longitude']) || $data['longitude'] < -180 || $data['longitude'] > 180) {
        $error = 'Longitude harus berupa angka antara -180 sampai 180.';
    } else {
        $params = array($data['nama_pemilik'], $data['alamat'], (float) $data['latitude'], (float) $data['longitude']);
        $pdo = db();
        if ($data['id']) {
            $params[] = $data['id'];
            $stmt = $pdo->prepare('UPDATE rumah SET nama_pemilik=?, alamat=?, latitude=?, longitude=? WHERE id=?');
            $stmt->execute($params);
            header('Location: index.php?pesan=ubah');
        } else {
            $stmt = $pdo->prepare('INSERT INTO rumah (nama_pemilik, alamat, latitude, longitude) VALUES (?,?,?,?)');
            $stmt->execute($params);
            header('Location: index.php?pesan=tambah');
        }
        exit;
    }
}
$judul = $data['id'] ? 'Edit Data Rumah' : 'Tambah Data Rumah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <h1>📍 <?= e($judul) ?></h1>
    <a href="index.php" class="btn btn-light">← Kembali</a>
</header>

<main class="container grid">
    <section class="card">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form">
            <input type="hidden" name="id" value="<?= e($data['id']) ?>">

            <label>Nama Pemilik Rumah
                <input type="text" name="nama_pemilik" maxlength="100" required value="<?= e($data['nama_pemilik']) ?>">
            </label>

            <label>Alamat Lengkap
                <textarea name="alamat" rows="3" required><?= e($data['alamat']) ?></textarea>
            </label>

            <div class="row">
                <label>Latitude
                    <input type="text" name="latitude" id="lat" required value="<?= e($data['latitude']) ?>">
                </label>
                <label>Longitude
                    <input type="text" name="longitude" id="lng" required value="<?= e($data['longitude']) ?>">
                </label>
            </div>

            <button type="button" class="btn btn-gps" id="btnGps">🛰️ Ambil Lokasi GPS Saat Ini</button>
            <p id="gpsStatus" class="hint">Atau klik / geser penanda pada peta untuk menentukan koordinat.</p>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </section>

    <section class="card">
        <h2>Tentukan Lokasi di Peta</h2>
        <div id="map" class="map map-form"></div>
    </section>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const inLat = document.getElementById('lat');
    const inLng = document.getElementById('lng');
    const status = document.getElementById('gpsStatus');

    const map = L.map('map').setView([-2.5, 118], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setPosisi(lat, lng, zoom) {
        lat = Number(lat).toFixed(8);
        lng = Number(lng).toFixed(8);
        inLat.value = lat;
        inLng.value = lng;
        if (!marker) {
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            marker.on('dragend', ev => {
                const p = ev.target.getLatLng();
                setPosisi(p.lat, p.lng);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        if (zoom) map.setView([lat, lng], zoom);
    }

    const valid = (lat, lng) => !isNaN(lat) && !isNaN(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;

    // Data lama (mode edit)
    if (inLat.value && inLng.value && valid(+inLat.value, +inLng.value)) {
        setPosisi(inLat.value, inLng.value, 17);
    }

    // Klik peta -> set koordinat
    map.on('click', ev => setPosisi(ev.latlng.lat, ev.latlng.lng));

    // Ketik manual -> pindahkan penanda
    [inLat, inLng].forEach(el => el.addEventListener('change', () => {
        if (valid(parseFloat(inLat.value), parseFloat(inLng.value))) {
            setPosisi(inLat.value, inLng.value, 17);
        }
    }));

    // Ambil koordinat dari GPS perangkat (Geolocation API)
    document.getElementById('btnGps').addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Browser tidak mendukung Geolocation.';
            return;
        }
        status.textContent = 'Mengambil lokasi GPS...';
        navigator.geolocation.getCurrentPosition(pos => {
            setPosisi(pos.coords.latitude, pos.coords.longitude, 18);
            status.textContent = 'Lokasi didapat (akurasi ± ' + Math.round(pos.coords.accuracy) + ' m).';
        }, err => {
            status.textContent = 'Gagal mengambil lokasi: ' + err.message;
        }, {enableHighAccuracy: true, timeout: 15000});
    });
</script>
</body>
</html>
