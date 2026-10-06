# Prompt buat Claude Code — Fitur Upload Drag & Drop di Media Library Picker

Copy-paste seluruh isi file ini sebagai prompt ke Claude Code di folder proyek `wcm2_version2`.

---

Kamu kerja di admin panel **cms-admin** proyek `wcm2_version2` (WCM/ArenaSport77). Baca dulu `CLAUDE.md`, `docs/HANDOFF.md`, `docs/ROADMAP.md` buat konteks umum sebelum mulai.

## Masalah

Modal **"Select from Media Library"** (dipakai di 3 tempat: picker featured image & OG image di `cms-admin/pages/pages.php`, picker gambar iklan di `cms-admin/pages/ads.php`, dan file picker TinyMCE lewat `cms-admin/includes/tinymce-media-picker.php`) **cuma bisa milih gambar yang udah ada** di tabel `media_library`. Gak ada cara upload file baru dari dalam modal itu — operator harus keluar dulu, buka halaman `cms-admin/pages/media-library.php`, isi form upload di situ, baru balik lagi ke modal buat milih. Ribet, dan kalau tabel `media_library` masih kosong (kasus nyata sekarang — 3 artikel contoh ArenaSport77 punya featured image yang gak pernah didaftarin ke `media_library`), modal cuma nampilin "No images found in the Media Library." tanpa jalan keluar.

## Target

Tambahin **drop-zone upload langsung di dalam modal "Select from Media Library"** — mirip pola standar "Click to upload or drag & drop an image here (max 5 MB)": area putus-putus di bagian atas grid, bisa diklik buat buka file picker OS, atau di-drag-drop file gambar langsung ke situ. Begitu upload sukses, file itu otomatis masuk ke grid (tanpa reload halaman) dan operator bisa langsung klik buat milihnya — gak perlu pindah halaman sama sekali.

## Yang sudah ada (reuse, jangan duplikat logic)

`cms-admin/pages/media-library.php` baris ~108–205 udah punya validasi upload lengkap yang battle-tested — pola ini **harus di-reuse persis**, bukan ditulis ulang:

1. Extension check cepat: `['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf']`.
2. Deteksi MIME asli lewat `finfo` (bukan percaya nama file dari client) — map ke ekstensi final via `$mimeExtMap`.
3. Limit ukuran per tipe: gambar 5 MB, PDF 10 MB.
4. Simpan ke `uploads/media/{tahun}/{bulan}/`, bikin `index.php` guard 403 di tiap level folder kalau belum ada.
5. Nama file aman: lowercase + slug dari nama asli + 16 hex random suffix, cek collision sebelum `move_uploaded_file()`.
6. Path yang disimpan ke DB selalu pakai leading slash: `/uploads/media/2026/08/namafile-abcdef1234567890.jpg`.
7. Setelah file tersimpan, insert row baru ke tabel `media_library` (`file_name`, `file_path`, `file_type`, `mime_type`, `file_size_kb`, `is_active=1`).

Modal picker-nya sendiri ada di `cms-admin/includes/tinymce-media-picker.php` — HTML modal `#mce-ml-modal`, grid `.mce-ml-grid`, JS yang handle search/select/`window.wpmMlPicker`. CSRF: proyek ini pakai `cms_csrf_field()` buat render token dan `cms_verify_csrf()` (dipanggil otomatis di `cms-admin/includes/auth.php` buat tiap POST) — endpoint baru WAJIB ikut pola ini, jangan skip CSRF.

## Yang perlu dikerjain

### 1. Endpoint upload AJAX baru

Bikin `cms-admin/actions/media-upload.php`:
- `require` auth.php (dapet proteksi login + CSRF check otomatis, sama kayak action lain di folder itu — lihat `cms-admin/actions/banners-store.php` buat pola yang mirip).
- Terima `multipart/form-data` dengan field `media_file` (satu file per request — kalau mau dukung multi-file drop sekaligus, JS kirim beberapa request paralel/berurutan, bukan satu request banyak file, biar error handling per-file jelas).
- **Pindahin (bukan copy-paste ulang) logic validasi dari `media-library.php` baris ~108–205 ke function shared**, misal `cms_handle_media_upload(string $tmpName, string $origName, int $fileBytes): array` di `cms-admin/includes/functions.php` (atau file includes yang sesuai) — return array berisi `file_path`, `file_name`, `mime_type`, `file_size_kb`, `file_type`, atau throw exception dengan pesan error yang jelas kalau gagal validasi. Dengan ini, `media-library.php` (form manual yang sudah ada) DAN endpoint baru ini sama-sama manggil function yang sama — gak ada logic ke-duplikat/ke-drift.
- Setelah file valid & tersimpan ke disk, insert ke `media_library` (reuse struktur insert yang ada di `media-library.php` action `create`).
- Response **JSON**, bukan redirect:
  ```json
  { "ok": true, "id": 123, "file_name": "...", "file_path": "/uploads/media/2026/08/...", "url": "...", "mime_type": "image/webp", "width": 1200, "height": 630 }
  ```
  Field `url` pakai `app_asset_preview_url()` (function yang sama dipakai di `tinymce-media-picker.php` buat resolve `data-src`), karena BASE_URL bisa beda kalau cms-admin di-deploy di subdomain terpisah dari frontend (lihat komentar `app_asset_preview_url()` di `cms-admin/config/app.php`). Width/height ambil pakai `getimagesize()` kayak yang udah dilakuin di `tinymce-media-picker.php`.
  Kalau gagal: `{ "ok": false, "error": "pesan error yang jelas" }` dengan HTTP status code sesuai (400 buat validation error, 403 buat auth/csrf gagal, dll).
- CSRF token dikirim dari JS lewat header custom (misal `X-CSRF-Token`) atau field form biasa — samain cara CSRF token proyek ini biasa diverifikasi (cek isi `cms_verify_csrf()` di `cms-admin/includes/functions.php` atau `auth.php`, ikutin cara baca token yang sama).

### 2. Drop-zone UI di dalam modal

Edit `cms-admin/includes/tinymce-media-picker.php`:
- Tambahin elemen drop-zone di `#mce-ml-body`, SEBELUM `.mce-ml-grid` (selalu tampil, bukan cuma pas grid kosong) — style konsisten sama design system yang ada di file ini (pakai CSS var `--line`, `--surface-soft`, `--muted`, dst, bukan warna hardcode baru):
  ```html
  <div id="mce-ml-dropzone" tabindex="0" role="button" aria-label="Upload gambar baru">
      <input type="file" id="mce-ml-file-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
      <span id="mce-ml-dropzone-text">Click to upload or drag &amp; drop an image here (max 5 MB)</span>
      <span id="mce-ml-dropzone-progress" hidden></span>
  </div>
  ```
- State visual: default (border putus-putus `--line`), `dragover` (border jadi warna aksen + background sedikit beda — ikutin token warna aksen yang sudah dipakai elemen lain di file ini, misal `--navlink-active-border`), `uploading` (progress text / spinner sederhana, disable interaksi sementara), `error` (pesan merah singkat, auto-hilang beberapa detik atau pas user coba lagi).
- JS (tambahin ke `<script>` yang udah ada di file ini, jangan bikin file JS terpisah — modal ini didesain self-contained):
  - Klik dropzone → trigger `#mce-ml-file-input.click()`.
  - `dragover`/`dragleave`/`drop` listener di dropzone, `preventDefault()` biar browser gak buka file-nya langsung.
  - Validasi ringan di sisi client dulu (ekstensi + ukuran) sebelum kirim — tapi validasi SERVER (step 1) tetap sumber kebenaran, client-side cuma buat UX cepat kasih tau user salah sebelum nunggu network round-trip.
  - Upload tiap file lewat `fetch('actions/media-upload.php', { method: 'POST', body: formData })` dengan header CSRF yang sesuai.
  - Sukses → bikin elemen `.mce-ml-item` baru persis struktur yang di-render PHP (`data-src`, `data-path`, `data-alt`, `data-name`, `data-width`, `data-height`), **prepend** ke `.mce-ml-grid` (paling atas, biar upload terbaru gampang keliatan), dan **daftarin event listener click/keydown yang sama** kayak item lain (reuse function `selectItem` yang sudah ada, jangan tulis ulang).
  - Kalau grid sebelumnya nampilin `.mce-ml-empty` ("No images found..."), hapus elemen itu begitu upload pertama sukses.
  - Multi-file drop: proses satu-satu (sequential atau `Promise.all`, terserah, yang penting progress per-file keliatan kalau ada yang gagal di tengah tetep lanjut yang lain, gak stop semua).
  - Error per file → tampilin pesan singkat di dropzone (gak perlu modal alert, cukup text merah sebentar), lanjut proses file berikutnya kalau multi-file.

### 3. Konsistensi di 2 titik lain yang reuse modal ini

Karena `pages.php` dan `ads.php` reuse modal yang sama lewat `tinymce-media-picker.php`, perubahan di file itu otomatis kepake di ketiga tempat. **Tes manual di ketiganya** setelah selesai:
- `cms-admin/pages/pages.php` → New/Edit Article → tombol "Choose from Media Library" di field Featured Image DAN OG Image.
- `cms-admin/pages/ads.php` → picker gambar iklan.
- TinyMCE content editor (insert image via toolbar) di halaman mana pun yang pakai `tinymce.init()` dengan `file_picker_callback: window.wpmMlPicker`.

### 4. Testing

- `php -l` tiap file yang diedit.
- Test manual: upload JPG, PNG, WebP valid — harus langsung muncul di grid dan bisa dipilih.
- Test reject: file >5MB, ekstensi gak didukung (misal .txt di-rename jadi .jpg — harus ketolak di step MIME detection server-side, bukan cuma client-side check yang bisa di-bypass).
- Test drag & drop beneran (bukan cuma klik) di browser.
- Test multi-file drop sekaligus (2-3 gambar bareng).
- Pastiin item yang baru di-upload BISA langsung diklik buat milih gambar itu ke field yang lagi dibuka (featured image / OG image / TinyMCE) tanpa perlu refresh apapun.
- Pastiin modal lama (yang udah ada isinya) tetep kebaca normal — jangan sampai perubahan ini malah bikin item lama ilang dari grid.

### 5. Dokumentasi

Setelah selesai dan dites, update `docs/ROADMAP.md` / `docs/HANDOFF.md` — catat fitur baru ini singkat (drop-zone upload langsung di picker, endpoint `cms-admin/actions/media-upload.php`) biar ke-track di progress.

---

Kalau ada keputusan desain yang ambigu (misal mau dukung berapa file sekaligus, atau mau ada preview-before-upload atau langsung upload), pakai judgment yang paling konsisten sama pola UI yang udah ada di file ini — gak perlu nunggu konfirmasi operator buat hal-hal kecil kayak gitu, tapi kalau ada perubahan skema database atau ada risiko keamanan (misal validasi upload yang dilonggarin), stop dan tanya dulu ke operator.
