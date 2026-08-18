<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site-bootstrap.php';

$q = trim((string) ($_GET['q'] ?? ''));
$results = [];
if ($q !== '') {
    $stmt = $pdo->prepare(
        'SELECT p.page_id, p.title, p.slug, p.excerpt, p.featured_image, p.published_at,
                c.name AS category_name
         FROM pages p
         LEFT JOIN article_categories c ON c.id = p.category_id
         WHERE p.status = "published" AND (p.title LIKE :q OR p.excerpt LIKE :q)
         ORDER BY p.published_at DESC LIMIT 20'
    );
    $stmt->execute(['q' => '%' . $q . '%']);
    $results = $stmt->fetchAll();
}

$pageTitle = ($q !== '' ? 'Hasil pencarian: ' . $q : 'Cari') . ' — ' . WPM_SITE_NAME;
require __DIR__ . '/includes/site-header.php';
?>
<h1 class="wpm-page-title"><?= $q !== '' ? 'Hasil untuk &ldquo;' . wpm_esc($q) . '&rdquo;' : 'Cari Berita' ?></h1>

<div class="wpm-listing-main" style="max-width:760px;">
  <?php foreach ($results as $article): ?>
  <a href="<?= wpm_esc(wpm_article_url($article['slug'])) ?>" class="wpm-list-card">
    <img src="<?= wpm_esc(wpm_image_url($article['featured_image'])) ?>" alt="<?= wpm_esc($article['title']) ?>">
    <span class="wpm-list-card__body">
      <span class="wpm-list-card__title"><?= wpm_esc($article['title']) ?></span>
      <?php if (!empty($article['excerpt'])): ?><span class="wpm-list-card__excerpt"><?= wpm_esc($article['excerpt']) ?></span><?php endif; ?>
      <span class="wpm-list-card__meta"><?= wpm_esc(wpm_time_ago($article['published_at'])) ?></span>
    </span>
  </a>
  <?php endforeach; ?>
  <?php if ($q !== '' && !$results): ?><p class="wpm-sidebar-empty">Tidak ada hasil untuk "<?= wpm_esc($q) ?>".</p><?php endif; ?>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
