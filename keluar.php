<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$wasStaff = is_staff();
logout_user();

set_flash('success', 'Anda telah keluar. Sesi dan cookie login sudah dihapus.');
redirect($wasStaff ? url('admin/login.php') : url('masuk.php'));
