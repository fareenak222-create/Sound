(function () {
  // Scroll-reveal animation
  var els = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (en) { en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { threshold: .08 });
    els.forEach(function (el, i) { el.style.transitionDelay = (i % 6) * 60 + 'ms'; io.observe(el); });
  } else els.forEach(function (el) { el.classList.add('in'); });
  // Confirm before destructive actions
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-confirm]');
    if (t && !window.confirm(t.getAttribute('data-confirm'))) e.preventDefault();
  });
  // Pause other media when one starts
  document.addEventListener('play', function (e) {
    document.querySelectorAll('audio,video').forEach(function (m) { if (m !== e.target) m.pause(); });
  }, true);
})();
