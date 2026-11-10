/* =========================================================
   FZM Production — stage.js (beranda)
   Tumpukan foto. Foto tampil satu per satu, lalu berkala foto
   paling depan bergeser perlahan dan masuk ke belakang tumpukan.
   Klik foto membuka tampilan besar (ditangani site.js).
   ========================================================= */
(function () {
  'use strict';
  var deck = document.getElementById('deck');
  if (!deck) return;
  var cards = Array.prototype.slice.call(deck.querySelectorAll('.deck-card'));
  var count = document.getElementById('cornerPill');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var n = cards.length;
  var VISIBLE = 4, INTERVAL = 4800, SLIDE_OUT = 820;
  var front = 0, paused = false;
  if (!n) return;

  function slotOf(i) { return (i - front + n) % n; }

  function place(card, slot, shown) {
    var depth = Math.min(slot, VISIBLE);
    card.style.transform = 'translate(' + depth * 3 + '%,' + -depth * 6 + '%) scale(' + (1 - depth * 0.07) + ')';
    card.style.opacity = shown ? String(1 - depth * 0.14) : '0';
    card.style.zIndex = String(50 - slot);
    card.style.pointerEvents = shown ? '' : 'none';
    if (card.tagName === 'A') card.tabIndex = slot === 0 ? 0 : -1;
  }

  function render() {
    cards.forEach(function (card, i) {
      var slot = slotOf(i);
      place(card, slot, slot < VISIBLE);
    });
    if (count) {
      var pad = function (v) { return (v < 10 ? '0' : '') + v; };
      count.textContent = pad(front + 1) + ' — ' + pad(n);
    }
  }

  /* Foto depan meluncur perlahan ke samping, lalu turun ke belakang tumpukan. */
  function step() {
    if (n < 2) return;
    var leaving = cards[front];
    front = (front + 1) % n;
    cards.forEach(function (card, i) {
      if (card !== leaving) place(card, slotOf(i), slotOf(i) < VISIBLE);
    });
    if (count) {
      count.textContent = (front < 9 ? '0' : '') + (front + 1) + ' — ' + (n < 10 ? '0' : '') + n;
    }
    leaving.style.zIndex = '60';
    leaving.style.pointerEvents = 'none';
    leaving.classList.add('is-leaving');
    leaving.style.transform = 'translate(-22%,3%) rotate(-1.5deg) scale(.98)';
    setTimeout(function () {
      var slot = slotOf(cards.indexOf(leaving));
      leaving.classList.remove('is-leaving');
      place(leaving, slot, slot < VISIBLE);
      leaving.style.zIndex = '1';
    }, SLIDE_OUT);
  }

  function intro() {
    render();
    if (reduce) return;
    /* Tampil satu per satu, dari yang paling belakang. */
    var order = cards.map(function (_, i) { return i; })
      .filter(function (i) { return slotOf(i) < VISIBLE; })
      .sort(function (a, b) { return slotOf(b) - slotOf(a); });
    cards.forEach(function (card) { card.style.opacity = '0'; });
    order.forEach(function (i, k) {
      setTimeout(function () { place(cards[i], slotOf(i), true); }, 200 + k * 340);
    });
    setTimeout(function () {
      setInterval(function () {
        var viewerOpen = document.body.classList.contains('no-scroll');
        if (!paused && !viewerOpen && !document.hidden) step();
      }, INTERVAL);
    }, 200 + order.length * 340);
  }

  deck.addEventListener('pointerenter', function () { paused = true; });
  deck.addEventListener('pointerleave', function () { paused = false; });
  deck.addEventListener('focusin', function () { paused = true; });
  deck.addEventListener('focusout', function () { paused = false; });

  intro();
})();
