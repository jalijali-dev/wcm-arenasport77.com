<?php
declare(strict_types=1);
/**
 * includes/site-header.php — shared header partial for Biang Olahraga.
 * Implements the approved mockup V2 (homepage-mockup-v2.html): solid
 * black bar, bold uppercase menu on the LEFT, search on the RIGHT behind
 * a plain vertical divider (the original diagonal-cut divider looked
 * broken in practice — flagged by operator 13 Agu 2026 and replaced),
 * brand name at the head of the nav. Also opens
 * the <div class="wpm-page"> wrapper + sticky vertical category rail —
 * every page using this partial MUST close with </div> before
 * site-footer.php (site-footer.php does not close it for you).
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
    <nav class="wpm-header__nav-left" aria-label="Navigasi utama">
      <a href="<?= wpm_esc(wpm_base_url('/')) ?>" class="wpm-brand">BIANG<span class="wpm-brand__accent">OLAHRAGA</span></a>
      <a href="<?= wpm_esc(wpm_base_url('/')) ?>" class="<?= $activeNavSlug === 'home' ? 'is-active' : '' ?>">Home</a>
      <?php foreach ($navCategories as $navSlug => $navLabel): ?>
      <a href="<?= wpm_esc(wpm_category_url($navSlug)) ?>" class="<?= $activeNavSlug === $navSlug ? 'is-active' : '' ?>"><?= wpm_esc($navLabel) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="wpm-header__search-wrap">
      <form class="wpm-header__search" action="<?= wpm_esc(wpm_base_url('/cari.php')) ?>" method="get" role="search">
        <svg class="wpm-header__search-icon" width="15" height="15" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <input type="text" name="q" placeholder="Search" aria-label="Cari berita">
      </form>
    </div>
  </div>
</header>

<div class="wpm-page">
  <aside class="wpm-rail" aria-label="Kategori">
    <?php foreach ($navCategories as $navSlug => $navLabel): ?>
    <a href="<?= wpm_esc(wpm_category_url($navSlug)) ?>" class="wpm-rail__<?= wpm_esc(wpm_category_color_class($navSlug)) ?> <?= $activeNavSlug === $navSlug ? 'is-active' : '' ?>"><?= wpm_esc($navLabel) ?></a>
    <div class="wpm-rail__dot"></div>
    <?php endforeach; ?>
  </aside>

  <main class="wpm-content">
    <div class="wpm-wrap">
