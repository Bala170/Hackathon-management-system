/**
 * HackCommand – Announcements AJAX Polling
 * Polls server every 8 seconds for new announcements
 */
(function() {
    const BASE_URL = document.querySelector('meta[name="base-url"]')?.content ||
                     window.location.pathname.split('/').slice(0, 2).join('/');

    let lastCount = 0;

    function fetchUnreadCount() {
        $.ajax({
            url: BASE_URL + '/ajax/fetch_announcements.php',
            method: 'GET',
            data: { count_only: 1 },
            dataType: 'json',
            success: function(data) {
                if (data && typeof data.unread_count !== 'undefined') {
                    updateBadge(data.unread_count);
                }
            },
            error: function() {
                // Silently fail — will retry on next interval
            }
        });
    }

    function updateBadge(count) {
        const badge = document.getElementById('notifBadge');
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
                badge.style.display = 'inline-flex';
            } else {
                // Create badge if it doesn't exist
                const bellLink = document.querySelector('.bi-bell-fill')?.parentElement;
                if (bellLink) {
                    const newBadge = document.createElement('span');
                    newBadge.id = 'notifBadge';
                    newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge';
                    newBadge.textContent = count;
                    bellLink.appendChild(newBadge);
                }
            }

            // Flash tab title if new announcements
            if (count > lastCount && lastCount > 0) {
                flashTitle(count);
            }
        } else if (badge) {
            badge.style.display = 'none';
        }
        lastCount = count;
    }

    function flashTitle(count) {
        const original = document.title;
        const flash = '(' + count + ') New Announcement!';
        let toggled = false;
        const interval = setInterval(function() {
            document.title = toggled ? original : flash;
            toggled = !toggled;
        }, 1000);
        setTimeout(function() {
            clearInterval(interval);
            document.title = original;
        }, 6000);
    }

    // Start polling
    if (typeof $ !== 'undefined') {
        fetchUnreadCount();
        setInterval(fetchUnreadCount, 8000);
    }
})();
