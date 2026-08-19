<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site-bootstrap.php';

$navCategories = wpm_site_nav_categories();
$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '' || !isset($navCategories[$slug])) {
    http_response_code(404);
    $pageTitle = 'Kategori tidak ditemukan — ' . WPM_SITE_NAME;
    require __DIR__ . '/includes/site-header.php';
    echo '<div class="wpm-wrap"><h1 class="wpm-page-title">Kategori tidak ditemukan</h1><p><a href="' . wpm_esc(wpm_base_url('/')) . '">Kembali ke beranda</a></p></div>';
    require __DIR__ . '/includes/site-footer.php';
    exit;
}

$label = $navCategories[$slug];
$colorClass = wpm_category_color_class($slug);
$perPage = 12;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$articles = wpm_get_articles($pdo, $perPage, $offset, $slug);
$total = wpm_count_articles($pdo, $slug);
$totalPages = (int) max(1, ceil($total / $perPage));
$popular = wpm_get_popular_articles($pdo, 5);

$pageTitle = $label . ' — ' . WPM_SITE_NAME;
$metaDescription = 'Kumpulan berita ' . $label . ' terbaru di ' . WPM_SITE_NAME . '.';
$activeNavSlug = $slug;
require __DIR__ . '/includes/site-header.php';
?>
<div class="wpm-wrap">
<div class="wpm-breadcrumb"><a href="<?= wpm_esc(wpm_base_url('/')) ?>">Beranda</a> / <?= wpm_esc($label) ?></div>

<div class="wpm-sec-head <?= $colorClass ?>">
  <span class="wpm-chip"><?= wpm_esc($label) ?></span>
</div>

<div class="wpm-listing-grid">
  <div class="wpm-listing-main">
    <?php foreach ($articles as $article): ?>
    <a href="<?= wpm_esc(wpm_article_url($article['slug'])) ?>" class="wpm-list-card">
      <img src="<?= wpm_esc(wpm_image_url($article['featured_image'])) ?>" alt="<?= wpm_esc($article['title']) ?>">
      <span class="wpm-list-card__body">
        <span class="wpm-list-card__title"><?= wpm_esc($article['title']) ?></span>
        <?php if (!empty($article['excerpt'])): ?><span class="wpm-list-card__excerpt"><?= wpm_esc($article['excerpt']) ?></span><?php endif; ?>
        <span class="wpm-list-card__meta"><?= wpm_esc(wpm_time_ago($article['published_at'])) ?></span>
      </span>
    </a>
    <?php endforeach; ?>
    <?php if (!$articles): ?>
      <p class="wpm-sidebar-empty">Belum ada artikel di kategori <?= wpm_esc($label) ?>.</p>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="wpm-pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php if ($p === $page): ?>
          <span class="is-active"><?= $p ?></span>
        <?php else: ?>
          <a href="<?= wpm_esc(wpm_category_url($slug)) ?>?page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>

  <aside class="wpm-popular-panel">
    <h5>Terpopuler</h5>
    <?php foreach ($popular as $i => $p): ?>
    <a href="<?= wpm_esc(wpm_article_url($p['slug'])) ?>" class="wpm-p-row">
      <span class="wpm-p-row__rank"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      <span class="wpm-p-row__title"><?= wpm_esc($p['title']) ?></span>
    </a>
    <?php endforeach; ?>
    <?php if (!$popular): ?><p class="wpm-sidebar-empty">Belum ada data.</p><?php endif; ?>
  </aside>
</div>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
