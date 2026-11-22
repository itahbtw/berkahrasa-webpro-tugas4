<?php
require_once __DIR__ . '/../includes/auth.php';
require_permission('produk.view');

$q = clean_text($_GET['q'] ?? '', 80);
$products = get_products(false);

if ($q !== '') {
    $needle = mb_strtolower($q);
    $products = array_values(array_filter(
        $products,
        static fn (array $p): bool => str_contains(mb_strtolower($p['nama'] . ' ' . $p['varian']
            . ' ' . $p['tag']), $needle)
    ));
}

$adminTitle  = 'Kelola Produk';
$adminActive = 'produk';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>Daftar Produk (<?= count($products) ?>)</h2>
    <div class="panel-tools">
      <form method="get" class="inline-search">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari produk…"
            aria-label="Cari produk">
        <button class="btn-sm btn-sm-ghost" type="submit">Cari</button>
      </form>
      <?php if (user_can('produk.create')): ?>
      <a class="btn-sm btn-sm-primary" href="<?= url('admin/produk/form.php') ?>">+ Tambah
          Produk</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Foto</th><th>Nama
            Produk</th><th>Varian</th><th>Ukuran</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$products): ?>
        <tr><td colspan="7" class="empty-note">Tidak ada produk yang cocok.</td></tr>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><img class="thumb"
              src="<?= e(str_starts_with((string) $p['gambar'], 'gambar/') ? asset((string) $p['gambar']) : url($p['gambar'])) ?>" alt="<?= e($p['nama']) ?>"></td>
          <td><b><?= e($p['nama']) ?></b><?php if ($p['tag']): ?><br><small><?= e($p['tag'])
              ?></small><?php endif; ?></td>
          <td><?= e($p['varian']) ?></td>
          <td><?= e($p['ukuran']) ?></td>
          <td><?= rupiah($p['harga']) ?></td>
          <td><?= $p['aktif'] ? '<span class="badge badge-aktif">Aktif</span>' :
              '<span class="badge badge-nonaktif">Nonaktif</span>' ?></td>
          <td>
            <div class="actions">
              <?php if (user_can('produk.edit')): ?>
              <a class="btn-sm btn-sm-ghost"
                  href="<?= url('admin/produk/form.php?id=' . (int) $p['id']) ?>">Edit</a>
              <?php endif; ?>
              <?php if (user_can('produk.hapus')): ?>
              <form method="post" action="<?= url('admin/produk/delete.php') ?>"
                  onsubmit="return confirm('Hapus produk &quot;<?= e(addslashes($p['nama'])) ?>&quot;?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button class="btn-sm btn-sm-danger" type="submit">Hapus</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="admin-note">Operator dapat menambah dan mengubah produk, tetapi hanya administrator
    yang boleh menghapus produk.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
