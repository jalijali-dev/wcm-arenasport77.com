<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/site-bootstrap.php';

$navCategories = wpm_site_nav_categories();
$headlines = wpm_get_category_headlines($pdo); // slug => article, in nav order
$popular = wpm_get_popular_articles($pdo, 5);

// Filmstrip data: up to 8 latest articles per category (skip categories
// with zero articles entirely, rather than rendering an empty section).
$byCategory = [];
foreach (array_keys($navCategories) as $slug) {
    $rows = wpm_get_articles($pdo, 8, 0, $slug);
    if ($rows) {
        $byCategory[$slug] = $rows;
    }
}

// "Tips" gets its own bottom list section (matches mockup layout) if it
// has articles; otherwise falls back gracefully — index below just checks.
$tipsArticles = wpm_get_articles($pdo, 3, 0, 'tips');

$pageTitle = WPM_SITE_NAME . ' — ' . WPM_SITE_TAGLINE;
$metaDescription = 'Berita dan update olahraga terkini: Bulu Tangkis, Tinju, Moto GP, dan Tips seputar dunia olahraga di ' . WPM_SITE_NAME . '.';
$activeNavSlug = 'home';
require __DIR__ . '/includes/site-header.php';
?>

<?php if ($headlines):
  // array_merge() on two string-keyed arrays would collapse duplicate
  // slugs instead of repeating the list — build an indexed [slug, item]
  // pair list first so the marquee genuinely loops twice.
  $tickerPairs = [];
  foreach (['headlines', 'headlines'] as $_pass) {
      foreach ($headlines as $slug => $item) {
          $tickerPairs[] = [$slug, $item];
      }
  }
?>
<div class="wpm-ticker" aria-label="Berita terbaru berjalan">
  <div class="wpm-ticker__track">
    <?php foreach ($tickerPairs as [$slug, $item]): ?>
      <a href="<?= wpm_esc(wpm_article_url($item['slug'])) ?>"><b><?= wpm_esc($navCategories[$slug] ?? $item['category_name']) ?></b> <?= wpm_esc($item['title']) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($headlines): ?>
<?php
  // Flex-based bento: first headline is the big "main" item, any
  // remaining headlines (0-3 of them, depending how many categories
  // have articles yet) stack in the side column. No fixed 5-slot grid,
  // so there's never a leftover empty cell when fewer than 4 categories
  // have content yet (see site.css .wpm-bento comment for the bug this
  // replaced — flagged by operator 13 Agu 2026).
  $bentoItems = array_values($headlines);
  $bentoSlugs = array_keys($headlines);
  $bentoMain = $bentoItems[0];
  $bentoMainSlug = $bentoSlugs[0];
  $bentoSide = array_slice($bentoItems, 1);
  $bentoSideSlugs = array_slice($bentoSlugs, 1);

  $renderBentoItem = static function (array $item, string $slug, bool $isMain) use ($navCategories) {
      $colorClass = wpm_category_color_class($slug);
      $wrapClass = $isMain ? 'wpm-bento__item wpm-bento__main ' . $colorClass : 'wpm-bento__item ' . $colorClass;
      echo '<a href="' . wpm_esc(wpm_article_url($item['slug'])) . '" class="' . $wrapClass . '">';
      echo '<img src="' . wpm_esc(wpm_image_url($item['featured_image'])) . '" alt="' . wpm_esc($item['title']) . '">';
      echo '<span class="wpm-bento__tag">' . wpm_esc($navCategories[$slug] ?? '') . '</span>';
      echo '<span class="wpm-bento__cap">';
      echo '<span class="wpm-bento__title">' . wpm_esc($item['title']) . '</span>';
      echo '<span class="wpm-bento__meta">' . wpm_esc(wpm_time_ago($item['published_at'])) . '</span>';
      echo '</span></a>';
  };
?>
<div class="wpm-bento">
  <?php // Main item always gets .wpm-bento__main (flex:1.6) — as the sole
        // flex child when there's no side column yet, that still grows
        // to fill 100% of the row's width. ?>
  <?php $renderBentoItem($bentoMain, $bentoMainSlug, true); ?>
  <?php if ($bentoSide): ?>
  <div class="wpm-bento__side">
    <?php foreach ($bentoSide as $i => $item): $renderBentoItem($item, $bentoSideSlugs[$i], false); endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="wpm-empty-state">
  <span class="wpm-empty-state__icon" aria-hidden="true">🏸</span>
  <h2>Belum ada artikel</h2>
  <p>Artikel yang dipublikasikan di kategori Bulu Tangkis, Tinju, Moto GP, atau Tips akan tampil di sini.</p>
</div>
<?php endif; ?>

<?php foreach (['bulu-tangkis', 'tinju', 'moto-gp'] as $slug):
  if (empty($byCategory[$slug])) { continue; }
  $label = $navCategories[$slug];
  $colorClass = wpm_category_color_class($slug);
?>
<div class="wpm-sec-head <?= $colorClass ?>">
  <span class="wpm-chip"><?= wpm_esc($label) ?></span>
  <a class="wpm-more" href="<?= wpm_esc(wpm_category_url($slug)) ?>">Lihat semua &rarr;</a>
</div>
<div class="wpm-filmstrip">
  <?php foreach ($byCategory[$slug] as $article): ?>
  <a href="<?= wpm_esc(wpm_article_url($article['slug'])) ?>" class="wpm-film-card">
    <img src="<?= wpm_esc(wpm_image_url($article['featured_image'])) ?>" alt="<?= wpm_esc($article['title']) ?>">
    <span class="wpm-film-card__body">
      <span class="wpm-film-card__title"><?= wpm_esc($article['title']) ?></span>
      <span class="wpm-film-card__meta"><?= wpm_esc(wpm_time_ago($article['published_at'])) ?></span>
    </span>
  </a>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<div class="wpm-bottom-grid">
  <div>
    <div class="wpm-sec-head c4">
      <span class="wpm-chip">Tips</span>
      <a class="wpm-more" href="<?= wpm_esc(wpm_category_url('tips')) ?>">Lihat semua &rarr;</a>
    </div>
    <?php if ($tipsArticles): ?>
    <div class="wpm-tips-list">
      <?php foreach ($tipsArticles as $article): ?>
      <a href="<?= wpm_esc(wpm_article_url($article['slug'])) ?>" class="wpm-tips-item">
        <img src="<?= wpm_esc(wpm_image_url($article['featured_image'])) ?>" alt="<?= wpm_esc($article['title']) ?>">
        <span class="wpm-tips-item__body">
          <span class="wpm-tips-item__title"><?= wpm_esc($article['title']) ?></span>
          <span class="wpm-tips-item__meta"><?= wpm_esc(wpm_time_ago($article['published_at'])) ?></span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="wpm-sidebar-empty">Belum ada artikel Tips.</p>
    <?php endif; ?>
  </div>

  <div class="wpm-popular-panel">
    <h5>Terpopuler Minggu Ini</h5>
    <?php foreach ($popular as $i => $p): ?>
    <a href="<?= wpm_esc(wpm_article_url($p['slug'])) ?>" class="wpm-p-row">
      <span class="wpm-p-row__rank"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
      <span class="wpm-p-row__title"><?= wpm_esc($p['title']) ?></span>
    </a>
    <?php endforeach; ?>
    <?php if (!$popular): ?><p class="wpm-sidebar-empty">Belum ada data.</p><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
