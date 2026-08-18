<?php declare(strict_types=1); ?>
    </div>
  </main>
</div>

<footer class="wpm-footer">
  <div class="wpm-wrap wpm-footer__grid">
    <div>
      <div class="wpm-footer__brand">Biang<span>Olahraga</span></div>
      <p class="wpm-footer__about">Portal olahraga multi-cabang: Bulu Tangkis, Tinju, Moto GP, dan Tips seputar dunia olahraga.</p>
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
  <div class="wpm-footer__bottom">&copy; <?= date('Y') ?> Biang Olahraga. Seluruh hak cipta dilindungi.</div>
</footer>
</body>
</html>
