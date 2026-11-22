<?php
require_once __DIR__ . '/../includes/auth.php';

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

require_permission($isEdit ? 'produk.edit' : 'produk.create');

$product = $isEdit ? get_product($id) : null;

if ($isEdit && !$product) {
    set_flash('error', 'Produk tidak ditemukan.');
    redirect(url('admin/produk/index.php'));
}

$form = [
    'nama'      => $product['nama'] ?? '',
    'varian'    => $product['varian'] ?? '',
    'ukuran'    => $product['ukuran'] ?? '',
    'harga'     => $product['harga'] ?? '',
    'gambar'    => $product['gambar'] ?? 'gambar/logo-icon.svg',
    'tag'       => $product['tag'] ?? '',
    'deskripsi' => $product['deskripsi'] ?? '',
    'aktif'     => $product['aktif'] ?? 1,
];

$errors = [];
$oldImage = $product['gambar'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['nama']      = clean_text($_POST['nama'] ?? '', 150);
    $form['varian']    = clean_text($_POST['varian'] ?? '', 200);
    $form['ukuran']    = clean_text($_POST['ukuran'] ?? '', 100);
    $form['harga']     = clean_text($_POST['harga'] ?? '', 20);
    $form['gambar']    = clean_text($_POST['gambar'] ?? '', 255);
    $form['tag']       = clean_text($_POST['tag'] ?? '', 50);
    $form['deskripsi'] = clean_text($_POST['deskripsi'] ?? '', 2000);
    $form['aktif']     = isset($_POST['aktif']) ? 1 : 0;

    if (mb_strlen($form['nama']) < 3) {
        $errors[] = 'Nama produk minimal 3 karakter.';
    }
    if (!valid_price($form['harga'])) {
        $errors[] = 'Harga harus angka antara 0 dan 100.000.000.';
    }
    if ($form['gambar'] === '') {
        $form['gambar'] = 'gambar/logo-icon.svg';
    }
    if (!safe_image_path($form['gambar'])) {
        $errors[] =
            'Path gambar harus berada di folder gambar/ dan berekstensi gambar yang diizinkan.';
    }

    $newUpload = null;
    if (isset($_FILES['upload']) && is_array($_FILES['upload'])) {
        $upload = save_product_image($_FILES['upload']);
        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        } elseif ($upload['path'] !== null) {
            $newUpload = $upload['path'];
            $form['gambar'] = $newUpload;
        }
    }

    if ($errors && $newUpload !== null) {
        remove_uploaded_image($newUpload);
    }

    if (!$errors) {
        $data = [
            $form['nama'],
            $form['varian'],
            $form['ukuran'],
            (int) $form['harga'],
            $form['gambar'],
            $form['tag'],
            $form['deskripsi'],
            $form['aktif'],
        ];

        try {
            if ($isEdit) {
                $data[] = $id;
                $stmt = db()->prepare(
                    'UPDATE produk SET nama=?, varian=?, ukuran=?, harga=?, gambar=?, tag=?, deskripsi=?, aktif=? WHERE id=?'
                );
                $stmt->execute($data);
                log_activity((int) current_user()['id'], 'produk.ubah', 'Produk #' . $id . ' ('
                    . $form['nama'] . ')');

                if ($oldImage !== $form['gambar']) {
                    remove_uploaded_image($oldImage);
                }

                set_flash('success', 'Produk berhasil diperbarui.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO produk (nama, varian, ukuran, harga, gambar, tag, deskripsi, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute($data);
                log_activity((int) current_user()['id'], 'produk.tambah', 'Produk baru: ' .
                    $form['nama'] . ' (#' . db()->lastInsertId() . ')');
                set_flash('success', 'Produk baru berhasil ditambahkan.');
            }
            redirect(url('admin/produk/index.php'));
        } catch (Throwable $e) {
            error_log('[produk] ' . $e->getMessage());
            $errors[] = 'Gagal menyimpan produk. Silakan coba lagi.';
            if ($newUpload !== null) {
                remove_uploaded_image($newUpload);
            }
        }
    }
}

$adminTitle  = $isEdit ? 'Edit Produk' : 'Tambah Produk';
$adminActive = 'produk';
include __DIR__ . '/../includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2><?= $isEdit ? 'Edit Produk: ' . e($product['nama']) : 'Produk Baru' ?></h2>
    <a class="btn-sm btn-sm-ghost" href="<?= url('admin/produk/index.php') ?>">&larr; Kembali</a>
  </div>
  <div class="panel-body">
    <?php if ($errors): ?>
    <div class="flash flash-error">
      <ul>
        <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form"
        action="<?= url('admin/produk/form.php' . ($isEdit ? '?id=' . $id : '')) ?>">
      <?= csrf_field() ?>

      <div>
        <label for="nama">Nama Produk <span class="required">*</span></label>
        <input type="text" id="nama" name="nama" value="<?= e($form['nama']) ?>" required
            maxlength="150">
      </div>

      <div class="form-row">
        <div>
          <label for="varian">Varian Rasa</label>
          <input type="text" id="varian" name="varian" value="<?= e($form['varian']) ?>"
              placeholder="Original, Balado, Keju" maxlength="200">
        </div>
        <div>
          <label for="ukuran">Ukuran</label>
          <input type="text" id="ukuran" name="ukuran" value="<?= e($form['ukuran']) ?>"
              placeholder="50g / 125g / 250g" maxlength="100">
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="harga">Harga (Rp) <span class="required">*</span></label>
          <input type="number" id="harga" name="harga" value="<?= e($form['harga']) ?>" min="0"
              max="100000000" step="500" required>
        </div>
        <div>
          <label for="tag">Label / Tag</label>
          <input type="text" id="tag" name="tag" value="<?= e($form['tag']) ?>"
              placeholder="Terlaris, Favorit, dll." maxlength="50">
        </div>
      </div>

      <div>
        <label for="gambar">Path Gambar</label>
        <input type="text" id="gambar" name="gambar" value="<?= e($form['gambar']) ?>"
            placeholder="gambar/keripiksingkong.jpg" maxlength="255">
        <p class="form-hint">Harus berupa path relatif di folder <code>gambar/</code>, misalnya
            <code>gambar/keripiksingkong.jpg</code>.</p>
        <img class="img-preview"
            src="<?= e(asset((string) ($form['gambar'] ?: 'gambar/logo-icon.svg'))) ?>"
                alt="Pratinjau gambar">
      </div>

      <div>
        <label for="upload">Unggah Gambar Baru (opsional)</label>
        <input type="file" id="upload" name="upload"
            accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="form-hint">Maksimal 3 MB, hanya JPG/PNG/WEBP/GIF. Nama file diacak dan isi
            file diperiksa di server.</p>
      </div>

      <div>
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" maxlength="2000"
            placeholder="Deskripsi singkat produk..."><?= e($form['deskripsi']) ?></textarea>
      </div>

      <div>
        <label class="check-inline">
          <input type="checkbox" name="aktif" value="1" <?= $form['aktif'] ? 'checked' : '' ?>>
          Tampilkan produk di halaman publik
        </label>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Produk'
            ?></button>
        <a class="btn btn-ghost" href="<?= url('admin/produk/index.php') ?>">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
