/**
 * HackCommand – General UI Interactions
 */
(function() {
    'use strict';

    // Tooltip initialization
    document.addEventListener('DOMContentLoaded', function() {
        var tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltips.forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
    });

    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 5000);
        });
    });

    // Confirm delete actions
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggler = document.querySelector('.navbar-toggler');
        if (toggler) {
            toggler.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            });
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            const sidebar = document.querySelector('.sidebar');
            const toggler = document.querySelector('.navbar-toggler');
            if (sidebar && sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !toggler.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Initialize AOS (Animate On Scroll) for subtle page animations
    document.addEventListener('DOMContentLoaded', function() {
        if (window.AOS) {
            AOS.init({
                once: true,
                duration: 700,
                easing: 'ease-out-cubic',
                offset: 60
            });
        }
    });

    // Count-up animation for elements with data-count
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('[data-count]');
        if (!('IntersectionObserver' in window)) return;

        const ease = function(t) { return 1 - Math.pow(1 - t, 3); };

        const animateCount = function(el) {
            const target = parseFloat(el.getAttribute('data-count')) || 0;
            const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
            const suffix = el.getAttribute('data-suffix') || '';
            const start = 0;
            const duration = 1000;
            const startTime = performance.now();

            const step = function(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const val = start + (target - start) * ease(progress);
                el.textContent = decimals > 0 ? val.toFixed(decimals) + suffix : Math.round(val) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCount(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.6 });

        counters.forEach((el) => observer.observe(el));
    });
})();
