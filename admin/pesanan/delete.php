<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesanan.hapus');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/pesanan/index.php'));
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('SELECT kode FROM pesanan WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $kode = $stmt->fetchColumn();

    if ($kode !== false) {
        db()->prepare('DELETE FROM pesanan WHERE id = ?')->execute([$id]);
        log_activity((int) current_user()['id'], 'pesanan.hapus', 'Pesanan #' . $id . ' (' .
            $kode . ')');
        set_flash('success', 'Pesanan berhasil dihapus.');
    } else {
        set_flash('error', 'Pesanan tidak ditemukan.');
    }
} else {
    set_flash('error', 'Pesanan tidak valid.');
}

redirect(url('admin/pesanan/index.php'));
