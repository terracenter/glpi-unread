/**
 * Unread Tracker Frontend Logic
 * Dynamic Odoo-like notification icon, color-priority badge, and interactive popover with category filtering.
 */

// Resolve plugin base URL from the script tag src (handles any GLPI page depth)
const _unreadPluginBase = (function() {
    const s = document.querySelector('script[src*="unreadtracker/js/unread.js"]');
    return s ? s.src.replace(/js\/unread\.js.*$/, '') : '/plugins/unreadtracker/';
})();

// Global state variables to store loaded data
window._unreadTickets = [];
window._unreadStats = { total: 0, new: 0, updated: 0, overdue: 0 };
window._unreadCurrentFilter = 'all';

// Helper to map GLPI priority (1-6) to colors/names
function getPriorityInfo(priority) {
    switch(parseInt(priority)) {
        case 6: return { class: 'bg-priority-6', name: 'Mayor' };
        case 5: return { class: 'bg-priority-5', name: 'Muy Alta' };
        case 4: return { class: 'bg-priority-4', name: 'Alta' };
        case 3: return { class: 'bg-priority-3', name: 'Media' };
        case 2: return { class: 'bg-priority-2', name: 'Baja' };
        default: return { class: 'bg-priority-1', name: 'Muy Baja' };
    }
}

// Format relative time
function formatTime(dateStr) {
    if (!dateStr) return '';
    try {
        const t = new Date(dateStr.replace(' ', 'T'));
        const now = new Date();
        const diffMs = now - t;
        const diffMins = Math.round(diffMs / 60000);
        const diffHrs = Math.round(diffMs / 3600000);

        if (diffMins < 1) return 'Ahora';
        if (diffMins < 60) return `${diffMins}m`;
        if (diffHrs < 24) return `${diffHrs}h`;
        
        return t.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit' });
    } catch(e) {
        return dateStr;
    }
}

// Build and inject the Odoo-like Notification Menu
function injectNotificationMenu() {
    if (document.getElementById('unread-notification-li')) return;

    // Locate header navbar container (standard Tabler UI header in GLPI 10)
    const headerNav = document.querySelector('header.navbar .navbar-nav.flex-row') || 
                      document.querySelector('header.navbar .navbar-nav') || 
                      document.querySelector('.header .navbar-nav') ||
                      document.querySelector('header.navbar');

    if (!headerNav) return;

    const dropdownLi = document.createElement('li');
    dropdownLi.id = 'unread-notification-li';
    dropdownLi.className = 'nav-item dropdown d-flex align-items-center me-2';

    dropdownLi.innerHTML = `
        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" title="Notificaciones de Tickets" aria-haspopup="true" aria-expanded="false" style="position: relative; padding: 8px;">
            <i class="ti ti-bell" style="font-size: 1.25rem; color: #495057;"></i>
            <span id="unread-count-badge" class="unread-badge" style="display: none;">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end unread-notification-dropdown">
            <div class="unread-notification-header d-flex justify-content-between align-items-center">
                <span>Tickets Pendientes</span>
                <span class="badge bg-secondary-lt" id="unread-dropdown-count-header">0</span>
            </div>
            
            <div class="unread-notification-filters">
                <button class="unread-notification-filter-btn active" data-filter="all">
                    <i class="ti ti-folder me-1"></i> Todos <span class="badge bg-secondary" id="filter-count-all">0</span>
                </button>
                <button class="unread-notification-filter-btn" data-filter="new">
                    <i class="ti ti-star me-1"></i> Nuevos <span class="badge bg-secondary" id="filter-count-new">0</span>
                </button>
                <button class="unread-notification-filter-btn" data-filter="updated">
                    <i class="ti ti-refresh me-1"></i> Act. <span class="badge bg-secondary" id="filter-count-updated">0</span>
                </button>
                <button class="unread-notification-filter-btn" data-filter="overdue">
                    <i class="ti ti-alarm me-1"></i> Retr. <span class="badge bg-secondary" id="filter-count-overdue">0</span>
                </button>
            </div>

            <div class="unread-notification-list" id="unread-tickets-list-container">
                <!-- Se pobla dinámicamente -->
            </div>
            <div class="unread-notification-footer">
                <a href="${_unreadPluginBase}../../front/ticket.php">Ver bandeja de entrada</a>
            </div>
        </div>
    `;

    // Insert next to user profile dropdown
    const profileItem = headerNav.querySelector('li.nav-item.dropdown:last-child') || 
                        headerNav.querySelector('.nav-item:last-child');
    if (profileItem) {
        profileItem.before(dropdownLi);
    } else {
        headerNav.appendChild(dropdownLi);
    }

    // Add filter buttons click events
    dropdownLi.querySelectorAll('.unread-notification-filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Evitar que cierre el dropdown al hacer click en filtros
            
            dropdownLi.querySelectorAll('.unread-notification-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            window._unreadCurrentFilter = filter;
            renderFilteredTickets(filter);
        });
    });

    // Populate when dropdown is shown
    dropdownLi.addEventListener('show.bs.dropdown', function() {
        renderFilteredTickets(window._unreadCurrentFilter);
    });
}

// Update the main badge count and colors based on highest priority
function updateUnreadBadge() {
    fetch(_unreadPluginBase + 'ajax/get_unread_details.php')
        .then(r => r.json())
        .then(data => {
            const tickets = data.tickets || [];
            const stats = data.stats || { total: 0, new: 0, updated: 0, overdue: 0 };
            
            window._unreadTickets = tickets;
            window._unreadStats = stats;

            const badge = document.getElementById('unread-count-badge');
            if (!badge) return;

            const count = stats.total || 0;

            if (count > 0) {
                // Determine highest priority
                let maxPriority = 1;
                tickets.forEach(t => {
                    if (t.priority > maxPriority) maxPriority = t.priority;
                });

                // Clear previous classes
                badge.className = 'unread-badge';
                
                const pInfo = getPriorityInfo(maxPriority);
                badge.classList.add(pInfo.class);
                
                // Pulse if it has major or very high priorities
                if (maxPriority >= 5) {
                    badge.classList.add('unread-badge-pulse');
                }

                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }

            // Update stats indicators inside dropdown UI if it is present
            const headerCount = document.getElementById('unread-dropdown-count-header');
            if (headerCount) headerCount.textContent = count;

            const fAll = document.getElementById('filter-count-all');
            const fNew = document.getElementById('filter-count-new');
            const fUpd = document.getElementById('filter-count-updated');
            const fOvd = document.getElementById('filter-count-overdue');

            if (fAll) fAll.textContent = stats.total;
            if (fNew) fNew.textContent = stats.new;
            if (fUpd) fUpd.textContent = stats.updated;
            if (fOvd) fOvd.textContent = stats.overdue;
        })
        .catch(() => {});
}

// Render filtered tickets list in dropdown
function renderFilteredTickets(filter) {
    const listContainer = document.getElementById('unread-tickets-list-container');
    if (!listContainer) return;

    listContainer.innerHTML = '';

    // Filter tickets array
    let filtered = window._unreadTickets;
    if (filter === 'new') {
        filtered = window._unreadTickets.filter(t => t.is_new);
    } else if (filter === 'updated') {
        filtered = window._unreadTickets.filter(t => t.is_updated);
    } else if (filter === 'overdue') {
        filtered = window._unreadTickets.filter(t => t.is_overdue);
    }

    if (filtered.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="ti ti-circle-check text-success fs-2 d-block mb-1"></i>
                Sin tickets pendientes en esta sección
            </div>
        `;
        return;
    }

    // Limit to 10 tickets for readability
    const displayList = filtered.slice(0, 10);

    displayList.forEach(ticket => {
        const pInfo = getPriorityInfo(ticket.priority);
        const item = document.createElement('a');
        item.href = `${_unreadPluginBase}../../front/ticket.form.php?id=${ticket.id}`;
        item.className = 'unread-notification-item';

        // Determinar ícono de estado
        let statusIcon = '<i class="ti ti-ticket"></i>';
        let extraMeta = '';

        if (ticket.is_overdue) {
            statusIcon = '<i class="ti ti-alert-triangle text-danger"></i>';
            extraMeta = '<span class="unread-notification-overdue-warning me-2"><i class="ti ti-alarm me-1"></i>Retrasado</span>';
        } else if (ticket.is_new) {
            statusIcon = '<i class="ti ti-star text-success"></i>';
        } else if (ticket.is_updated) {
            statusIcon = '<i class="ti ti-refresh text-warning"></i>';
        }

        item.innerHTML = `
            <div class="unread-notification-icon-wrapper">
                ${statusIcon}
            </div>
            <div class="unread-notification-content">
                <div class="unread-notification-title" title="${ticket.name}">
                    <span class="unread-dot ${pInfo.class}" title="Prioridad: ${pInfo.name}"></span>
                    ${ticket.name || 'Sin título'}
                </div>
                <div class="unread-notification-meta">
                    <div>
                        ${extraMeta}
                        <span>ID: #${ticket.id}</span>
                    </div>
                    <span>${formatTime(ticket.date_mod)}</span>
                </div>
            </div>
        `;

        listContainer.appendChild(item);
    });

    // Si hay más de 10 tickets, mostrar aviso
    if (filtered.length > 10) {
        const moreItem = document.createElement('div');
        moreItem.className = 'text-center py-2 text-muted border-top bg-light';
        moreItem.style.fontSize = '11px';
        moreItem.textContent = `Y ${filtered.length - 10} tickets más pendientes...`;
        listContainer.appendChild(moreItem);
    }
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
            if (data.success) {
                updateUnreadBadge();
            }
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
    injectNotificationMenu();
    updateUnreadBadge();
    markCurrentTicketAsRead();
    markUnreadRows();
    
    // Poll for updates every 60s
    setInterval(updateUnreadBadge, 60000);
});
