<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site-bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$article = $slug !== '' ? wpm_get_article_by_slug($pdo, $slug) : null;

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Artikel tidak ditemukan — ' . WPM_SITE_NAME;
    require __DIR__ . '/includes/site-header.php';
    echo '<h1 class="wpm-page-title">Artikel tidak ditemukan</h1><p><a href="' . wpm_esc(wpm_base_url('/')) . '">Kembali ke beranda</a></p>';
    require __DIR__ . '/includes/site-footer.php';
    exit;
}

wpm_increment_views($pdo, (int) $article['page_id']);
$related = $article['category_id']
    ? wpm_get_related_articles($pdo, (int) $article['category_id'], (int) $article['page_id'], 5)
    : [];
$popular = wpm_get_popular_articles($pdo, 5);
$colorClass = wpm_category_color_class($article['category_slug'] ?? null);

$pageTitle = ($article['meta_title'] ?: $article['title']) . ' — ' . WPM_SITE_NAME;
$metaDescription = $article['meta_description'] ?: $article['excerpt'];
$activeNavSlug = $article['category_slug'] ?? '';
require __DIR__ . '/includes/site-header.php';
?>
<div class="wpm-breadcrumb">
  <a href="<?= wpm_esc(wpm_base_url('/')) ?>">Beranda</a>
  <?php if ($article['category_slug']): ?> / <a href="<?= wpm_esc(wpm_category_url($article['category_slug'])) ?>"><?= wpm_esc($article['category_name']) ?></a><?php endif; ?>
</div>

<div class="wpm-article">
  <div class="wpm-article__main">
    <?php if ($article['category_name']): ?><span class="wpm-badge <?= $colorClass ?>"><?= wpm_esc($article['category_name']) ?></span><?php endif; ?>
    <h1 class="wpm-article__title"><?= wpm_esc($article['title']) ?></h1>
    <div class="wpm-article__meta"><?= wpm_esc(wpm_time_ago($article['published_at'])) ?> &middot; <?= (int) $article['views'] ?> views</div>

    <?php if (!empty($article['featured_image'])): ?>
    <img class="wpm-article__cover" src="<?= wpm_esc(wpm_image_url($article['featured_image'])) ?>" alt="<?= wpm_esc($article['title']) ?>">
    <?php endif; ?>

    <?= wpm_render_ad_slot($pdo, 'article-before-title', 'article', (int) $article['page_id']) ?>

    <div class="wpm-article__body"><?= $article['content'] ?></div>

    <?= wpm_render_ad_slot($pdo, 'article-after-title', 'article', (int) $article['page_id']) ?>
  </div>

  <aside class="wpm-popular-panel">
    <?= wpm_render_ad_slot($pdo, 'sidebar-left', 'article', (int) $article['page_id']) ?>
    <?php if ($related): ?>
    <h5>Artikel Terkait</h5>
    <?php foreach ($related as $r): ?>
    <a href="<?= wpm_esc(wpm_article_url($r['slug'])) ?>" class="wpm-p-row">
      <span class="wpm-p-row__title"><?= wpm_esc($r['title']) ?></span>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
    <h5 style="margin-top:18px;">Terpopuler</h5>
    <?php foreach ($popular as $i => $p): ?>
    <a href="<?= wpm_esc(wpm_article_url($p['slug'])) ?>" class="wpm-p-row">
      <span class="wpm-p-row__rank"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      <span class="wpm-p-row__title"><?= wpm_esc($p['title']) ?></span>
    </a>
    <?php endforeach; ?>
    <?= wpm_render_ad_slot($pdo, 'sidebar-right', 'article', (int) $article['page_id']) ?>
  </aside>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
