/* ============================================================
   Berkah Rasa — Dynamic Content Script (Tugas 4)
   ============================================================ */

const ASSET_VERSION = window.__BERKAH_ASSET_VERSION__ || '4.7.5';

const FALLBACK_PRODUCTS = [
  {
    id: 1,
    name: 'Keripik Singkong Original',
    variant: 'Gurih asin, iris tipis renyah',
    size: '50g / 125g / 250g',
    price: 5000,
    img: 'gambar/keripiksingkong.jpg?v=' + ASSET_VERSION,
    tag: 'Terlaris',
  },
  {
    id: 2,
    name: 'Keripik Singkong Balado',
    variant: 'Manis pedas, bumbu melimpah',
    size: '50g / 125g / 250g',
    price: 6000,
    img: 'gambar/keripik-balado.jpg',
    tag: 'Favorit',
  },
  {
    id: 3,
    name: 'Stik Singkong Keju',
    variant: 'Keju original & pedas',
    size: 'Kecil / Besar',
    price: 8000,
    img: 'gambar/stiksingkongkeju.webp',
    tag: 'Rekomendasi',
  },
  {
    id: 4,
    name: 'Opak Singkong',
    variant: 'Original gurih, mekar saat digoreng',
    size: 'Isi 10 keping',
    price: 10000,
    img: 'gambar/opak-bersih.jpg',
    tag: 'Tradisional',
  },
  {
    id: 5,
    name: 'Tiwul Instan Gula Aren',
    variant: 'Manis legit, praktis diseduh',
    size: '250g',
    price: 7000,
    img: 'gambar/tiwul-gula-aren.jpg',
    tag: 'Khas Desa',
  },
  {
    id: 6,
    name: 'Gatot Kering',
    variant: 'Gurih kenyal, taburan kelapa',
    size: '250g',
    price: 7000,
    img: 'gambar/gatot.jpg',
    tag: 'Langka',
  },
];

const CART_KEY = 'berkahrasa_cart';
const PRODUCTS =
  Array.isArray(window.__BERKAH_PRODUCTS__) && window.__BERKAH_PRODUCTS__.length
    ? window.__BERKAH_PRODUCTS__
    : FALLBACK_PRODUCTS;
const ORDER_API = window.__BERKAH_API__ || 'api/order.php';
const CSRF_TOKEN =
  window.__BERKAH_CSRF__ || document.querySelector('meta[name="csrf-token"]')?.content || '';

let cart = [];
try {
  cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
} catch (e) {
  cart = [];
}
if (!Array.isArray(cart)) cart = [];

/* ---------- helpers ---------- */
const rupiah = (n) => 'Rp' + Number(n).toLocaleString('id-ID');
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

function escapeHtml(value) {
  return String(value ?? '').replace(
    /[&<>"']/g,
    (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch],
  );
}

function setCookie(name, value, days) {
  const base = String(window.__BERKAH_BASE__ || '').replace(/\/+$/, '');
  const path = base === '' ? '/' : base + '/';
  const expires = new Date(Date.now() + days * 86400000).toUTCString();
  const secure = location.protocol === 'https:' ? '; Secure' : '';
  document.cookie =
    name +
    '=' +
    encodeURIComponent(value) +
    '; expires=' +
    expires +
    '; path=' +
    path +
    '; SameSite=Lax' +
    secure;
}

function saveCart() {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch (e) {
    /* storage penuh / mode privat */
  }
  updateCartUI();
}

/* ---------- toast ---------- */
let toastTimer;
function showToast(msg) {
  let t = $('#toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast';
    t.className = 'toast';
    t.setAttribute('role', 'status');
    t.setAttribute('aria-live', 'polite');
    document.body.appendChild(t);
  }
  t.innerHTML =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span></span>';
  t.querySelector('span').textContent = String(msg ?? '');
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

/* ---------- products ---------- */
function productCard(p) {
  const id = Number(p.id) || 0;
  const name = escapeHtml(p.name);
  const variant = escapeHtml(p.variant);
  const size = escapeHtml(p.size);
  const img = escapeHtml(p.img);
  const tag = escapeHtml(p.tag);

  return `
  <article class="product-card reveal">
    <div class="product-media">
      <span class="product-tag">${tag}</span>
      <img src="${img}" alt="${name}" loading="lazy">
    </div>
    <div class="product-body">
      <h3>${name}</h3>
      <span class="variant">${variant}</span>
      <span class="size">Ukuran: ${size}</span>
      <button class="quick-view-btn" onclick="openProduct(${id})">Lihat detail →</button>
      <div class="product-foot">
        <span class="price">${rupiah(p.price)} <small>/ bungkus</small></span>
        <button class="add-btn" data-id="${id}" onclick="addToCart(${id}, this)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
          Tambah
        </button>
      </div>
    </div>
  </article>`;
}

function renderProducts(filter) {
  const grid = $('#productGrid');
  if (!grid) return;
  const q = (filter || '').toLowerCase().trim();
  const list = PRODUCTS.filter(
    (p) =>
      String(p.name || '')
        .toLowerCase()
        .includes(q) ||
      String(p.variant || '')
        .toLowerCase()
        .includes(q),
  );
  grid.innerHTML = list.length
    ? list.map(productCard).join('')
    : '<p class="empty-state">Produk tidak ditemukan. Coba kata kunci lain.</p>';
  observeReveals();
}

/* ---------- cart ---------- */
function addToCart(id, btn) {
  const productId = Number(id) || 0;
  const p = PRODUCTS.find((x) => Number(x.id) === productId);
  if (!p) return;
  const item = cart.find((x) => Number(x.id) === productId);
  if (item) item.qty++;
  else cart.push({ id: productId, qty: 1 });
  saveCart();
  if (btn) {
    btn.classList.add('added');
    btn.textContent = 'Ditambahkan';
    setTimeout(() => {
      btn.classList.remove('added');
      btn.innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg> Tambah';
    }, 1100);
  }
  showToast(p.name + ' ditambahkan ke keranjang');
}

function changeQty(id, delta) {
  const productId = Number(id) || 0;
  const item = cart.find((x) => Number(x.id) === productId);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter((x) => Number(x.id) !== productId);
  saveCart();
  renderCartItems();
}

function removeItem(id) {
  const productId = Number(id) || 0;
  cart = cart.filter((x) => Number(x.id) !== productId);
  saveCart();
  renderCartItems();
}

function cartTotal() {
  return cart.reduce((sum, i) => {
    const p = PRODUCTS.find((x) => Number(x.id) === Number(i.id));
    return sum + (p ? Number(p.price) * i.qty : 0);
  }, 0);
}

function ongkirAmount(subtotal) {
  const cfg = window.__BERKAH_ONGKIR__ || { threshold: 100000, fee: 10000 };
  const threshold = Number(cfg.threshold) || 100000;
  const fee = Number(cfg.fee) || 10000;
  return subtotal > 0 && subtotal < threshold ? fee : 0;
}

function grandTotal() {
  const subtotal = cartTotal();
  return subtotal + ongkirAmount(subtotal);
}

function updateCartUI() {
  const count = cart.reduce((s, i) => s + i.qty, 0);
  $$('#cartCount').forEach((el) => (el.textContent = count));
  $$('.cart-btn').forEach((el) => el.classList.toggle('has-items', count > 0));
}

function renderCartItems() {
  const box = $('#cartItems');
  if (!box) return;
  if (!cart.length) {
    box.innerHTML =
      '<div class="cart-empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><p>Keranjang masih kosong.<br>Yuk pilih camilan favoritmu!</p></div>';
    return;
  }
  box.innerHTML = cart
    .map((i) => {
      const p = PRODUCTS.find((x) => Number(x.id) === Number(i.id));
      if (!p) return '';
      const id = Number(p.id) || 0;
      return `
    <div class="cart-item">
      <img src="${escapeHtml(p.img)}" alt="${escapeHtml(p.name)}">
      <div class="ci-info">
        <b>${escapeHtml(p.name)}</b>
        <span>${rupiah(p.price)}</span>
        <div class="qty">
          <button onclick="changeQty(${id}, -1)" aria-label="kurangi">−</button>
          <span>${i.qty}</span>
          <button onclick="changeQty(${id}, 1)" aria-label="tambah">+</button>
        </div>
      </div>
      <button class="ci-remove" onclick="removeItem(${id})">Hapus</button>
    </div>`;
    })
    .join('');
  const totalEl = $('#cartTotal');
  if (totalEl) totalEl.textContent = rupiah(grandTotal());
}

/* ---------- quick view produk ---------- */
function openProduct(id) {
  const productId = Number(id) || 0;
  const p = PRODUCTS.find((x) => Number(x.id) === productId);
  if (!p) return;

  const modal = $('#productModal');
  if (!modal) return;

  $('#quickImg').src = p.img;
  $('#quickImg').alt = p.name;
  $('#quickTag').textContent = p.tag || 'Produk Berkah Rasa';
  $('#quickName').textContent = p.name;
  $('#quickVariant').textContent = p.variant || '';
  $('#quickSize').textContent = p.size || '';
  $('#quickPrice').textContent = rupiah(p.price);
  $('#quickDesc').textContent =
    p.desc ||
    `${p.name} — camilan singkong pilihan Berkah Rasa, dibuat dengan bahan alami dan proses higienis.`;
  $('#quickAdd').onclick = function () {
    addToCart(productId, null);
    closeProduct();
  };

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeProduct() {
  $('#productModal')?.classList.remove('open');
  document.body.style.overflow = '';
}

function openCart() {
  renderCartItems();
  $('#cartDrawer')?.classList.add('open');
  $('#cartOverlay')?.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  $('#cartDrawer')?.classList.remove('open');
  $('#cartOverlay')?.classList.remove('open');
  document.body.style.overflow = '';
}

function goToCheckout() {
  if (!cart.length) {
    showToast('Keranjang masih kosong');
    return;
  }
  closeCart();
  window.location.href = (window.__BERKAH_BASE__ || '') + '/checkout.php';
}

function renderCheckoutSummary() {
  const box = $('#checkoutItems');
  if (!box) return;

  if (!cart.length) {
    box.innerHTML =
      '<p class="muted-text">Keranjang masih kosong. Silakan pilih produk terlebih dahulu.</p>';
    ['#checkoutSubtotal', '#checkoutOngkir', '#checkoutTotal'].forEach((sel) => {
      const el = $(sel);
      if (el) el.textContent = rupiah(0);
    });
    const note = $('#checkoutOngkirNote');
    if (note) note.textContent = '';
    const btn = $('#coSubmit');
    if (btn) btn.disabled = true;
    return;
  }

  box.innerHTML = cart
    .map((i) => {
      const p = PRODUCTS.find((x) => Number(x.id) === Number(i.id));
      if (!p) return '';
      return `<div class="checkout-item">
      <img src="${escapeHtml(p.img)}" alt="${escapeHtml(p.name)}">
      <div>
        <b>${escapeHtml(p.name)}</b>
        <small>${i.qty} x ${rupiah(p.price)}</small>
      </div>
      <strong>${rupiah(Number(p.price) * i.qty)}</strong>
    </div>`;
    })
    .join('');

  const subtotal = cartTotal();
  const ongkir = ongkirAmount(subtotal);
  const totalEl = $('#checkoutTotal');
  if (totalEl) totalEl.textContent = rupiah(subtotal + ongkir);
  const subEl = $('#checkoutSubtotal');
  if (subEl) subEl.textContent = rupiah(subtotal);
  const ongkirEl = $('#checkoutOngkir');
  if (ongkirEl) ongkirEl.textContent = ongkir > 0 ? rupiah(ongkir) : 'Gratis';
  const note = $('#checkoutOngkirNote');
  if (note) {
    note.textContent =
      ongkir > 0
        ? 'Tambah ' +
          rupiah(Math.max(0, (window.__BERKAH_ONGKIR__?.threshold || 100000) - subtotal)) +
          ' lagi untuk gratis ongkir.'
        : 'Anda mendapat gratis ongkir.';
  }
}

function updateTransferInfo() {
  const box = $('#transferInfo');
  const selected = ($('input[name="pembayaran"]:checked')?.value || 'cod').trim();
  if (box) box.hidden = selected !== 'transfer';
}

function renderStruk(kode, metode, subtotal, ongkir, total) {
  const box = $('#strukItems');
  if (!box) return;

  box.innerHTML = cart
    .map((i) => {
      const p = PRODUCTS.find((x) => Number(x.id) === Number(i.id));
      if (!p) return '';
      return `<div class="struk-row"><span>${escapeHtml(p.name)} × ${i.qty}</span><b>${rupiah(Number(p.price) * i.qty)}</b></div>`;
    })
    .join('');

  $('#strukKode').textContent = kode;
  $('#strukTanggal').textContent = new Date().toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
  $('#strukBayar').textContent = metode.includes('COD') ? 'COD' : 'Transfer Bank';
  $('#strukSubtotal').textContent = rupiah(subtotal);
  $('#strukOngkir').textContent = ongkir > 0 ? rupiah(ongkir) : 'Gratis';
  $('#strukTotal').textContent = rupiah(total);
}

async function submitCheckout(event) {
  event.preventDefault();
  if (!cart.length) {
    showToast('Keranjang masih kosong');
    return;
  }

  const nama = ($('#coName')?.value || '').trim();
  const wa = ($('#coPhone')?.value || '').trim();
  const email = ($('#coEmail')?.value || '').trim();
  const alamat = ($('#coAddress')?.value || '').trim();
  const catatan = ($('#coNote')?.value || '').trim();
  const pembayaran = ($('input[name="pembayaran"]:checked')?.value || 'cod').trim();
  const agree = $('#coAgree')?.checked;

  if (nama.length < 3 || nama.length > 120) {
    showToast('Nama 3–120 karakter');
    return;
  }
  if (!/^[0-9+\-\s()]{8,20}$/.test(wa)) {
    showToast('Nomor WhatsApp tidak valid');
    return;
  }
  if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
    showToast('Format email tidak valid');
    return;
  }
  if (alamat.length > 300 || catatan.length > 500) {
    showToast('Alamat atau catatan terlalu panjang');
    return;
  }
  if (!agree) {
    showToast('Setujui kebijakan privasi terlebih dahulu');
    return;
  }
  if (!CSRF_TOKEN) {
    showToast('Sesi tidak valid. Muat ulang halaman.');
    return;
  }

  const btn = $('#coSubmit');
  const label = btn ? btn.textContent : '';
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memproses...';
  }

  const payload = {
    nama,
    email,
    no_wa: wa,
    alamat,
    catatan,
    pembayaran,
    items: cart.map((i) => ({ id: Number(i.id), qty: i.qty })),
  };

  let data;
  try {
    const res = await fetch(ORDER_API, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': CSRF_TOKEN,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    });
    const raw = await res.text();
    try {
      data = JSON.parse(raw);
    } catch (e) {
      data = null;
    }
    if (!res.ok || !data || !data.success) {
      const fallback = raw && raw.length < 160 ? raw : 'Gagal menyimpan pesanan. Coba lagi.';
      throw new Error((data && data.message) || fallback);
    }
  } catch (err) {
    showToast(err.message || 'Pesanan gagal disimpan. Coba lagi.');
    if (btn) {
      btn.disabled = false;
      btn.textContent = label;
    }
    return;
  }

  const kode = data.kode || '';
  const rekening = ($('#transferInfo .transfer-number')?.textContent || '').trim();
  const metode =
    pembayaran === 'transfer'
      ? `Transfer ke BCA ${rekening} a.n. Berkah Rasa. Setelah transfer, konfirmasi ke penjual via halaman Kontak.`
      : 'Silakan bayar saat pesanan diterima (COD).';

  renderStruk(
    kode,
    metode,
    Number(data.subtotal ?? cartTotal()),
    Number(data.ongkir ?? ongkirAmount(cartTotal())),
    Number(data.total ?? grandTotal()),
  );

  cart = [];
  saveCart();
  renderCheckoutSummary();

  const formCard = $('#checkoutFormCard');
  const summaryCard = $('#checkoutSummaryCard');
  const success = $('#checkoutSuccess');
  if (formCard) formCard.hidden = true;
  if (summaryCard) summaryCard.hidden = true;
  if (success) {
    success.hidden = false;
    $('#checkoutKode').textContent = kode;
    $('#checkoutPaymentInfo').textContent = metode;
    success.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else {
    showToast('Pesanan berhasil dibuat dengan kode ' + kode);
  }
  if (btn) {
    btn.disabled = false;
    btn.textContent = label;
  }
}

/* ---------- dark mode (cookie, bukan localStorage) ---------- */
const THEME_COOKIE = 'berkah_theme';
const CONSENT_COOKIE = 'berkah_consent';

function toggleDark() {
  document.body.classList.toggle('dark');
  const dark = document.body.classList.contains('dark');
  setCookie(THEME_COOKIE, dark ? 'dark' : 'light', 365);
  const btn = $('#darkToggle');
  if (btn) btn.innerHTML = dark ? sunIcon() : moonIcon();
}
const moonIcon = () =>
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
const sunIcon = () =>
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>';

/* ---------- cookie consent ---------- */
function acceptCookies() {
  setCookie(CONSENT_COOKIE, '1', 365);
  const banner = $('#cookieBanner');
  if (banner) {
    banner.classList.add('hide');
    setTimeout(() => banner.remove(), 350);
  }
}

/* ---------- navigation ---------- */
function setNavOpen(open) {
  $('#mainNav')?.classList.toggle('open', open);
  document.body.classList.toggle('nav-open', open);
  const btn = $('.nav-toggle');
  btn?.classList.toggle('open', open);
  btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
  btn?.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
}

function toggleNav() {
  setNavOpen(!$('#mainNav')?.classList.contains('open'));
}

function closeNav() {
  setNavOpen(false);
}

/* ---------- dropdown profil (model YouTube) ---------- */
function toggleAccountMenu() {
  const menu = $('#accountDropdown');
  const btn = $('.account-chip');
  const open = menu?.classList.toggle('open') ?? false;
  btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
  if (open) closeAdminProfile();
}

function toggleAdminProfile() {
  const menu = $('#adminProfileDropdown');
  const btn = $('.admin-who');
  const open = menu?.classList.toggle('open') ?? false;
  btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
  if (open) closeAccountMenu();
}

function closeAccountMenu() {
  $('#accountDropdown')?.classList.remove('open');
  $('.account-chip')?.setAttribute('aria-expanded', 'false');
}

function closeAdminProfile() {
  $('#adminProfileDropdown')?.classList.remove('open');
  $('.admin-who')?.setAttribute('aria-expanded', 'false');
}

/* ---------- navigasi panel admin (layar potrat/kecil) ---------- */
function toggleAdminNav() {
  const nav = $('#adminNav');
  const btn = $('.admin-nav-toggle');
  const open = nav?.classList.toggle('open') ?? false;
  btn?.classList.toggle('open', open);
  btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
  document.body.classList.toggle('nav-open', open);
}

function closeAdminNav() {
  $('#adminNav')?.classList.remove('open');
  $('.admin-nav-toggle')?.classList.remove('open');
  $('.admin-nav-toggle')?.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('nav-open');
}

/* ---------- accordion ---------- */
function toggleAccordion(header) {
  const acc = header.parentElement;
  const body = acc.querySelector('.accordion-body');
  const isOpen = acc.classList.contains('open');
  acc.parentElement.querySelectorAll('.accordion.open').forEach((a) => {
    a.classList.remove('open');
    a.querySelector('.accordion-body').style.maxHeight = null;
  });
  if (!isOpen) {
    acc.classList.add('open');
    body.style.maxHeight = body.scrollHeight + 'px';
  }
}

/* ---------- contact form ---------- */
function validateContact(e) {
  const name = ($('#cname')?.value || '').trim();
  const email = ($('#cemail')?.value || '').trim();
  const msg = ($('#cmsg')?.value || '').trim();
  if (name.length < 3) {
    showToast('Nama minimal 3 karakter');
    e.preventDefault();
    return false;
  }
  if (name.length > 120) {
    showToast('Nama maksimal 120 karakter');
    e.preventDefault();
    return false;
  }
  if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
    showToast('Format email tidak valid');
    e.preventDefault();
    return false;
  }
  if (email.length > 150) {
    showToast('Email terlalu panjang');
    e.preventDefault();
    return false;
  }
  if (msg.length < 10) {
    showToast('Pesan minimal 10 karakter');
    e.preventDefault();
    return false;
  }
  if (msg.length > 2000) {
    showToast('Pesan maksimal 2000 karakter');
    e.preventDefault();
    return false;
  }
  return true;
}

/* ---------- scroll effects ---------- */
function initScroll() {
  const header = $('.site-header');
  const toTop = $('#toTop');
  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    header?.classList.toggle('scrolled', y > 10);
    toTop?.classList.toggle('show', y > 600);
  });
}

let revealObserver;
function observeReveals() {
  if (!('IntersectionObserver' in window)) {
    $$('.reveal').forEach((el) => el.classList.add('in'));
    return;
  }
  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) {
            en.target.classList.add('in');
            revealObserver.unobserve(en.target);
          }
        });
      },
      { threshold: 0.12 },
    );
  }
  $$('.reveal:not(.in)').forEach((el) => revealObserver.observe(el));
}

/* ---------- promo bar ---------- */
function initPromoBar() {
  const bar = document.querySelector('#topbarPromo');
  if (!bar) return;
  //Sinkronisasi status lama dari sessionStorage ke cookie (sekali saja).
  let closed = false;
  try {
    closed = sessionStorage.getItem('berkahrasa_promo_closed') === '1';
  } catch (e) {}
  if (closed) {
    setCookie('berkah_promo_closed', '1', 30);
    bar.style.display = 'none';
  }
}

/* ---------- init ---------- */
document.addEventListener('DOMContentLoaded', () => {
  initPromoBar();

  // Tema dibaca server dari cookie; samakan ikon tombol saja.
  const darkToggle = $('#darkToggle');
  if (darkToggle && document.body.classList.contains('dark')) darkToggle.innerHTML = sunIcon();

  initScroll();
  renderProducts();
  updateCartUI();
  renderCartItems();
  renderCheckoutSummary();
  observeReveals();

  const search = $('#searchInput');
  if (search) search.addEventListener('input', (e) => renderProducts(e.target.value));

  const page = location.pathname.split('/').pop() || 'index.php';
  $$('.nav-link').forEach((a) => {
    const hrefFile = (a.getAttribute('href') || '').split('/').pop();
    a.classList.toggle('active', hrefFile === page || (page === '' && hrefFile === 'index.php'));
    a.addEventListener('click', closeNav);
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.site-header')) closeNav();
    if (!e.target.closest('.account-menu-wrap')) closeAccountMenu();
    if (!e.target.closest('.admin-profile-wrap')) closeAdminProfile();
  });

  $$('input[name="pembayaran"]').forEach((radio) =>
    radio.addEventListener('change', updateTransferInfo),
  );
  updateTransferInfo();

  $$('.admin-nav a').forEach((a) => a.addEventListener('click', closeAdminNav));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeNav();
      closeCart();
      closeProduct();
      closeAdminNav();
      closeAccountMenu();
      closeAdminProfile();
    }
  });

  const yearEl = $('#year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
});
