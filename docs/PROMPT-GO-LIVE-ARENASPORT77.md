# Prompt buat Claude Code — Go-Live ArenaSport77 (arenasport77.com)

Copy-paste seluruh isi file ini sebagai prompt awal ke Claude Code di folder proyek `wcm2_version2`.

---

Kamu bertugas bawa situs **ArenaSport77** (working folder: `wcm2_version2`, brand publik: ArenaSport77, domain final: **arenasport77.com**) dari kondisi dev lokal ke **live production**, lewat hosting cPanel yang sudah aktif untuk domain ini. Baca `CLAUDE.md`, `docs/HANDOFF.md`, dan `docs/ROADMAP.md` dulu di awal sesi untuk konteks lengkap sebelum mulai — jangan asumsi, ikuti apa yang tercatat di sana.

## Kondisi saat ini (per 19 Agustus 2026)

- Kode frontend (index.php, kategori.php, artikel.php, cari.php, includes/, assets/) sudah di-redesign total ke tema biru "ArenaSport77" (mockup V3, `docs/homepage-mockup-v3.html`) dan **sudah diverifikasi jalan di dev lokal** (operator sudah cek di browser).
- 4 kategori final: **Olahraga, Gaya Hidup, Sepak Bola, Otomotif**. 3 artikel contoh sudah ada di DB dev (Olahraga, Sepak Bola, Otomotif) — kategori **Gaya Hidup masih kosong**.
- Struktur permalink **`/artikel/{slug}` dan `/kategori/{slug}` sudah final** — TIDAK perlu diubah lagi, operator sudah konfirmasi.
- Git repo sudah ada, remote: `https://github.com/jalijali-dev/wcm-arenasport77.com.git`, branch `main`.
- **PENTING — cek dulu sebelum lanjut**: kemungkinan besar masih ada banyak perubahan yang **belum di-commit** ke git (rebrand ArenaSport77, redesign V3, docs). Jalankan `git status` di awal sesi. Kalau ada perubahan belum ter-commit, review lalu commit & push ke `origin/main` dulu sebelum deploy — jangan deploy kode yang belum masuk git.
- Ada laporan sebelumnya soal `.git/index.lock` sempat gagal ke-unlink di salah satu sesi lain (izin file) — kalau kamu ketemu error serupa saat git command, cek dulu apakah ada proses git lain yang masih jalan sebelum menghapus lock file secara paksa.
- Dua file template `cms-admin/config/app.php.example` dan `cms-admin/config/database.php.example` yang harusnya ada (disebut di `docs/HANDOFF.md`) **hilang dari disk** — kemungkinan kehapus gak sengaja. Ini gak blocking buat go-live, tapi kalau sempat, bikin ulang dari isi `cms-admin/config/app.php` dan `database.php` versi dev sekarang (redact kredensial asli, ganti jadi placeholder) supaya proyek clone berikutnya (Tentakel lain) tetap punya template.

## Tujuan sesi ini: bikin arenasport77.com live dan bisa diakses publik dengan benar

Kerjakan checklist ini urut dari atas. Di setiap langkah yang butuh keputusan/kredensial yang cuma operator yang tau (path cPanel, DB production, dll), **tanya dulu ke operator lewat chat** — jangan asumsi atau ngarang kredensial.

### 1. Commit & push kode terbaru

- `git status`, review diff, commit semua perubahan rebrand/redesign/docs yang belum ke-commit (pisahkan jadi beberapa commit logis kalau memang lebih rapi, atau satu commit besar juga gak masalah untuk tahap ini).
- Push ke `origin main`.
- Pastikan `.gitignore` masih benar melindungi `cms-admin/config/database.php`, `cms-admin/config/app.php`, dan semua file `_*.php` (one-off scripts) — **JANGAN sampai kredensial atau script sekali-pakai ikut ter-commit**. Kalau ada yang kelanjur ke-commit di masa lalu, cek history-nya dan bersihkan (`git rm --cached`, atau kalau kredensial asli pernah ke-push, anggap bocor dan generate ulang semuanya).

### 2. Setup deploy di cPanel (Git Version Control)

- Login ke cPanel akun hosting arenasport77.com. Buka menu **Git Version Control** → clone repo `https://github.com/jalijali-dev/wcm-arenasport77.com.git` (kalau private repo, siapkan deploy key/PAT sesuai kebutuhan GitHub).
- Cek **path docroot asli** untuk domain arenasport77.com di cPanel → Domains (biasanya `/home/USERNAME/public_html/` tapi USERNAME dan strukturnya beda-beda tiap hosting — tanya operator kalau gak yakin akses cPanel-nya sendiri).
- Edit `.cpanel.yml` di repo: ganti `/home/USERNAME/public_html/` ke path docroot asli, dan update komentar di file itu yang masih nyebut "biangolahraga.com" jadi "arenasport77.com" (cuma komentar, gak fungsional, tapi biar gak membingungkan). Commit & push perubahan ini.
- Di cPanel Git Version Control, klik **"Manage" → "Pull or Deploy" → "Deploy HEAD Commit"** buat trigger `.cpanel.yml` (rsync kode ke docroot, exclude `.git`, `cms-admin/config/database.php`, `cms-admin/config/app.php`, `uploads`).

### 3. Bikin config production langsung di server (JANGAN lewat git)

`cms-admin/config/database.php` dan `cms-admin/config/app.php` sengaja gitignored — harus dibuat manual langsung di server produksi (lewat cPanel File Manager atau SSH), bukan di-push dari lokal.

- **Database production**: minta operator bikin database MySQL baru **kosong** di cPanel (MySQL Databases), nama bebas tapi sebaiknya jelas (mis. `usernamec_arenasport77` — cPanel biasanya prefix otomatis). **JANGAN pakai fitur "Copy database" dari database manapun** — ini prinsip isolasi konten yang sudah jadi pelajaran dari insiden sebelumnya (lihat `docs/HANDOFF.md` bagian "Isolasi Database"). Harus genuinely kosong.
- Bikin `cms-admin/config/database.php` di server dengan kredensial DB production itu (host biasanya `localhost` di shared hosting, bukan `mysql` seperti di dev Docker).
- Bikin `cms-admin/config/app.php` di server: `CMS_ADMIN_NAME` = `'WCM'`, `CMS_ADMIN_TAGLINE` = `'ArenaSport77'` (samain sama dev), tapi **`CMS_AI_ENC_SECRET` dan `GROWTH_AGENT_DIGEST_TOKEN` WAJIB di-generate baru** (`bin2hex(random_bytes(32))`), jangan reuse punya dev lokal — beda environment, beda secret.

### 4. Jalankan schema migration + verifikasi kategori auto-seed

- Buka `https://arenasport77.com/cms-admin/` sekali di browser — ini akan trigger `schema-guard.php` buat auto-create semua tabel yang dibutuhkan (`pages`, `article_categories`, `admins`, dll) di database production yang masih kosong.
- Buka `https://arenasport77.com/` sekali — ini akan trigger `wpm_site_migrate_categories()` di `includes/site-bootstrap.php`, otomatis insert 4 kategori final (Olahraga, Gaya Hidup, Sepak Bola, Otomotif) ke database production.

### 5. Bikin admin account production pertama

Database production kosong belum ada admin sama sekali, jadi belum bisa login ke `cms-admin/`. Ikuti pola yang sama seperti yang sudah dipakai di dev lokal (lihat `docs/HANDOFF.md` bagian "Isolasi Database" soal `_cleanup-fresh-start.php`, dan `cms-admin/_seed-articles.php` yang barusan dipakai buat isi artikel):

- Bikin script sekali-pakai (misal `cms-admin/_create-first-admin.php`) yang INSERT 1 row ke tabel `admins` (kolom: `name`, `email`, `password_hash` pakai `password_hash()`, `role` = `'superadmin'`, `is_active` = 1) — dengan **safety guard** cuma mau jalan kalau DB_NAME cocok dengan nama DB production, dan cuma mau jalan kalau tabel `admins` masih kosong (`SELECT COUNT(*) FROM admins`) supaya gak bisa dipakai buat bikin admin liar berkali-kali kalau lupa dihapus.
- Generate password random (`bin2hex(random_bytes(9))`), tampilkan sekali di layar, minta operator catat langsung.
- Upload & jalankan sekali lewat browser, lalu **HAPUS file itu dari server** segera setelah dipakai — jangan sampai nyangkut, sama seperti aturan di script-script sekali-pakai sebelumnya.
- Login ke `cms-admin/` pakai akun baru itu buat verifikasi.

### 6. DNS & SSL

- Pastikan domain **arenasport77.com** sudah di-pointing (A record atau nameserver) ke hosting cPanel yang dipakai. Kalau belum, ini keputusan/akses di level registrar domain — tanya operator siapa yang pegang akses domain.
- Aktifkan SSL (AutoSSL bawaan cPanel biasanya cukup, atau Let's Encrypt) untuk `arenasport77.com` dan `www.arenasport77.com`. Pastikan situs bisa diakses via `https://` dan idealnya http otomatis redirect ke https.

### 7. Verifikasi rewrite rules (permalink)

- `.htaccess` di root sudah punya rule `/artikel/{slug}` dan `/kategori/{slug}` — pastikan `mod_rewrite` aktif di hosting (default aktif di hampir semua shared hosting cPanel, tapi cek kalau ada masalah 404).
- Test langsung: buka `https://arenasport77.com/artikel/<slug-artikel-yang-ada>` dan `https://arenasport77.com/kategori/olahraga` di browser, pastikan gak 404.

### 8. Hardening production

- Pastikan `display_errors` PHP di production **OFF** (jangan tampilkan stack trace/error PHP mentah ke publik) — cek `php.ini` cPanel (MultiPHP INI Editor) atau tambahkan `ini_set('display_errors', '0')` di entry point kalau perlu, sambil tetap log error ke file (`log_errors` ON).
- Pastikan folder `uploads/` writable oleh web server (permission biasanya `755` folder / `644` file, sesuaikan sama kebiasaan hosting cPanel).
- Double-check **tidak ada file `_*.php` (script sekali-pakai) yang nyangkut** di server production setelah semua langkah di atas selesai — ini prinsip keamanan yang berulang kali disebut di docs proyek ini, jangan sampai kelewat.

### 9. SEO dasar (Fase 5 di `docs/ROADMAP.md`)

- Cek apakah `cms-admin/pages/sitemaps.php` / `includes/sitemap-service.php` sudah generate sitemap otomatis (`sitemap.xml` atau sejenisnya) — kalau ada, pastikan bisa diakses publik di production.
- Bikin/cek `robots.txt` di root, arahkan ke sitemap.
- Setup Google Search Console buat property `arenasport77.com`, verifikasi ownership, submit sitemap.

### 10. Smoke test akhir + update docs

- Test manual di browser production: homepage, tiap kategori, artikel, search, login admin, logout — pastikan semuanya jalan tanpa error dan gambar/CSS ke-load dengan benar (bukan mixed-content http/https).
- Setelah semua beres dan situs benar-benar live, update `docs/ROADMAP.md` (Fase 5 — Pra-Launch) dan `docs/roadmap-progress.html` mencatat tanggal go-live serta checklist mana yang sudah/belum (misalnya kalau SEO/GSC belum sempat, catat itu sebagai next step, jangan tandai selesai kalau belum beneran).

---

Kalau ada keputusan yang butuh operator (kredensial cPanel, path docroot, siapa pegang akses domain registrar, dll), berhenti dan tanya di chat — jangan lanjut eksekusi dengan asumsi.
