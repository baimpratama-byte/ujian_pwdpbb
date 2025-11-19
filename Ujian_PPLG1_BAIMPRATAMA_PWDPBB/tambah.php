<?php
// create.php
require_once __DIR__ . '/koneksi.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nidn = strtoupper(trim($_POST['nidn'] ?? ''));
    $nama = trim($_POST['nama_dosen'] ?? '');
    $tgl = trim($_POST['tgl_mulai_tugas'] ?? null);
    $jenjang = trim($_POST['jenjang_pendidikan'] ?? '');
    $bidang = trim($_POST['bidang_keilmuan'] ?? '');

    // validasi sederhana
    if ($nidn === '') $errors[] = "NIDN wajib diisi.";
    if (strlen($nidn) > 10) $errors[] = "NIDN maksimal 10 karakter.";
    if ($nama === '') $errors[] = "Nama wajib diisi.";

    // cek duplicate nidn
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM baimpratama WHERE nidn = :nidn");
        $stmt->execute([':nidn' => $nidn]);
        if ($stmt->fetchColumn() > 0) $errors[] = "NIDN sudah terdaftar.";
    }

    // handle upload foto
    $foto_name = null;
    if (empty($errors) && isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['foto'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload gagal (kode error {$f['error']}).";
        } else {
            $allowed = ['image/jpeg','image/png','image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) {
                $errors[] = "Tipe file tidak diijinkan. Hanya JPG/PNG/GIF.";
            } elseif ($f['size'] > 2 * 1024 * 1024) {
                $errors[] = "Ukuran file maksimal 2MB.";
            } else {
           
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $foto_name = $nidn . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $foto_name)) {
                    $errors[] = "Gagal menyimpan file.";
                    $foto_name = null;
                }
            }
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO baimpratama (nidn, nama_dosen, tgl_mulai_tugas, jenjang_pendidikan, bidang_keilmuan, foto_dosen)
                VALUES (:nidn, :nama, :tgl, :jenjang, :bidang, :foto)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nidn'=>$nidn, ':nama'=>$nama, ':tgl'=>$tgl ?: null,
            ':jenjang'=>$jenjang, ':bidang'=>$bidang, ':foto'=>$foto_name
        ]);
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tambah Dosen</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Tambah Dosen</h1>
      <div class="actions">
        <a class="btn ghost" href="index.php">Kembali</a>
      </div>
    </div>

    <?php if($errors): ?>
      <div class="card">
        <strong>Errors:</strong>
        <ul><?php foreach($errors as $e): ?><li><?= e($e) ?></li><?php endforeach;?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card" style="margin-top:12px;">
      <div class="form-row">
        <div class="form-col">
          <label class="label">NIDN (primary key)</label>
          <input class="input" name="nidn" required maxlength="10" value="<?= e($_POST['nidn'] ?? '') ?>">
        </div>
        <div class="form-col">
          <label class="label">Nama Dosen</label>
          <input class="input" name="nama_dosen" required value="<?= e($_POST['nama_dosen'] ?? '') ?>">
        </div>
        <div class="form-col">
          <label class="label">Tanggal Mulai Tugas</label>
          <input class="input" type="date" name="tgl_mulai_tugas" value="<?= e($_POST['tgl_mulai_tugas'] ?? '') ?>">
        </div>
        <div class="label"><label for="jenjang_pendidikan" >jenjang Pendidikan</label><br><br>
        <input type="text" type="text" class="input" name="jenjang_pendidikan" value="<?= e($_POST['jenjang_pendidikan'] ?? '') ?>"></div>

        
        <div style="flex-basis:100%;">
          <label class="label">Foto Dosen (jpg/png/gif, max 2MB)</label>
          <input class="input" type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div style="margin-top:12px;">
        <button class="btn" type="submit">Simpan</button>
        <a class="btn ghost" href="index.php">Batal</a>
      </div>
    </form>
  </div>
</body>
</html>
