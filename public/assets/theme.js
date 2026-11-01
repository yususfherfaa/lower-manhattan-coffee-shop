/* ═══════════════════════════════════════════════════════════════
   LOWER MANHATTAN COFFEE SHOP — THEME INTERACTIONS (theme.js)
   Animations, reveal, ripple, toast, count-up, scroll-top, etc.
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    // ── REDUCED MOTION CHECK ────────────────────────────────────
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── REVEAL ON SCROLL (IntersectionObserver) ─────────────────
    function initReveal() {
        const els = document.querySelectorAll('.reveal');
        if (!els.length) return;

        if (prefersReduced) {
            els.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var delay = 0;
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var d = parseInt(entry.target.dataset.delay) || delay;
                    setTimeout(function () {
                        entry.target.classList.add('is-visible');
                    }, d);
                    delay += 70;
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

        els.forEach(function (el) { observer.observe(el); });
    }

    // ── RIPPLE EFFECT ON BUTTONS ────────────────────────────────
    function initRipple() {
        if (prefersReduced) return;
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn, .btn-add, .btn-checkout, .btn-submit, .btn-primary, .menu a');
            if (!btn) return;

            var rect = btn.getBoundingClientRect();
            var ripple = document.createElement('span');
            var size = Math.max(rect.width, rect.height);
            ripple.style.cssText =
                'position:absolute;border-radius:50%;background:rgba(255,255,255,.3);' +
                'width:' + size + 'px;height:' + size + 'px;' +
                'left:' + (e.clientX - rect.left - size / 2) + 'px;' +
                'top:' + (e.clientY - rect.top - size / 2) + 'px;' +
                'transform:scale(0);animation:ripple-anim .5s ease-out;pointer-events:none;';
            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });

        // Inject ripple keyframe
        var style = document.createElement('style');
        style.textContent = '@keyframes ripple-anim{to{transform:scale(3);opacity:0}}';
        document.head.appendChild(style);
    }

    // ── SCROLL-TO-TOP BUTTON ────────────────────────────────────
    function initScrollTop() {
        var btn = document.createElement('button');
        btn.className = 'scroll-top';
        btn.setAttribute('aria-label', 'Scroll to top');
        btn.innerHTML = '&#8593;';
        document.body.appendChild(btn);

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, { passive: true });
    }

    // ── DECORATIVE BLOBS ────────────────────────────────────────
    function initBlobs() {
        if (prefersReduced) return;
        // Only add on pages that aren't struk/print
        if (document.body.getAttribute('onload')) return;

        var blob1 = document.createElement('div');
        blob1.className = 'blob-decoration blob-1';
        var blob2 = document.createElement('div');
        blob2.className = 'blob-decoration blob-2';
        document.body.appendChild(blob1);
        document.body.appendChild(blob2);
    }

    // ── TOAST SYSTEM ────────────────────────────────────────────
    window.CoffeeShopToast = {
        container: null,
        init: function () {
            if (this.container) return;
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        },
        show: function (message, type, duration) {
            this.init();
            type = type || 'success';
            duration = duration || 3000;

            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            var icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '⚠';
            toast.innerHTML = '<span style="font-size:18px;">' + icon + '</span><span>' + message + '</span>';
            this.container.appendChild(toast);

            setTimeout(function () {
                toast.classList.add('toast-exit');
                setTimeout(function () { toast.remove(); }, 400);
            }, duration);
        }
    };

    // ── COUNT-UP ANIMATION ──────────────────────────────────────
    function initCountUp() {
        if (prefersReduced) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                observer.unobserve(el);

                var text = el.textContent.trim();
                // Extract number from text like "Rp 1.500.000" or plain "42"
                var match = text.match(/[\d.,]+/);
                if (!match) return;

                var numStr = match[0].replace(/\./g, '').replace(',', '.');
                var target = parseFloat(numStr);
                if (isNaN(target) || target === 0) return;

                var prefix = text.substring(0, text.indexOf(match[0]));
                var suffix = text.substring(text.indexOf(match[0]) + match[0].length);
                var isInt = !match[0].includes(',');
                var duration = 1200;
                var start = performance.now();

                function update(now) {
                    var elapsed = now - start;
                    var progress = Math.min(elapsed / duration, 1);
                    // Ease out quad
                    var eased = 1 - (1 - progress) * (1 - progress);
                    var current = Math.round(target * eased);

                    if (isInt) {
                        el.textContent = prefix + current.toLocaleString('id-ID') + suffix;
                    } else {
                        el.textContent = prefix + (target * eased).toFixed(2).replace('.', ',') + suffix;
                    }

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        el.textContent = text; // Reset to exact original
                    }
                }

                requestAnimationFrame(update);
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.count-up, .stat-card p, .stat-card .stat-value').forEach(function (el) {
            observer.observe(el);
        });
    }

    // ── CONFETTI (for order success page) ───────────────────────
    window.CoffeeShopConfetti = function () {
        if (prefersReduced) return;
        var colors = ['#C47F5E', '#D9A99A', '#D4B896', '#8FA68A', '#D9A441', '#B08F6E'];
        for (var i = 0; i < 40; i++) {
            var piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = Math.random() * 100 + 'vw';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.animationDelay = (Math.random() * 2) + 's';
            piece.style.animationDuration = (2 + Math.random() * 2) + 's';
            piece.style.width = (6 + Math.random() * 6) + 'px';
            piece.style.height = (6 + Math.random() * 6) + 'px';
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            document.body.appendChild(piece);
            (function (p) {
                setTimeout(function () { p.remove(); }, 5000);
            })(piece);
        }
    };

    // ── GREETING BASED ON TIME ──────────────────────────────────
    window.CoffeeShopGreeting = function () {
        var hour = new Date().getHours();
        if (hour >= 5 && hour < 11)  return { text: 'Selamat pagi', emoji: '☀️', icon: 'sunrise' };
        if (hour >= 11 && hour < 15) return { text: 'Selamat siang', emoji: '🌤️', icon: 'sun' };
        if (hour >= 15 && hour < 18) return { text: 'Selamat sore', emoji: '🌅', icon: 'sunset' };
        return { text: 'Selamat malam', emoji: '🌙', icon: 'moon' };
    };

    // ── CART BADGE BOUNCE ───────────────────────────────────────
    window.CoffeeShopCartBounce = function () {
        var badge = document.querySelector('.cart-badge');
        if (!badge || prefersReduced) return;
        badge.style.animation = 'none';
        badge.offsetHeight; // trigger reflow
        badge.style.animation = 'bounce-in .4s cubic-bezier(.22,1,.36,1)';
    };

    // ── INIT ON DOM READY ───────────────────────────────────────
    function init() {
        initReveal();
        initRipple();
        initScrollTop();
        initBlobs();

        // Delay count-up slightly for smooth load
        setTimeout(initCountUp, 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
