<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('produk.hapus');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/produk/index.php'));
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $product = get_product($id);
    if ($product) {
        $stmt = db()->prepare('DELETE FROM produk WHERE id = ?');
        $stmt->execute([$id]);
        remove_uploaded_image($product['gambar']);
        log_activity((int) current_user()['id'], 'produk.hapus', 'Produk #' . $id . ' (' .
            $product['nama'] . ')');
        set_flash('success', 'Produk berhasil dihapus.');
    } else {
        set_flash('error', 'Produk tidak ditemukan.');
    }
} else {
    set_flash('error', 'Produk tidak valid.');
}

redirect(url('admin/produk/index.php'));
