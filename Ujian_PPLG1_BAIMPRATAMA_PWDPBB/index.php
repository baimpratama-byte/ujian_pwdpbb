<?php
require_once __DIR__ . '/koneksi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$params = [];
$sql = "SELECT * FROM baimpratama";
if ($q !== '') {
    // gunakan parameter unik untuk tiap kolom agar tidak error saat execute
    $sql .= " WHERE nama_dosen LIKE :kw1 OR nidn LIKE :kw2 OR bidang_keilmuan LIKE :kw3";
    $params[':kw1'] = "%$q%";
    $params[':kw2'] = "%$q%";
    $params[':kw3'] = "%$q%";
}
$sql .= " ORDER BY nama_dosen ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Daftar Dosen - BaimPratama</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Data Dosen (table: baimpratama)</h1>
      <div class="actions">
        <a class="btn" href="tambah.php">+ Tambah Dosen</a>
      </div>
    </div>

    <!-- perbaikan form pencarian -->
    <form method="get" class="search-form" style="margin-bottom:12px;">
      <input class="input" type="text" name="q" maxlength="255" placeholder="Cari nama, NIDN atau bidang keilmuan..." value="<?= e($q) ?>">
      <button class="btn" type="submit" aria-label="Cari">Cari</button>
      <a class="btn ghost" href="index.php">Reset</a>
    </form>

    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>NIDN</th>
          <th>Nama</th>
          <th>Tgl Mulai</th>
          <th>Jenjang</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!$rows): ?>
          <tr><td colspan="8" class="small">Belum ada data.</td></tr>
        <?php else: foreach($rows as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($r['nidn']) ?></td>
            <td><?= e($r['nama_dosen']) ?></td>
            <td><?= e($r['tgl_mulai_tugas']) ?></td>
            <td><?= e($r['jenjang_pendidikan']) ?></td>
            <td>
              <?php if(!empty($r['foto_dosen']) && file_exists(__DIR__ . '/uploads/' . $r['foto_dosen'])): ?>
                <img class="avatar" src="uploads/<?= e($r['foto_dosen']) ?>" alt="">
              <?php else: ?>
                <span class="small">-</span>
              <?php endif; ?>
            </td>
            <td class="row-actions">
              <a href="detail.php?nidn=<?= e($r['nidn']) ?>">Detail</a>
              <a href="edit.php?nidn=<?= e($r['nidn']) ?>">Edit</a>
              <a href="hapus.php?nidn=<?= e($r['nidn']) ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

  </div>
</body>
</html>
