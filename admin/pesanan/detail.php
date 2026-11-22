<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('pesanan.view');

$id = (int) ($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT p.*, u.username AS akun_username, u.nama AS akun_nama, u.email AS akun_email
     FROM pesanan p
     LEFT JOIN users u ON u.id = p.user_id
     WHERE p.id = ? LIMIT 1'
);
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Pesanan tidak ditemukan.');
    redirect(url('admin/pesanan/index.php'));
}

$statuses = pesanan_statuses();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('pesanan.status');
    verify_csrf();

    $status = clean_text($_POST['status'] ?? '', 20);
    $allowed = allowed_order_statuses((string) current_user()['role'], (string) $order['status']);

    if (isset($statuses[$status]) && in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        log_activity(
            (int) current_user()['id'],
            'pesanan.status',
            'Pesanan ' . $order['kode'] . ': ' . $order['status'] . ' -> ' . $status
        );
        set_flash('success', 'Status pesanan diperbarui menjadi "' . $statuses[$status] . '".');
    } else {
        set_flash('error', 'Status tidak valid untuk alur pesanan ini.');
    }

    redirect(url('admin/pesanan/detail.php?id=' . $id));
}

$stmt = $pdo->prepare('SELECT * FROM pesanan_detail WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->execute([$id]);
$details = $stmt->fetchAll();

$wa = normalize_wa((string) $order['no_wa']);
$selectableStatuses = allowed_order_statuses((string) current_user()['role'], (string)
    $order['status']);

$adminTitle  = 'Detail Pesanan';
$adminActive = 'pesanan';
include __DIR__ . '/../includes/header.php';
?>

<div class="detail-grid">
  <div class="panel">
    <div class="panel-head">
      <h2>Item Pesanan</h2>
      <a class="btn-sm btn-sm-ghost" href="<?= url('admin/pesanan/index.php') ?>">&larr; Kembali</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php if (!$details): ?>
          <tr><td colspan="4" class="empty-note">Tidak ada item pada pesanan ini.</td></tr>
          <?php endif; ?>
          <?php foreach ($details as $d): ?>
          <tr>
            <td><?= e($d['nama_produk']) ?></td>
            <td><?= rupiah($d['harga']) ?></td>
            <td><?= (int) $d['qty'] ?></td>
            <td><?= rupiah($d['subtotal']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-right">Total</th>
            <th><?= rupiah($order['total']) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <?php if ($order['akun_username']): ?>
    <div class="account-owner">
      <h3>Akun Pemesan</h3>
      <p><b>@<?= e($order['akun_username']) ?></b> · <?= e($order['akun_nama']) ?> · <?=
          e($order['akun_email']) ?></p>
      <p class="admin-note">Pesanan tertaut ke akun pelanggan. Pemilik dapat memantau pesanan di
          halaman “Pesanan Saya”.</p>
    </div>
    <?php else: ?>
    <p class="admin-note">Pesanan ini dibuat sebagai tamu (tanpa akun).</p>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2>Info Pesanan</h2>
      <?= status_badge($order['status']) ?>
    </div>
    <div class="panel-body">
      <ul class="detail-list">
        <li><span class="k">Kode</span><span class="v"><?= e($order['kode']) ?></span></li>
        <li><span class="k">Nama</span><span class="v"><?= e($order['nama_pembeli']) ?></span></li>
        <li><span class="k">Email</span><span class="v"><?= e($order['email'] ?: '-') ?></span></li>
        <li><span class="k">WhatsApp</span><span class="v"><?= e($order['no_wa']) ?></span></li>
        <li><span class="k">Pembayaran</span><span class="v"><?= $order['pembayaran'] ===
            'transfer' ? 'Transfer Bank — BCA ' . e(BCA_ACCOUNT) : 'Bayar di Tempat (COD)'
                ?></span></li>
        <li><span class="k">Subtotal</span><span class="v"><?= rupiah((int) $order['total'] -
            (int) ($order['ongkir'] ?? 0)) ?></span></li>
        <li><span class="k">Ongkir</span><span class="v"><?= (int) ($order['ongkir'] ?? 0) > 0 ?
            rupiah((int) $order['ongkir']) : 'Gratis' ?></span></li>
        <li><span class="k">Tanggal</span><span class="v"><?=
            e(format_tanggal($order['created_at'])) ?></span></li>
        <li><span class="k">Alamat</span><span class="v"><?= nl2br(e($order['alamat'] ?: '-'))
            ?></span></li>
        <li><span class="k">Catatan</span><span class="v"><?= nl2br(e($order['catatan'] ?: '-'))
            ?></span></li>
      </ul>

      <?php if ($wa !== ''): ?>
      <p class="mt-1">
        <a class="btn-sm btn-sm-primary" href="https://wa.me/<?= e($wa) ?>" target="_blank"
            rel="noopener">Chat Pembeli</a>
      </p>
      <?php endif; ?>

      <hr class="admin-divider">

      <?php if (user_can('pesanan.status')): ?>
      <h3 class="detail-section-title">Ubah Status</h3>
      <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <div>
          <select name="status">
            <?php foreach ($selectableStatuses as $key): ?>
            <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : ''
                ?>><?= e($statuses[$key]) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="form-hint">
            <?= current_user()['role'] === 'admin'
                ? 'Administrator dapat memilih semua status.'
                : 'Operator mengikuti alur: Baru → Diproses → Dikirim → Selesai.' ?>
          </p>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit">Simpan Status</button>
        </div>
      </form>
      <?php endif; ?>

      <?php if (user_can('pesanan.hapus')): ?>
      <form method="post" action="<?= url('admin/pesanan/delete.php') ?>"
          onsubmit="return confirm('Hapus pesanan ini?')" class="mt-sm">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
        <button class="btn-sm btn-sm-danger" type="submit">Hapus Pesanan</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
