# Project: WCM 2 - Version 1 — "Tentakel 2" untuk struktur PBN — brand: Biang Olahraga

> **WAJIB DIBACA DI AWAL SESI, SEBELUM AKSI APAPUN.** File ini adalah
> instruksi proyek, bukan sekadar catatan — ikuti isinya. Setelah baca
> file ini, baca juga `docs/HANDOFF.md` (checklist detail) dan
> `docs/ROADMAP.md` (status per fase) sebelum mulai kerja. Kalau ada
> keputusan besar yang masih kosong di bawah (permalink, domain), TANYA
> operator dulu lewat chat — jangan asumsi atau eksekusi sendiri duluan.

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

Domain dan detail struktur permalink WCM 2 **masih belum ditentukan**
per 13 Agu 2026 — lihat `docs/HANDOFF.md` untuk daftar lengkap hal yang
masih perlu dikonfirmasi operator sebelum lanjut ke tahap berikutnya.

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

## Yang BELUM dikerjakan — task list buat lanjut

1. Kasih detail struktur permalink yang mau dibuat beda dari
   `/artikel/{slug}` punya wcm1_version1.
2. Jalanin schema migration di database `wpm_cms_wcm2_version1` (tabel
   masih kosong), lalu input 4 kategori final (Bulu Tangkis/Tinju/Moto
   GP/Tips) ke CMS.
3. Audit ulang modul cms-admin sesuai konteks multi-cabang olahraga
   (bukan cuma sepak bola seperti wcm1_version1).
4. Implementasikan mockup homepage yang sudah disetujui ke frontend
   publik sungguhan (index.php, kategori.php, artikel.php,
   site-header.php, dst.) — sambungkan ke data CMS real begitu poin 1-2
   selesai.
5. Ganti tagline placeholder `WCM2.VERSION1` di admin panel ke "Biang
   Olahraga" begitu operator konfirmasi nama ini final.
6. Domain, Git repo, hosting cPanel, GSC — semua menunggu domain final.

## Cara lanjut sesi ini

Baca file ini dulu di awal sesi, lalu `docs/HANDOFF.md` buat detail
checklist, dan `docs/ROADMAP.md` buat status per fase. Kalau ada
keputusan besar yang belum jelas (permalink, domain), TANYA operator
dulu — jangan asumsi sendiri.
