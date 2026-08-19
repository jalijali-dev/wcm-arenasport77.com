<?php declare(strict_types=1); ?>
</main>

<footer class="wpm-footer">
  <div class="wpm-wrap wpm-footer__grid">
    <div>
      <div class="wpm-footer__brand"><?= wpm_esc(WPM_SITE_NAME) ?></div>
      <p class="wpm-footer__about">Portal berita: Olahraga, Gaya Hidup, Sepak Bola, dan Otomotif.</p>
    </div>
    <div>
      <h4>Kategori</h4>
      <ul>
        <?php foreach (wpm_site_nav_categories() as $navSlug => $navLabel): ?>
        <li><a href="<?= wpm_esc(wpm_category_url($navSlug)) ?>"><?= wpm_esc($navLabel) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h4>Tautan</h4>
      <ul>
        <li><a href="<?= wpm_esc(wpm_base_url('/cari.php')) ?>">Cari Berita</a></li>
        <li><a href="<?= wpm_esc(wpm_base_url('/')) ?>">Beranda</a></li>
      </ul>
    </div>
    <div>
      <h4>Ikuti Kami</h4>
      <ul>
        <li>Facebook</li>
        <li>Twitter / X</li>
        <li>Instagram</li>
      </ul>
    </div>
  </div>
  <div class="wpm-footer__bottom">&copy; <?= date('Y') ?> <?= wpm_esc(WPM_SITE_NAME) ?>. Seluruh hak cipta dilindungi.</div>
</footer>
</body>
</html>
