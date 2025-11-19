<?php
require_once __DIR__ . '/koneksi.php';
$nidn = isset($_GET['nidn']) ? strtoupper(trim($_GET['nidn'])) : '';
if (!$nidn) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM baimpratama WHERE nidn = :nidn");
$stmt->execute([':nidn'=>$nidn]);
$data = $stmt->fetch();
if (!$data) { echo "Data tidak ditemukan."; exit; }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Detail Dosen</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Detail Dosen</h1>
      <div class="actions">
        <a class="btn ghost" href="index.php">Kembali</a>
        <a class="btn" href="edit.php?nidn=<?= e($data['nidn']) ?>">Edit</a>
      </div>
    </div>

    <div class="card">
      <?php if(!empty($data['foto_dosen']) && file_exists(__DIR__ . '/uploads/' . $data['foto_dosen'])): ?>
        <p><img class="avatar" src="uploads/<?= e($data['foto_dosen']) ?>" alt=""></p>
      <?php endif; ?>
      <p><strong>NIDN:</strong> <?= e($data['nidn']) ?></p>
      <p><strong>Nama:</strong> <?= e($data['nama_dosen']) ?></p>
      <p><strong>Tanggal Mulai Tugas:</strong> <?= e($data['tgl_mulai_tugas']) ?></p>
      <p><strong>Jenjang Pendidikan:</strong> <?= e($data['jenjang_pendidikan']) ?></p>
      <p><strong>Bidang Keilmuan:</strong> <?= e($data['bidang_keilmuan']) ?></p>
    </div>
  </div>
</body>
</html>
