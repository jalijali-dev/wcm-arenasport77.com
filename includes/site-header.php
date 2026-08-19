<?php
declare(strict_types=1);
/**
 * includes/site-header.php — shared header partial for ArenaSport77.
 * Implements the approved mockup V3 (docs/homepage-mockup-v3.html, update
 * 19 Agu 2026, rebrand dari Biang Olahraga): tema biru cerah, header
 * putih simple (logo kiri, nav horizontal, search kanan sebagai pill
 * bulat), TANPA rail kategori vertikal lagi (dihapus bareng mockup
 * V2/tema gelap punya Biang Olahraga). Opens <main class="wpm-content">
 * only (NOT .wpm-wrap) — full-bleed sections like the homepage ticker
 * need to sit outside the 1200px wrap, so each page is responsible for
 * wrapping its own content in <div class="wpm-wrap">...</div> wherever
 * it wants that max-width applied. Every page using this partial MUST
 * close </main> before site-footer.php (site-footer.php does not close
 * it for you).
 *
 * Expects (optional): $pageTitle, $metaDescription, $activeNavSlug.
 */
$pageTitle = $pageTitle ?? WPM_SITE_NAME;
$metaDescription = $metaDescription ?? WPM_SITE_TAGLINE;
$activeNavSlug = $activeNavSlug ?? '';
$navCategories = wpm_site_nav_categories();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= wpm_esc($pageTitle) ?></title>
<meta name="description" content="<?= wpm_esc($metaDescription) ?>">
<link rel="stylesheet" href="<?= wpm_esc(wpm_base_url('/assets/css/site.css')) ?>">
<link rel="icon" type="image/svg+xml" href="<?= wpm_esc(wpm_base_url('/assets/img/favicon.svg')) ?>">
<link rel="alternate icon" href="<?= wpm_esc(wpm_base_url('/assets/img/favicon.ico')) ?>">
</head>
<body>

<header class="wpm-header">
  <div class="wpm-wrap wpm-header__bar">
    <a href="<?= wpm_esc(wpm_base_url('/')) ?>" class="wpm-brand"><span class="wpm-brand__mark">A77</span> <?= wpm_esc(WPM_SITE_NAME) ?></a>
    <nav class="wpm-header__nav-left" aria-label="Navigasi utama">
      <a href="<?= wpm_esc(wpm_base_url('/')) ?>" class="<?= $activeNavSlug === 'home' ? 'is-active' : '' ?>">Home</a>
      <?php foreach ($navCategories as $navSlug => $navLabel): ?>
      <a href="<?= wpm_esc(wpm_category_url($navSlug)) ?>" class="<?= $activeNavSlug === $navSlug ? 'is-active' : '' ?>"><?= wpm_esc($navLabel) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="wpm-header__search-wrap">
      <form class="wpm-header__search" action="<?= wpm_esc(wpm_base_url('/cari.php')) ?>" method="get" role="search">
        <svg class="wpm-header__search-icon" width="15" height="15" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <input type="text" name="q" placeholder="Cari" aria-label="Cari berita">
      </form>
    </div>
  </div>
</header>

<main class="wpm-content">
