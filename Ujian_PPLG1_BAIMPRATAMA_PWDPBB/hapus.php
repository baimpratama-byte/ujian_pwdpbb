<?php
require_once __DIR__ . '/koneksi.php';
$nidn = isset($_GET['nidn']) ? strtoupper(trim($_GET['nidn'])) : '';
if ($nidn) {

    $stmt = $pdo->prepare("SELECT foto_dosen FROM baimpratama WHERE nidn = :nidn");
    $stmt->execute([':nidn'=>$nidn]);
    $row = $stmt->fetch();
    if ($row) {
        if (!empty($row['foto_dosen']) && file_exists(__DIR__ . '/uploads/' . $row['foto_dosen'])) {
            @unlink(__DIR__ . '/uploads/' . $row['foto_dosen']);
        }
        $del = $pdo->prepare("DELETE FROM baimpratama WHERE nidn = :nidn");
        $del->execute([':nidn'=>$nidn]);
    }
}
header('Location: index.php');
exit;
