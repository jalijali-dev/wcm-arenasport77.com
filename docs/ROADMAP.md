# Progress Roadmap — WCM 2 - Version 1

Status per 13 Agustus 2026.

Legenda: 🟢 Selesai · 🟠 Sebagian · ⚪ Belum mulai

> **Update 13 Agu 2026 (nama brand direvisi):** Nama brand sempat
> ditentukan "Biang Bola", tapi diganti operator ke **Biang Olahraga**
> karena risiko copyright pada nama sebelumnya. Semua referensi di
> dokumen & kode sudah disesuaikan.
>
> Niche & brand final: **Biang Olahraga**, multi-cabang olahraga dengan 4 kategori: Bulu Tangkis,
> Tinju, Moto GP, Tips. Mockup homepage V2 (referensi visual juara.net,
> tema gelap/dark dengan aksen warna per kategori, layout bento asimetris
> + rail kategori vertikal) sudah disetujui operator, **dan sudah
> diimplementasikan ke kode PHP asli** (index.php, kategori.php,
> artikel.php, cari.php, includes/site-header.php dst — nyambung penuh ke
> data CMS real, bukan hardcode). Yang masih belum final: domain, dan
> detail struktur permalink (sementara pakai pola `/artikel/{slug}` &
> `/kategori/{slug}` yang sama dengan wcm1_version1 — lihat catatan di
> `.htaccess`, siap diganti begitu operator konfirmasi).
>
> **Insiden isolasi database (13 Agu 2026):** login pertama ke admin
> panel WCM 2 nampilin data olahraga77.com asli (admin "olahraga77", 4
> artikel, 74 views) — ternyata database `wpm_cms_wcm2_version1` dibuat
> lewat "Copy database" phpMyAdmin dari wcm1_version1, bukan kosong.
> Sudah diperbaiki: script sekali-pakai `_cleanup-fresh-start.php`
> di-jalankan untuk TRUNCATE semua tabel + bikin admin baru
> (`admin@olahraga77.com`). Database sekarang genuinely kosong. Detail
> di `docs/HANDOFF.md` bagian "Isolasi Database".

## Fase 0 — Pondasi 🟠 Sebagian

Database MySQL dev lokal `wpm_cms_wcm2_version1` sudah ada dan sudah
genuinely kosong (lihat catatan insiden isolasi database di atas —
sempat ke-copy penuh dari wcm1_version1, sudah dibersihkan via
`_cleanup-fresh-start.php`). Domain masih belum ditentukan. Belum ada
Git repo, hosting, atau GSC — semua menunggu domain final. Folder
proyek (`wcm2_version1`) sudah dibuat operator dan connect ke Cowork.

## Fase 1 — Backend: Schema & Adaptasi CMS 🟠 Sebagian

`cms-admin/` sudah di-clone dari `wcm1_version1` (bukan dari money site
langsung), sesuai standar `HANDOFF-CMS-ADMIN.md`. Config DB & secret
sudah dipisah (DB name `wpm_cms_wcm2_version1`, encryption secret
digenerate ulang, tagline diganti placeholder). Login admin panel sudah
berhasil dites (dengan admin baru `admin@olahraga77.com` pasca-cleanup).
Belum: tabel di DB masih perlu di-generate ulang lewat schema migration
(kosong pasca-cleanup), audit modul yang relevan/gak relevan juga belum
diulang khusus buat topik Biang Olahraga, tagline placeholder
`WCM2.VERSION1` belum diganti ke nama brand asli.

## Fase 2 — Backend: Isi Konten Struktural ⚪ Belum mulai

Niche sudah ditentukan (Biang Olahraga, multi-cabang olahraga), tapi belum
ada satu kategori atau artikel pun yang dibuat di database — situs masih
benar-benar kosong. 4 kategori final yang perlu dibuat begitu tabel
migration jalan: Bulu Tangkis, Tinju, Moto GP, Tips.

## Fase 3 — Frontend: Bangun dari Mockup 🟠 Sebagian

Mockup homepage V2 sudah diimplementasikan penuh ke kode PHP asli:
`index.php` (ticker, bento hero dari headline per kategori, filmstrip
artikel per kategori, list Tips + panel Terpopuler), `kategori.php`,
`artikel.php`, `cari.php`, dan partial `includes/site-header.php` /
`includes/site-footer.php` (rail kategori vertikal, header
menu-kiri/search-kanan). Semua nyambung ke data CMS real lewat
`includes/site-bootstrap.php` (bukan hardcode) — tapi karena tabel
`pages`/`article_categories` masih kosong (belum ada artikel), tampilan
saat ini baru bisa dites lewat empty-state. Belum: detail struktur
permalink URL final dari operator (sementara pakai pola
`/artikel/{slug}` & `/kategori/{slug}`, sama dengan wcm1_version1),
verifikasi visual di browser dengan data artikel sungguhan.

## Fase 4 — Branding & Polish 🟠 Sebagian

Nama brand final sudah ditentukan: **Biang Olahraga**. Palet warna mockup
sudah dipakai (dark/ink base + aksen merah, teal, gold, violet per
kategori). Branding admin panel **sudah disesuaikan (16 Agu 2026)**:
`CMS_ADMIN_NAME`/`CMS_ADMIN_TAGLINE` gak lagi placeholder `WPM`/
`WCM2.VERSION1`, logo WPM warisan clone diganti wordmark teks "BO"
sementara (SVG) di sidebar/login/favicon admin. Belum: logo profesional
final untuk Biang Olahraga (yang sekarang masih placeholder text-based,
bukan logo asli), favicon publik `assets/img/favicon.svg` juga masih
placeholder huruf "B" sederhana yang sama.

## Fase 5 — Pra-Launch ⚪ Belum mulai

Belum relevan — nunggu Fase 1-4 selesai dan domain ditentukan dulu.

## Fase 6 — AI Automation Layer ⚪ Belum mulai

Modul Growth Agent ikut ter-clone dari wcm1_version1 (prompt sudah netral,
gak nyebut nama situs manapun) — jadi prasyarat kode sudah ada begitu
saatnya dipakai. Belum aktif dipakai generate konten apapun untuk Biang
Olahraga — prompt/style rules juga masih perlu disesuaikan ke topik
multi-cabang olahraga (bukan cuma satu cabang), bukan cuma warisan dari
wcm1_version1 yang fokus sepak bola.

---

*Untuk checklist detail proses clone/handoff dan hal yang masih perlu
dikonfirmasi ke operator, lihat `docs/HANDOFF.md`. Standar umum
clone/handoff admin panel ada di `wcm1_version1/docs/HANDOFF-CMS-ADMIN.md`.*
