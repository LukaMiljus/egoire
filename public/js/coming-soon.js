/* ============================================================
   Egoire – Coming Soon Countdown
   Script: public/js/coming-soon.js
   ============================================================ */

(function () {
    'use strict';

    // ========================================================
    // ★  SET YOUR OPENING DATE HERE  ★
    // Format: 'YYYY-MM-DDTHH:MM:SS' (local time)
    // ========================================================
    const OPENING_DATE = '2026-06-01T12:00:00';
    // ========================================================

    // DOM refs
    const daysEl    = document.getElementById('cs-days');
    const hoursEl   = document.getElementById('cs-hours');
    const minutesEl = document.getElementById('cs-minutes');
    const secondsEl = document.getElementById('cs-seconds');

    const countdownWrap = document.getElementById('cs-countdown');
    const headingEl     = document.getElementById('cs-heading');
    const dividerEl     = document.getElementById('cs-divider');
    const subtextEl     = document.getElementById('cs-subtext');
    const openMessage   = document.getElementById('cs-open-message');
    const loader        = document.getElementById('cs-loader');

    const target = new Date(OPENING_DATE).getTime();

    /**
     * Pad a number to 2 digits.
     */
    function pad(n) {
        return String(n).padStart(2, '0');
    }

    /**
     * Update a single counter element with flip animation.
     */
    function updateDigit(el, value) {
        var formatted = pad(value);
        if (el.textContent !== formatted) {
            el.style.transform = 'scale(1.08)';
            el.textContent = formatted;
            setTimeout(function () {
                el.style.transform = 'scale(1)';
            }, 200);
        }
    }

    /**
     * Core tick – runs every second.
     */
    function tick() {
        var now  = Date.now();
        var diff = target - now;

        if (diff <= 0) {
            // Countdown finished – show "open" message
            countdownWrap.style.display = 'none';
            headingEl.style.display     = 'none';
            dividerEl.style.display     = 'none';
            subtextEl.style.display     = 'none';
            openMessage.classList.add('visible');
            return; // stop ticking
        }

        var totalSec = Math.floor(diff / 1000);
        var days     = Math.floor(totalSec / 86400);
        var hours    = Math.floor((totalSec % 86400) / 3600);
        var minutes  = Math.floor((totalSec % 3600) / 60);
        var seconds  = totalSec % 60;

        updateDigit(daysEl, days);
        updateDigit(hoursEl, hours);
        updateDigit(minutesEl, minutes);
        updateDigit(secondsEl, seconds);

        requestAnimationFrame(function () {
            setTimeout(tick, 1000 - (Date.now() % 1000));
        });
    }

    // Start countdown
    tick();

    // Dismiss loader after page loads
    window.addEventListener('load', function () {
        setTimeout(function () {
            if (loader) loader.classList.add('hidden');
        }, 400);
    });
})();
