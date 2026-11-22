<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesan.hapus');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/pesan/index.php'));
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('SELECT nama FROM pesan_kontak WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $nama = $stmt->fetchColumn();

    if ($nama !== false) {
        db()->prepare('DELETE FROM pesan_kontak WHERE id = ?')->execute([$id]);
        log_activity((int) current_user()['id'], 'pesan.hapus', 'Pesan #' . $id . ' dari ' . $nama);
        set_flash('success', 'Pesan berhasil dihapus.');
    } else {
        set_flash('error', 'Pesan tidak ditemukan.');
    }
} else {
    set_flash('error', 'Pesan tidak valid.');
}

redirect(url('admin/pesan/index.php'));
