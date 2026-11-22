<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

logout_user();
set_flash('success', 'Anda telah keluar dari panel admin.');
redirect(url('admin/login.php'));
