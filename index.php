<?php
require 'config.php';

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
if ($cari !== '') {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM rumah WHERE nama_pemilik LIKE ? OR alamat LIKE ? ORDER BY id DESC');
    $stmt->execute(array("%$cari%", "%$cari%"));
} else {
    $pdo = db();
    $stmt = $pdo->query('SELECT * FROM rumah ORDER BY id DESC');
}
$rumah = $stmt->fetchAll();
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$daftarPesan = array(
    'tambah' => 'Data rumah berhasil ditambahkan.',
    'ubah'   => 'Data rumah berhasil diperbarui.',
    'hapus'  => 'Data rumah berhasil dihapus.',
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Alamat Rumah</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <h1>📍 Sistem Informasi Alamat Rumah Berbasis GPS</h1>
    <a href="form.php" class="btn btn-primary">+ Tambah Rumah</a>
</header>

<main class="container">
    <?php if (isset($daftarPesan[$pesan])): ?>
        <div class="alert"><?= e($daftarPesan[$pesan]) ?></div>
    <?php endif; ?>

    <section class="card">
        <h2>Peta Lokasi Rumah <small>(<?= count($rumah) ?> titik)</small></h2>
        <div id="map" class="map"></div>
    </section>

    <section class="card">
        <div class="card-head">
            <h2>Data Rumah</h2>
            <form method="get" class="search">
                <input type="text" name="cari" placeholder="Cari nama / alamat..." value="<?= e($cari) ?>">
                <button class="btn">Cari</button>
                <?php if ($cari !== ''): ?><a href="index.php" class="btn btn-light">Reset</a><?php endif; ?>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>No</th><th>Nama Pemilik</th><th>Alamat Lengkap</th>
                    <th>Latitude</th><th>Longitude</th><th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$rumah): ?>
                    <tr><td colspan="6" class="empty">Belum ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($rumah as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($r['nama_pemilik']) ?></td>
                        <td><?= e($r['alamat']) ?></td>
                        <td><?= e($r['latitude']) ?></td>
                        <td><?= e($r['longitude']) ?></td>
                        <td class="aksi">
                            <button class="btn btn-sm" onclick="lihat(<?= (int) $r['id'] ?>)">Lihat</button>
                            <a class="btn btn-sm btn-light" href="form.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <form method="post" action="hapus.php" onsubmit="return confirm('Hapus data milik <?= e(addslashes($r['nama_pemilik'])) ?>?')">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const dataRumah = <?= json_encode($rumah, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Peta OpenStreetMap, default ke Indonesia
    const map = L.map('map').setView([-2.5, 118], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const markers = {};
    const bounds = [];

    dataRumah.forEach(r => {
        const lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
        const popup = `<b>${esc(r.nama_pemilik)}</b><br>${esc(r.alamat)}<br>
            <small>${lat}, ${lng}</small><br>
            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank">Rute (Google Maps)</a>`;
        markers[r.id] = L.marker([lat, lng]).addTo(map).bindPopup(popup);
        bounds.push([lat, lng]);
    });

    if (bounds.length === 1) map.setView(bounds[0], 16);
    else if (bounds.length > 1) map.fitBounds(bounds, {padding: [40, 40]});

    function lihat(id) {
        const m = markers[id];
        if (!m) return;
        document.getElementById('map').scrollIntoView({behavior: 'smooth'});
        map.flyTo(m.getLatLng(), 17);
        m.openPopup();
    }
</script>
</body>
</html>
