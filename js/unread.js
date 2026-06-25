/**
 * Unread Tracker Frontend Logic
 * Badge polling, mark-as-read on ticket open, visual indicators in lists
 */

// Resolve plugin base URL from the script tag src (handles any GLPI page depth)
const _unreadPluginBase = (function() {
    const s = document.querySelector('script[src*="unreadtracker/js/unread.js"]');
    return s ? s.src.replace(/js\/unread\.js.*$/, '') : '/plugins/unreadtracker/';
})();

// Poll get_count.php and update badge in navbar
function updateUnreadBadge() {
    fetch(_unreadPluginBase + 'ajax/get_count.php')
        .then(r => r.json())
        .then(data => {
            const count = data.count || 0;
            let badge = document.getElementById('unread-count-badge');

            // Find Tickets link in navbar
            const ticketLinks = document.querySelectorAll('a[href*="ticket.php"], a[href*="Ticket"]');

            if (ticketLinks.length && count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.id = 'unread-count-badge';
                    badge.className = 'unread-badge';
                    ticketLinks[0].appendChild(badge);
                }
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
}

// Mark current ticket as read when viewing ticket detail page
function markCurrentTicketAsRead() {
    const match = window.location.href.match(/ticket\.form\.php\?.*id=(\d+)/);
    if (!match) return;

    const ticketsId = parseInt(match[1]);
    if (!ticketsId) return;

    const csrfInput = document.querySelector('input[name=_glpi_csrf_token]');
    if (!csrfInput) return;

    const formData = new FormData();
    formData.append('tickets_id', ticketsId);
    formData.append('_glpi_csrf_token', csrfInput.value);

    fetch(_unreadPluginBase + 'ajax/mark_read.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) updateUnreadBadge();
        })
        .catch(() => {});
}

// Apply .unread-row class to unread tickets in list view
function markUnreadRows() {
    if (!window.location.href.match(/ticket\.php($|\?[^f])/)) return;

    fetch(_unreadPluginBase + 'ajax/get_unread_ids.php')
        .then(r => r.json())
        .then(data => {
            const ids = new Set(data.ids || []);
            document.querySelectorAll('tr[data-itemtype="Ticket"][data-id]').forEach(row => {
                if (ids.has(parseInt(row.dataset.id))) {
                    row.classList.add('unread-row');
                }
            });
        })
        .catch(() => {});
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadBadge();
    markCurrentTicketAsRead();
    markUnreadRows();
    setInterval(updateUnreadBadge, 60000);
});
