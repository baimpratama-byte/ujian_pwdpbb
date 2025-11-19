<?php
// edit.php
require_once __DIR__ . '/koneksi.php';

$nidn = isset($_GET['nidn']) ? strtoupper(trim($_GET['nidn'])) : '';
if (!$nidn) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM baimpratama WHERE nidn = :nidn");
$stmt->execute([':nidn'=>$nidn]);
$data = $stmt->fetch();
if (!$data) { echo "Data tidak ditemukan."; exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_dosen'] ?? '');
    $tgl = trim($_POST['tgl_mulai_tugas'] ?? null);
    $jenjang = trim($_POST['jenjang_pendidikan'] ?? '');

    if ($nama === '') $errors[] = "Nama wajib diisi.";

    // handle foto baru (jika diupload)
    $foto_name = $data['foto_dosen'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
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
                $newname = $nidn . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $newname)) {
                    $errors[] = "Gagal menyimpan file.";
                } else {
                    // hapus foto lama jika ada
                    if (!empty($foto_name) && file_exists(__DIR__ . '/uploads/' . $foto_name)) {
                        @unlink(__DIR__ . '/uploads/' . $foto_name);
                    }
                    $foto_name = $newname;
                }
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE baimpratama SET nama_dosen=:nama, tgl_mulai_tugas=:tgl, jenjang_pendidikan=:jenjang, bidang_keilmuan=:bidang, foto_dosen=:foto WHERE nidn=:nidn";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama'=>$nama, ':tgl'=>$tgl ?: null,
            ':jenjang'=>$jenjang, ':bidang'=>$bidang, ':foto'=>$foto_name, ':nidn'=>$nidn
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
  <title>Edit Dosen</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Edit Dosen</h1>
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
          <label class="label">NIDN (tidak bisa diubah)</label>
          <input class="input" name="nidn" value="<?= e($data['nidn']) ?>" disabled>
        </div>
        <div class="form-col">
          <label class="label">Nama Dosen</label>
          <input class="input" name="nama_dosen" required value="<?= e($_POST['nama_dosen'] ?? $data['nama_dosen']) ?>">
        </div>
        <div class="form-col">
          <label class="label">Tanggal Mulai Tugas</label>
          <input class="input" type="date" name="tgl_mulai_tugas" value="<?= e($_POST['tgl_mulai_tugas'] ?? $data['tgl_mulai_tugas']) ?>">
        </div>
        <div class="form-col">
          <label class="label">Jenjang Pendidikan</label>
          <input class="input" name="jenjang_pendidikan" value="<?= e($_POST['jenjang_pendidikan'] ?? $data['jenjang_pendidikan']) ?>">
        </div>
    
        <div style="flex-basis:100%;">
          <label class="label">Foto Dosen (kosongkan bila tidak ingin mengganti)</label><br>
          <?php if(!empty($data['foto_dosen']) && file_exists(__DIR__ . '/uploads/' . $data['foto_dosen'])): ?>
            <img class="avatar" src="uploads/<?= e($data['foto_dosen']) ?>" alt=""><br><br>
          <?php endif; ?>
          <input class="input" type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div style="margin-top:12px;">
        <button class="btn" type="submit">Update</button>
        <a class="btn ghost" href="index.php">Batal</a>
      </div>
    </form>
  </div>
</body>
</html>
