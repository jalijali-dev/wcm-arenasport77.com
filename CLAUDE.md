# Project: WCM 2 - Version 2 — "Tentakel 2" untuk struktur PBN — brand: ArenaSport77

> **WAJIB DIBACA DI AWAL SESI, SEBELUM AKSI APAPUN.** File ini adalah
> instruksi proyek, bukan sekadar catatan — ikuti isinya. Setelah baca
> file ini, baca juga `docs/HANDOFF.md` (checklist detail) dan
> `docs/ROADMAP.md` (status per fase) sebelum mulai kerja. Kalau ada
> keputusan besar yang masih kosong di bawah (permalink), TANYA operator
> dulu lewat chat — jangan asumsi atau eksekusi sendiri duluan.

> **REBRAND 19 Agu 2026 — baca ini duluan, timpa semua yang di bawah
> soal "Biang Olahraga".** Operator mengganti brand & domain: brand dan
> nama proyek sekarang **ArenaSport77**, domain final **arenasport77.com**
> (ganti dari draft sebelumnya "Biang Olahraga" / biangolahraga.com — DUA
> pivot beruntun: kategori diganti duluan, lalu brand/domain diganti
> lagi). **4 kategori final juga berubah**: ~~Bulu Tangkis, Tinju, Moto
> GP, Tips~~ → **Olahraga, Gaya Hidup, Sepak Bola, Otomotif**. Tema visual
> mockup juga diganti total (bukan revisi) dari dark/bento/rail-vertikal
> ala juara.net ke **tema biru cerah, layout editorial/list ala CNN
> Indonesia**, nav simple (Home/Olahraga/Gaya Hidup/Sepak Bola/Otomotif)
> — lihat `docs/homepage-mockup-v3.html` (disetujui operator) dan sudah
> diimplementasikan penuh ke kode PHP (index.php, kategori.php,
> artikel.php, cari.php, site-header/footer.php, site.css,
> site-bootstrap.php). Git repo remote juga sudah di-rename ke
> `github.com/jalijali-dev/wcm-arenasport77.com`. `DB_NAME` **tidak**
> ikut berubah — tetap `wpm_cms_wcm2_version2` (operator sengaja pilih
> nama kerja generik, bukan ikut nama brand, supaya gak perlu rename DB
> lagi tiap kali brand berubah). Paragraf-paragraf di bawah yang masih
> menyebut "Biang Olahraga" / kategori lama dibiarkan sebagai jejak
> historis proses keputusan — JANGAN dianggap kondisi final, ikuti update
> ini.

## Konteks & tujuan

Situs ini adalah "Tentakel 2" dalam struktur link-building operator:
Kepala (money site) ← dapat backlink dari Tentakel 1 (`wcm1_version1`,
sebelumnya olahraga77.com) ← dapat backlink dari Tentakel 2 (situs ini).
Jadi WCM 2 ini **backlink-nya menunjuk ke wcm1_version1, BUKAN langsung
ke money site.**

**Nama brand: Biang Olahraga** — situs multi-cabang olahraga dengan 4
kategori final: **Bulu Tangkis, Tinju, Moto GP, Tips**. Mockup homepage
sudah dibuat dan **disetujui operator** (referensi visual juara.net, tema
gelap/dark dominan dengan aksen warna per kategori, header menu-kiri +
search-kanan, rail kategori vertikal sticky, hero bento asimetris,
artikel per kategori sebagai filmstrip horizontal-scroll).

> **Update 19 Agu 2026 — dokumen ini sempat ketinggalan dari kondisi
> repo asli.** Domain **sudah final: biangolahraga.com**. Git repo sudah
> diinisialisasi (`origin: github.com/jalijali-dev/wcm-biangolahraga.com`,
> 1 commit "Initial commit — biangolahraga.com go-live prep") dan
> `.cpanel.yml` sudah ada untuk deploy cPanel (path `DEPLOYPATH` masih
> placeholder `/home/USERNAME/public_html/`, wajib diganti sebelum deploy
> pertama). Yang **belum** disinkronkan ke domain final: `DB_NAME` di
> `cms-admin/config/database.php` masih `wpm_cms_wcm2_version1` (nama
> kerja lama), belum diganti ke pola nama biangolahraga. Struktur
> permalink masih pola sementara `/artikel/{slug}` & `/kategori/{slug}` —
> belum ada konfirmasi eksplisit dari operator kalau ini final atau
> masih mau diganti. Detail lengkap: lihat update senada di
> `docs/HANDOFF.md` dan `docs/ROADMAP.md`.

Domain dan detail struktur permalink WCM 2 **masih belum ditentukan**
per 13 Agu 2026 — lihat `docs/HANDOFF.md` untuk daftar lengkap hal yang
masih perlu dikonfirmasi operator sebelum lanjut ke tahap berikutnya.
**(Update 19 Agu: domain sudah final, lihat catatan update di atas —
paragraf ini dibiarkan sebagai jejak historis, jangan dianggap kondisi
saat ini.)**

**Detail lengkap proses clone/setup:** lihat `docs/HANDOFF.md`.
**Progress per fase:** lihat `docs/ROADMAP.md`.
**Standar umum clone/handoff admin panel (dipakai lintas proyek):**
`wcm1_version1/docs/HANDOFF-CMS-ADMIN.md`.

## Risiko penting yang harus diingat (jangan skip)

1. **Database HARUS terpisah** — dari wcm1_version1 maupun dari money
   site. `cms-admin/config/database.php` sudah diarahkan ke
   `wpm_cms_wcm2_version1`, dan **database ini sudah dibuat operator** di
   MySQL dev lokal (masih kosong, belum ada tabel — jangan asumsikan
   admin panel bisa langsung jalan penuh sebelum schema migration
   dijalankan).
2. **Hindari duplicate content & PBN footprint** — sama seperti prinsip
   di wcm1_version1: konten harus digenerate/ditulis terpisah (bukan
   copy-paste dari wcm1_version1 atau situs manapun), tema visual harus
   beda (mockup sudah pakai tema gelap dengan referensi juara.net, beda
   dari charcoal+hijau punya wcm1_version1), dan struktur permalink juga
   sengaja dibikin beda (detail masih menyusul dari operator).

## Yang SUDAH dikerjakan

**13 Agu 2026:**
- Operator bikin folder `wcm2_version1`, connect ke Cowork. Folder
  `sagagoal77.com` sebelumnya juga di-rename operator jadi
  `wcm1_version1` biar penamaan seragam antar tentakel.
- `cms-admin/` di-clone dari `wcm1_version1/cms-admin/` (ikut standar
  HANDOFF-CMS-ADMIN — clone dari tentakel terdekat, bukan dari money
  site langsung). Livescore module dan konsep `sport_key` otomatis ikut
  bersih karena sudah dihapus duluan di sumbernya (wcm1_version1).
- Isolasi kredensial: `DB_NAME` diganti `wpm_cms_wcm2_version1`,
  `CMS_AI_ENC_SECRET` di-generate ulang (bukan reuse punya
  wcm1_version1), tagline (`app.php` + `login.php`) diganti placeholder
  `WCM2.VERSION1` — wajib diganti ke domain/brand asli begitu final.
- `docs/HANDOFF.md` dan `docs/ROADMAP.md` dibuat, merekam checklist
  HANDOFF-CMS-ADMIN mana yang udah/belum dieksekusi untuk proyek ini.
- **Operator create database MySQL kosong `wpm_cms_wcm2_version1`** di
  dev lokal (via phpMyAdmin).
- **Niche & brand final ditentukan: Biang Olahraga**, multi-cabang olahraga
  (Bulu Tangkis, Tinju, Moto GP, Tips).
- **Mockup homepage dibuat & disetujui operator** (HTML statis,
  referensi visual juara.net, tema gelap, rail kategori vertikal, hero
  bento asimetris, filmstrip artikel per kategori).

## Yang BELUM dikerjakan — task list buat lanjut (update 19 Agu 2026, pasca-rebrand ArenaSport77)

0. ~~Verifikasi visual di browser~~ — **selesai (19 Agu 2026)**, operator
   sudah buka homepage/kategori/artikel langsung dan konfirmasi tampilan
   V3 (tema biru, layout list-style, nav baru) render benar.
1. Konfirmasi ke operator: permalink `/artikel/{slug}` & `/kategori/{slug}`
   dipakai permanen, atau masih mau diganti pola lain sebelum go-live?
2. Audit ulang modul cms-admin sesuai konteks 4 kategori baru (Olahraga,
   Gaya Hidup, Sepak Bola, Otomotif — beda total dari draft sebelumnya
   yang multi-cabang-olahraga-doang) — termasuk **Growth Agent**
   (`cms-admin/pages/growth-agent.php`), prompt-nya masih warisan
   sepak-bola-doang dari wcm1_version1, belum disesuaikan.
3. **Sebagian selesai (19 Agu 2026):** 3 artikel contoh pertama sudah
   ditulis & di-input (published, dengan featured image) — 1 di
   Olahraga, 1 di Sepak Bola, 1 di Otomotif. Kategori **Gaya Hidup masih
   kosong total**, dan 3 artikel yang ada baru starter — masih perlu
   volume konten asli (bukan copy-paste) yang jauh lebih banyak buat
   SEO & PBN.
4. Isi placeholder yang masih tersisa: logo profesional final untuk
   ArenaSport77 (favicon publik sekarang masih wordmark teks "A77"
   sederhana), path `DEPLOYPATH` di `.cpanel.yml` (masih placeholder
   `/home/USERNAME/public_html/`), robots.txt/sitemap/GSC (Fase 7 SEO,
   belum mulai).
5. Cek ulang semua dokumen (CLAUDE.md, HANDOFF.md, ROADMAP.md) masih ada
   banyak paragraf historis yang menyebut "Biang Olahraga"/kategori lama
   dari sebelum rebrand — dibiarkan sebagai jejak keputusan, tapi kalau
   dirasa kepanjangan/membingungkan bisa diminta rapikan ulang.

## Cara lanjut sesi ini

Baca file ini dulu di awal sesi, lalu `docs/HANDOFF.md` buat detail
checklist, dan `docs/ROADMAP.md` buat status per fase. Kalau ada
keputusan besar yang belum jelas (permalink, domain), TANYA operator
dulu — jangan asumsi sendiri.
