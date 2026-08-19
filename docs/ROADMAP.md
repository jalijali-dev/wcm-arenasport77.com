# Progress Roadmap — WCM 2 - Version 2

Status per 19 Agustus 2026 (pasca-rebrand ArenaSport77).

Legenda: 🟢 Selesai · 🟠 Sebagian · ⚪ Belum mulai

> **REBRAND 19 Agu 2026 — baca ini duluan, timpa update-update di bawah
> yang masih sebut "Biang Olahraga".** Dua pivot beruntun hari ini: (1)
> 4 kategori final diganti dari Bulu Tangkis/Tinju/Moto GP/Tips ke
> **Olahraga, Gaya Hidup, Sepak Bola, Otomotif**; (2) brand & domain
> diganti total dari Biang Olahraga/biangolahraga.com ke **ArenaSport77 /
> arenasport77.com**. Mockup homepage **V3** (tema biru cerah, layout
> editorial/list ala CNN Indonesia, nav simple) disetujui operator dan
> **sudah diimplementasikan penuh** ke `index.php`, `kategori.php`,
> `artikel.php`, `cari.php`, `includes/site-bootstrap.php`,
> `includes/site-header.php`/`site-footer.php`, `assets/css/site.css`,
> `assets/img/favicon.svg`, `cms-admin/config/app.php`. Git remote
> di-rename ke `github.com/jalijali-dev/wcm-arenasport77.com.git`.
> `DB_NAME` tetap `wpm_cms_wcm2_version2` (gak ikut berubah by design).
> **Update 19 Agu 2026 (lanjutan) — verifikasi visual SELESAI:** operator
> sudah buka situsnya langsung di browser, konfirmasi tampilan V3 (tema
> biru, layout list-style, nav baru) render benar. Sempat ada revisi kecil
> (jarak ticker berjalan ke konten di bawahnya kedempetan) — sudah
> diperbaiki (`.wpm-ticker` dikasih `margin-bottom`), dikonfirmasi beres.
> **3 artikel contoh pertama juga sudah di-input** (lewat script sekali-
> pakai `_seed-articles.php`, sudah dihapus dari server) — satu per
> kategori Olahraga/Sepak Bola/Otomotif, masing-masing dengan featured
> image SVG orisinal. Sisa yang **belum**: artikel kategori Gaya Hidup
> (belum ada sama sekali), audit modul cms-admin/Growth Agent buat 4
> kategori baru, logo profesional final, `DEPLOYPATH` di `.cpanel.yml`.
> Detail lengkap ada di bagian atas `docs/HANDOFF.md` ("REBRAND 19 Agu
> 2026").

> **Update 13 Agu 2026 (nama brand direvisi) — historis, sebelum
> rebrand:** Nama brand sempat
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
>
> **Update 19 Agu 2026 (dokumen ini sempat ketinggalan dari repo asli):**
> saat direview ulang, beberapa hal sudah lebih maju dari yang tercatat
> di sini per 16 Agu:
> - **Domain sudah final: biangolahraga.com.**
> - **Git repo sudah ada** — remote
>   `github.com/jalijali-dev/wcm-biangolahraga.com`, 1 commit ("Initial
>   commit — biangolahraga.com go-live prep").
> - **`.cpanel.yml` sudah dibuat** untuk deploy cPanel — tapi
>   `DEPLOYPATH` masih placeholder `/home/USERNAME/public_html/`, wajib
>   diganti ke path docroot cPanel asli sebelum deploy pertama.
> - `DB_NAME` **belum** disinkronkan ke pola nama final — masih
>   `wpm_cms_wcm2_version1` (nama kerja lama).
> - Status tabel/kategori di database belum diverifikasi ulang sejak
>   16 Agu (lihat Fase 1 & 2).

## Fase 0 — Pondasi 🟢 Selesai (update 19 Agu)

Domain **sudah final: biangolahraga.com**. Git repo sudah ada (lihat
update di atas), `.cpanel.yml` untuk deploy cPanel juga sudah dibuat
(path docroot masih placeholder, belum final). Database MySQL dev lokal
`wpm_cms_wcm2_version1` sudah ada dan genuinely kosong (lihat catatan
insiden isolasi database di atas). Folder proyek (`wcm2_version2`) sudah
dibuat operator dan connect ke Cowork. Sisa PR kecil: `DB_NAME` masih
pakai nama kerja lama, belum disinkronkan ke pola nama final
biangolahraga, dan hosting cPanel/GSC belum di-setup (menunggu path
docroot & kredensial cPanel asli).

## Fase 1 — Backend: Schema & Adaptasi CMS 🟠 Sebagian

`cms-admin/` sudah di-clone dari `wcm1_version1` (bukan dari money site
langsung), sesuai standar `HANDOFF-CMS-ADMIN.md`. Config DB & secret
sudah dipisah (DB name `wpm_cms_wcm2_version1` — masih perlu diganti ke
pola final, lihat update di atas; encryption secret sudah digenerate
ulang). Login admin panel sudah berhasil dites (dengan admin baru
`admin@olahraga77.com` pasca-cleanup). Tabel schema di-generate otomatis
lewat helper `cms_ensure_table()`/`cms_ensure_column()`
(`cms-admin/includes/schema-guard.php`) begitu halaman terkait diakses —
belum diverifikasi ulang sejak 16 Agu apakah tabel `pages` &
`article_categories` sudah benar-benar ter-create di database saat ini.
Belum: audit modul yang relevan/gak relevan diulang khusus buat topik
Biang Olahraga, rename `DB_NAME` ke pola final.

## Fase 2 — Backend: Isi Konten Struktural 🟠 Sebagian (update 19 Agu, pasca-rebrand)

4 kategori final (Olahraga, Gaya Hidup, Sepak Bola, Otomotif) **sudah
didefinisikan sebagai single source of truth di kode**
(`wpm_site_nav_categories()` di `includes/site-bootstrap.php`) dan
fungsi `wpm_site_migrate_categories()` otomatis insert/update ke-4
kategori itu ke tabel `article_categories` setiap kali halaman frontend
publik diakses — **dikonfirmasi jalan** (kategori muncul otomatis begitu
operator buka homepage). **3 artikel contoh pertama sudah di-input**
(published, lengkap dengan featured image SVG orisinal): satu di
Olahraga ("5 Latihan Ringan Sebelum Mulai Rutin Olahraga untuk Pemula"),
satu di Sepak Bola ("Memahami Aturan Offside dalam Sepak Bola"), satu di
Otomotif ("5 Hal yang Wajib Dicek Sebelum Motor Dipakai Perjalanan
Jauh") — di-input lewat script sekali-pakai `_seed-articles.php` (sudah
dihapus dari server sesuai prosedur). Belum: kategori **Gaya Hidup**
masih kosong total, dan 3 artikel yang ada baru contoh/starter — volume
konten asli buat SEO & PBN masih jauh dari cukup.

## Fase 3 — Frontend: Bangun dari Mockup 🟢 Selesai (update 19 Agu, pasca-rebrand)

Paragraf di bawah ini soal mockup V2/Biang Olahraga sudah historis — lihat
catatan REBRAND di paling atas dokumen ini. Status terkini: mockup **V3**
(tema biru, layout editorial/list, 4 kategori baru Olahraga/Gaya
Hidup/Sepak Bola/Otomotif) sudah diimplementasikan penuh ke `index.php`,
`kategori.php`, `artikel.php`, `cari.php`, `includes/site-header.php` /
`site-footer.php` (tanpa rail vertikal lagi), semua nyambung ke data CMS
real lewat `includes/site-bootstrap.php`. **Operator sudah buka
langsung di browser dan konfirmasi tampilannya render benar (19 Agu
2026)** — verifikasi visual selesai. Belum: detail struktur permalink
URL final dari operator (sementara masih pakai pola `/artikel/{slug}` &
`/kategori/{slug}`, sama dengan wcm1_version1).

<details><summary>Historis — status Fase 3 sebelum rebrand (13-16 Agu, mockup V2)</summary>

Mockup homepage V2 sudah diimplementasikan penuh ke kode PHP asli:
`index.php` (ticker, bento hero dari headline per kategori, filmstrip
artikel per kategori, list Tips + panel Terpopuler), `kategori.php`,
`artikel.php`, `cari.php`, dan partial `includes/site-header.php` /
`includes/site-footer.php` (rail kategori vertikal, header
menu-kiri/search-kanan). Semua nyambung ke data CMS real lewat
`includes/site-bootstrap.php` (bukan hardcode) — tapi karena tabel
`pages`/`article_categories` masih kosong (belum ada artikel), tampilan
saat itu baru bisa dites lewat empty-state.

</details>

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
