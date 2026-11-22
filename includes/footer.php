
</main>

<footer class="sound-footer mt-5">

  <div class="footer-glow"></div>

  <div class="container position-relative">

    <!-- Newsletter -->
    <div class="footer-newsletter">
      <div>
        <span class="footer-eyebrow">STAY CONNECTED</span>
        <h3>Feel the music. Live the moment.</h3>
        <p>Discover new music, artists and entertainment with SOUND.</p>
      </div>

      <a href="<?= e(url('music.php')) ?>" class="footer-explore-btn">
        Explore Music <i class="bi bi-arrow-up-right"></i>
      </a>
    </div>

    <div class="footer-divider"></div>

    <!-- Main Footer -->
    <div class="row gy-5 footer-main">

      <!-- Brand -->
      <div class="col-lg-4 col-md-6">

        <a href="<?= e(url('index.php')) ?>" class="footer-brand">
          <span class="footer-brand-icon">
            <i class="bi bi-soundwave"></i>
          </span>
          SOUND<span class="brand-dot">.</span>
        </a>

        <p class="footer-description">
          <?= e(setting('tagline')) ?>
          Your destination for music, videos and discovering
          your favourite artists.
        </p>

        <div class="footer-socials">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
        </div>

      </div>

      <!-- Explore -->
      <div class="col-lg-2 col-6">

        <h6 class="footer-heading">EXPLORE</h6>

        <ul class="footer-links">
          <li><a href="<?= e(url('music.php')) ?>">Music</a></li>
          <li><a href="<?= e(url('videos.php')) ?>">Videos</a></li>
          <li><a href="<?= e(url('browse.php?by=artists')) ?>">Artists</a></li>
          <li><a href="<?= e(url('browse.php?by=albums')) ?>">Albums</a></li>
          <li><a href="<?= e(url('browse.php?by=genres')) ?>">Genres</a></li>
        </ul>

      </div>

      <!-- Company -->
      <div class="col-lg-2 col-6">

        <h6 class="footer-heading">COMPANY</h6>

        <ul class="footer-links">
          <li><a href="<?= e(url('about.php')) ?>">About Us</a></li>
          <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url('index.php')) ?>">Home</a></li>
        </ul>

      </div>

      <!-- Contact -->
      <div class="col-lg-4 col-md-6">

        <h6 class="footer-heading">GET IN TOUCH</h6>

        <div class="footer-contact">
          <span class="contact-icon">
            <i class="bi bi-envelope"></i>
          </span>
          <div>
            <small>Email us</small>
            <a href="mailto:<?= e(setting('contact_email')) ?>">
              <?= e(setting('contact_email')) ?>
            </a>
          </div>
        </div>

        <div class="footer-contact mt-3">
          <span class="contact-icon">
            <i class="bi bi-music-note-beamed"></i>
          </span>
          <div>
            <small>Our platform</small>
            <span>Music • Videos • Entertainment</span>
          </div>
        </div>

      </div>

    </div>

    <div class="footer-divider"></div>

    <!-- Bottom -->
    <div class="footer-bottom">

      <p>
        © <?= date('Y') ?> 
        <?= e(setting('site_name', 'SOUND')) ?>.
        <?= e(setting('footer_text', 'All rights reserved.')) ?>
      </p>

      <div class="footer-bottom-links">
        <span>Made for music lovers</span>
        <i class="bi bi-soundwave"></i>
      </div>

    </div>

  </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
