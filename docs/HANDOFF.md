# HANDOFF — WCM 2 - Version 1

Dokumen ini merekam apa yang sudah dieksekusi saat proses clone/handoff
`cms-admin/` dari **wcm1_version1** (Tentakel 1, dulu olahraga77.com) ke
proyek ini, mengikuti checklist standar di `wcm1_version1/docs/HANDOFF-CMS-ADMIN.md`.

## Konteks proyek

- **Nama kerja:** WCM 2 - Version 1
- **Nama brand:** **Biang Olahraga** (ditentukan operator 13 Agu 2026).
- **Peran dalam struktur PBN:** "Tentakel 2" — backlink menunjuk ke
  **wcm1_version1 (Tentakel 1)**, bukan langsung ke money site. Sesuai
  diagram operator: Kepala ← Tentakel 1 ← Tentakel 2.
- **Domain:** belum ditentukan (per 13 Agu 2026). `CMS_ADMIN_TAGLINE` dan
  branding lain sementara diisi placeholder `WCM2.VERSION1` — **wajib
  diganti** begitu domain final (dan bisa juga diganti duluan ke "Biang
  Olahraga" begitu operator konfirmasi).
- **Topik/konten:** multi-cabang olahraga, 4 kategori final: **Bulu
  Tangkis, Tinju, Moto GP, Tips**.
- **Tema visual:** dark/gelap dominan (referensi visual: juara.net —
  header solid gelap, badge kategori warna-warni, kartu artikel). Mockup
  homepage V2 sudah disetujui operator: header dengan menu di kiri +
  search di kanan (divider diagonal), rail kategori vertikal sticky di
  sisi kiri, hero bento asimetris, artikel per kategori sebagai filmstrip
  horizontal-scroll. Detail struktur permalink URL masih belum
  dikonfirmasi operator.

## Checklist HANDOFF-CMS-ADMIN — status eksekusi

### 1. Isolasi Database
- [x] `cms-admin/config/database.php` diarahkan ke `DB_NAME` baru:
      `wpm_cms_wcm2_version1` — genuinely beda nama dari
      `wpm_cms_olahraga77` (punya wcm1_version1).
- [x] Database `wpm_cms_wcm2_version1` sudah ada di MySQL dev lokal.
- [x] **BUG DITEMUKAN & DIPERBAIKI (13 Agu 2026): isolasi data GAGAL di
      percobaan pertama.** Database `wpm_cms_wcm2_version1` ternyata
      dibuat lewat fitur **"Copy database" phpMyAdmin dari
      wcm1_version1** — bukan database kosong seperti yang dicatat
      sebelumnya di dokumen ini. Baru ketahuan saat login pertama kali ke
      admin panel WCM 2: dashboard nampilin akun admin **"olahraga77"**,
      4 artikel published (termasuk "Tak Peduli Dianggap Tim..."),
      74 views — semua konten asli olahraga77.com, bukan data WCM 2.
      Operator konfirmasi ini genuinely ke-copy penuh (struktur + data),
      persis risiko duplicate-content/PBN footprint yang diwanti-wanti
      `/CLAUDE.md`. **Remediasi:** dibuat script sekali-pakai
      `cms-admin/_cleanup-fresh-start.php` — TRUNCATE semua tabel (skema
      tetap, isi dikosongkan) + insert 1 admin baru
      (`admin@olahraga77.com`, role superadmin — email sengaja masih
      pakai domain olahraga77 karena domain biangolahraga belum dibeli,
      cuma login lokal). Script itu WAJIB dihapus dari server setelah
      dijalankan sekali (gak ada konfirmasi apapun kalau diakses lewat
      URL, bisa wipe DB kapan saja kalau kebiarin nyangkut).
- [ ] Belum ada tabel/data lain di DB pasca-cleanup — perlu jalanin
      schema migration ulang (otomatis begitu halaman admin diakses,
      sama seperti pola wcm1_version1) untuk kategori & tabel lain yang
      dibutuhkan situs Biang Olahraga.
- [ ] Begitu hosting production untuk WCM 2 disiapkan, `database.php`
      wajib diganti lagi ke kredensial production yang juga terpisah.

### 2. Isolasi Kredensial & Secret
- [x] `cms-admin/config/database.php` dan `cms-admin/config/app.php`
      sudah tercakup pola `.gitignore` yang sama (perlu dibuat ulang di
      proyek ini — lihat poin Git di bawah, belum ada `.gitignore` baru).
- [x] `CMS_AI_ENC_SECRET` di `app.php` **di-generate ulang** (bukan
      reuse punya wcm1_version1) — beda instance, beda secret.
- [x] `GROWTH_AGENT_DIGEST_TOKEN` masih placeholder
      `GANTI_DENGAN_TOKEN_ACAK_ASLI` (ikut ter-clone dari template) —
      **wajib diisi token asli baru** sebelum modul digest dipakai.
- [x] `config/app.php.example` dan `config/database.php.example` sudah
      disesuaikan (tagline placeholder, catatan isolasi DB diupdate
      merujuk wcm1_version1).
- [x] Script sekali-pakai sudah dibuat (`_cleanup-fresh-start.php`, lihat
      poin 1 di atas) — untuk reset data, bukan reset-password. Sudah
      diberi safety guard (menolak jalan kalau `DB_NAME` bukan
      `wpm_cms_wcm2_version1`). **Ingat hapus file ini setelah
      dijalankan**, sama seperti catatan soal script reset-password di
      wcm1_version1.

### 3. Audit Modul yang Gak Relevan
- [x] Modul livescore/football/basketball/F1 **sudah bersih** di
      cms-admin sumber (wcm1_version1) sebelum di-clone — otomatis ikut
      bersih di sini juga, gak perlu diulang.
- [x] Konsep `sport_key` juga sudah dihapus di sumber — ikut bersih.
- [ ] **Belum di-audit ulang khusus buat konteks Biang Olahraga** — sekarang
      topik final sudah jelas (multi-cabang olahraga: Bulu Tangkis,
      Tinju, Moto GP, Tips), jadi audit ini perlu dijalankan begitu
      migration tabel selesai, untuk pastikan gak ada modul/kolom yang
      masih terlalu spesifik ke satu cabang olahraga tertentu.

### 4. Branding & Konten Netral
- [x] **Selesai (16 Agu 2026)** — `CMS_ADMIN_NAME`/`CMS_ADMIN_TAGLINE`
      (app.php) diganti dari placeholder `WPM`/`WCM2.VERSION1` ke pola
      final: `CMS_ADMIN_NAME = 'WCM'` (nama tool generik, konsisten
      dengan pola wcm1_version2), `CMS_ADMIN_TAGLINE = 'Biang Olahraga'`
      (brand situs ini). Tagline hardcoded di `login.php` (yang sebelumnya
      "Admin Panel - WCM 2 - Version 1", gak pakai konstanta sama sekali)
      diganti jadi baca `CMS_ADMIN_TAGLINE` langsung.
- [x] **Logo admin panel diganti (16 Agu 2026)** — sebelumnya masih pakai
      monogram WPM warisan clone (`img/logo.png` + `img/logo-white.png`,
      952x1294 PNG). Diganti wordmark teks sederhana "BO" (SVG,
      `img/logo.svg` + `img/logo-white.svg`, ink/putih + garis aksen
      merah `#ff3b30`) — dipasang di sidebar (`includes/sidebar.php`),
      login (`login.php`), dan favicon (`cms_favicon_url()` di
      `functions.php`, sebelumnya reuse `logo.png`). PNG lama sudah
      dihapus. **Logo ini sementara/placeholder text-based** — logo
      profesional final bisa menyusul belakangan, prioritas sekarang cuma
      supaya gak nunjuk ke identitas WPM lagi.
- [x] System prompt AI di `growth-agent-service.php` sudah netral sejak
      di sumber (gak nyebut nama situs manapun) — dicek ulang, aman,
      gak perlu diubah lagi. Perlu di-tweak lagi nanti supaya paham
      konteks multi-cabang olahraga (bukan cuma sepak bola) begitu
      Growth Agent mulai dipakai aktif.
- [ ] **Konten (artikel, kategori) belum ada sama sekali** — situs ini
      sekarang kosong total secara data (pasca-cleanup, lihat poin 1),
      tinggal cms-admin doang. 4 kategori final sudah diputuskan (Bulu
      Tangkis/Tinju/Moto GP/Tips) tapi belum di-input ke database.
      Artikel wajib ditulis dari nol, **TIDAK boleh copy-paste** dari
      wcm1_version1 atau situs manapun — ini prinsip yang sama persis
      yang dilanggar (tanpa sengaja) waktu database di-copy penuh dari
      wcm1_version1 di awal setup, lihat catatan bug di poin 1.

### 5. Frontend Publik — Dibangun Terpisah
- [x] **Frontend publik sudah diimplementasikan** (13 Agu 2026), terpisah
      total dari `cms-admin/` (pola sama seperti wcm1_version1): `index.php`,
      `kategori.php`, `artikel.php`, `cari.php`, `ad-click.php`,
      `includes/site-bootstrap.php` (data layer + helper, define nama
      brand & 4 kategori final), `includes/site-header.php` /
      `includes/site-footer.php` (mockup V2: header menu-kiri/search-kanan,
      rail kategori vertikal sticky, hero bento asimetris, filmstrip
      artikel), `includes/TimeHelpers.php`, `assets/css/site.css`,
      `.htaccess`. Semua nyambung ke data CMS real via PDO, bukan hardcode.
- [ ] **Belum ada artikel di database** — jadi belum bisa diverifikasi
      visual dengan konten sungguhan, baru sebatas empty-state/struktur
      halaman. Perlu dites ulang di browser begitu ada artikel live.
- [ ] Struktur permalink URL masih sementara: `.htaccess` pakai pola sama
      dengan wcm1_version1 (`/artikel/{slug}`, `/kategori/{slug}`) dengan
      catatan eksplisit di file itu bahwa ini **belum final** — ganti
      begitu operator kasih struktur yang "agak beda".

### 6. Deploy Workflow
- [ ] Belum ada Git repo untuk proyek ini.
- [ ] Belum ada `.gitignore`, `.cpanel.yml`, hosting cPanel, atau domain
      — semua menunggu domain final ditentukan.

### 7. SEO Dasar
- [ ] Belum dikerjakan — robots.txt, sitemap, favicon nunggu frontend
      publik dibangun dulu.

### 8. Verifikasi Akhir
- [ ] Belum relevan sepenuhnya — frontend sudah diimplementasikan tapi
      belum bisa diverifikasi visual dengan artikel sungguhan (database
      masih kosong). Verifikasi browser (homepage, kategori, artikel,
      rewrite `.htaccess`) menyusul begitu ada konten live — pola
      verifikasi yang sama seperti dilakukan di wcm1_version1 bisa
      diulang di sini.

## Yang sudah dieksekusi hari ini (13 Agu 2026)

1. Operator bikin folder `wcm2_version1` (sibling dari `wcm1_version1`,
   sebelumnya bernama `sagagoal77.com` lalu di-rename operator jadi
   `wcm1_version1` biar seragam), connect ke Cowork.
2. `cms-admin/` di-clone utuh dari `wcm1_version1/cms-admin/` (bukan
   dari sagagoal.com langsung — sesuai keputusan operator, ikut standar
   HANDOFF-CMS-ADMIN untuk clone dari Tentakel terdekat, bukan dari
   Kepala).
3. `cms-admin/config/database.php` — `DB_NAME` diganti ke
   `wpm_cms_wcm2_version1` (masih pakai host/user/pass dev lokal
   `mysql`/`root`/`root`, sama pola kayak wcm1_version1 waktu awal
   setup).
4. `cms-admin/config/app.php` — `CMS_AI_ENC_SECRET` di-generate ulang
   (bukan reuse), `CMS_ADMIN_TAGLINE` diganti placeholder.
5. `cms-admin/login.php` — tagline hardcoded diganti.
6. `cms-admin/config/*.example` — disesuaikan (tagline placeholder,
   catatan isolasi DB update merujuk wcm1_version1, bukan sagagoal.com).
7. Dokumen ini (`HANDOFF.md`) dan `ROADMAP.md` dibuat.
8. **Operator membuat database MySQL kosong `wpm_cms_wcm2_version1`** di
   dev lokal via phpMyAdmin.
9. **Niche & brand final ditentukan operator: Biang Olahraga**, multi-cabang
   olahraga dengan 4 kategori: Bulu Tangkis, Tinju, Moto GP, Tips.
10. **Mockup homepage V2 dibuat & disetujui operator** — referensi visual
    juara.net, tema gelap dengan aksen warna per kategori, header
    menu-kiri/search-kanan, rail kategori vertikal, hero bento asimetris,
    filmstrip artikel horizontal.
11. **Mockup diimplementasikan ke kode PHP asli** — frontend publik
    lengkap dibuat: `index.php`, `kategori.php`, `artikel.php`,
    `cari.php`, `ad-click.php`, `includes/site-bootstrap.php` (data layer,
    4 kategori final: bulu-tangkis/tinju/moto-gp/tips, mapping warna per
    kategori), `includes/site-header.php` + `site-footer.php` (layout
    mockup V2), `includes/TimeHelpers.php`, `assets/css/site.css`,
    `.htaccess` (permalink sementara, sama pola dengan wcm1_version1).
12. Nama brand direvisi dari "Biang Bola" ke **Biang Olahraga** (risiko
    copyright) — semua referensi di dokumen & kode disesuaikan.
13. **BUG isolasi database ditemukan & diperbaiki**: login pertama ke
    admin panel WCM 2 nampilin data olahraga77.com asli (admin
    "olahraga77", 4 artikel, 74 views) — konfirmasi operator database
    ternyata di-copy penuh dari wcm1_version1 lewat phpMyAdmin, bukan
    dibuat kosong. Dibuat & dijalankan script sekali-pakai
    `cms-admin/_cleanup-fresh-start.php`: TRUNCATE semua tabel + insert
    admin baru (`admin@olahraga77.com`, superadmin). Database sekarang
    genuinely kosong.

## Yang sudah dieksekusi (16 Agu 2026)

1. **Branding admin panel diganti dari warisan WPM** — `CMS_ADMIN_NAME`
   (`WPM` → `WCM`) dan `CMS_ADMIN_TAGLINE` (`WCM2.VERSION1` → `Biang
   Olahraga`) di `cms-admin/config/app.php`; tagline hardcoded di
   `login.php` (yang sebelumnya nulis "Admin Panel - WCM 2 - Version 1"
   dan gak baca konstanta sama sekali) diganti baca `CMS_ADMIN_TAGLINE`.
2. **Logo WPM diganti wordmark "BO" sementara** — `img/logo.svg` +
   `img/logo-white.svg` (ink/putih, garis aksen merah) menggantikan
   `logo.png`/`logo-white.png` (952x1294 PNG monogram WPM lama, sudah
   dihapus) di sidebar, login, dan favicon (`cms_favicon_url()`).
3. Sempat ada insiden **file `login.php` dan `assets/img/*` ke-overwrite
   manual sama file punya wcm1_version2** (operator copy-paste file
   salah project — bukan bug sync) — ketauan karena muncul teks "BRAVO
   SPORT 77" dan file `wm-logo-white.png` yang gak pernah dibuat di
   project ini. Sudah dibetulin balik ke isi project ini sendiri.
4. Ditest di browser: halaman login sampai dashboard, gak ada lagi
   tampilan yang nunjuk ke "WPM" atau brand project lain.

## Yang perlu dikonfirmasi/dikerjakan operator sebelum lanjut

1. **Detail struktur permalink** yang "agak beda" dari `/artikel/{slug}`
   — perlu tau maunya seperti apa (misal `/berita/{slug}`,
   `/{kategori}/{slug}`, dsb) sebelum frontend mulai diimplementasi ke
   kode.
2. ~~**Konfirmasi nama brand final** "Biang Olahraga"~~ — **selesai,
   nama brand sudah diganti di seluruh admin panel (config, sidebar,
   login, favicon) per 16 Agu 2026.**
3. Domain final (kalau sudah ada) — untuk update tagline & mulai setup
   Git/hosting/GSC.
4. Setelah 3 poin di atas jelas: jalanin schema migration di database
   yang sudah dibuat, input 4 kategori final ke CMS, audit ulang modul
   cms-admin sesuai konteks multi-cabang olahraga, lalu mulai bangun
   frontend publik dari mockup yang sudah disetujui.
