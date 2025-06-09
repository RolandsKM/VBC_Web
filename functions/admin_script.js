const perPage = 5;
let currentSortField = 'created_at';
let currentSortOrder = 'DESC';
let currentPage = 1;
let currentUserSortField = 'created_at';
let currentUserSortOrder = 'DESC';
let currentAdminSortField = 'created_at';
let currentAdminSortOrder = 'DESC';
let currentModSortField = 'created_at';
let currentModSortOrder = 'DESC';
let searchTimeout;
let eventSearchTimeout;


const filterPeriodSelect = document.getElementById('filter-period');
const totalUsersSpan = document.getElementById('total-users');
const bannedUsersSpan = document.getElementById('banned-users');
const bannedActiveCtx = document.getElementById('bannedActiveChart')?.getContext('2d');
const newUsersCtx = document.getElementById('newUsersChart')?.getContext('2d');
const eventsByDayCtx = document.getElementById('eventsByDayChart')?.getContext('2d');


function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Chart 
let bannedActiveChart = bannedActiveCtx ? new Chart(bannedActiveCtx, {
    type: 'doughnut',
    data: {
        labels: ['Aktīvi lietotāji', 'Bloķēti lietotāji'],
        datasets: [{
            label: 'Lietotāji',
            data: [0, 0],
            backgroundColor: ['#0d6efd', '#dc3545'],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            },
            datalabels: {
                color: '#fff',
                formatter: (value, context) => {
                    let total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    let percentage = total ? (value / total * 100).toFixed(1) : 0;
                    return `${value}\n(${percentage}%)`;
                },
                font: {
                    weight: 'bold',
                    size: 14,
                },
                anchor: 'center',
                align: 'center',
            }
        }
    },
    plugins: [ChartDataLabels],
}) : null;

let newUsersChart = newUsersCtx ? new Chart(newUsersCtx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Jaunie lietotāji',
            data: [],
            backgroundColor: '#198754',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { 
                ticks: { 
                    maxRotation: 90, 
                    minRotation: 45,
                    font: {
                        size: 12
                    }
                }
            },
            y: { 
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 12
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
}) : null;

let eventsByDayChart = eventsByDayCtx ? new Chart(eventsByDayCtx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            {
                label: 'Pasākumi dienā',
                data: [],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            },
            {
                label: 'Pasākumi nedēļā',
                data: [],
                borderColor: 'rgba(28, 200, 138, 1)',
                backgroundColor: 'rgba(28, 200, 138, 0.2)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            },
            {
                label: 'Pasākumi mēnesī',
                data: [],
                borderColor: 'rgba(231, 74, 59, 1)',
                backgroundColor: 'rgba(231, 74, 59, 0.2)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            },
            x: {
                type: 'category',
                ticks: {
                    maxRotation: 45,
                    minRotation: 45,
                    autoSkip: true,
                    maxTicksLimit: 20
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y}`;
                    }
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
}) : null;

// User Table 
function renderUsers(tableId, users) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Nav lietotāju.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr>
            <td>
                <img src="${user.profile_pic ? '../functions/assets/' + user.profile_pic : '../functions/assets/default-profile.png'}" 
                     alt="${user.username}'s profile" 
                     class="rounded-circle"
                     style="width: 40px; height: 40px; object-fit: cover;">
            </td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <span class="badge ${user.banned ? 'bg-danger' : 'bg-success'}">
                    ${user.banned ? 'Bloķēts' : 'Aktīvs'}
                </span>
            </td>
            <td class="text-center">
                <a href="user-details.php?id=${user.ID_user}" class="btn btn-sm btn-info me-1">
                    <i class="fas fa-eye"></i>
                </a>

            </td>
        </tr>
    `).join('');

    // sorting
    const table = tbody.closest('table');
    if (table) {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index < 4 && !header.hasAttribute('data-sort-initialized')) {
                header.style.cursor = 'pointer';
                header.setAttribute('data-sort-initialized', 'true');
                header.addEventListener('click', () => {
                    const currentOrder = header.getAttribute('data-order') || 'desc';
                    const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
                    
                    header.setAttribute('data-order', newOrder);
                    const headerText = header.textContent.replace(/[↑↓]/, '').trim();
                    header.innerHTML = headerText + (newOrder === 'asc' ? ' ↑' : ' ↓');
                    
                    let sortField;
                    switch(index) {
                        case 0: sortField = 'username'; break;
                        case 1: sortField = 'email'; break;
                        case 2: sortField = 'created_at'; break;
                        case 3: sortField = 'banned'; break;
                        default: sortField = 'created_at';
                    }
                    
                    currentUserSortField = sortField;
                    currentUserSortOrder = newOrder;
                    
                    const tableType = tableId.includes('todays') ? 'todays' : 'all';
                    fetchUsers(tableType, currentPage, filterPeriodSelect?.value || 'all', sortField, newOrder);
                });
            }
        });
    }
}

function renderPagination(containerId, currentPage, totalPages, tableType) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '';
    
    // Previous button
    html += `
        <button class="pagination-btn" data-page="${currentPage - 1}" data-table="${tableType}" 
                ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || // First page
            i === totalPages || // Last page
            (i >= currentPage - 1 && i <= currentPage + 1) // Pages around current
        ) {
            html += `
                <button class="pagination-btn ${i === currentPage ? 'active' : ''}" 
                        data-page="${i}" data-table="${tableType}"
                        ${i === currentPage ? 'disabled' : ''}>
                    ${i}
                </button>
            `;
        } else if (
            i === currentPage - 2 || // Before current page range
            i === currentPage + 2 // After current page range
        ) {
            html += '<span class="pagination-ellipsis">...</span>';
        }
    }

    // Next button
    html += `
        <button class="pagination-btn" data-page="${currentPage + 1}" data-table="${tableType}"
                ${currentPage === totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;

    container.innerHTML = html;

    // Add click handlers
    container.querySelectorAll('.pagination-btn:not(:disabled)').forEach(button => {
        button.addEventListener('click', () => {
            const page = parseInt(button.dataset.page);
            const tableType = button.dataset.table;
            
            if (tableType === 'mod' || tableType === 'admin') {
                const sortHeader = document.querySelector(`#${tableType}-body`).closest('table').querySelector('th[data-order]');
                const sortField = sortHeader ? sortHeader.dataset.sort : 'created_at';
                const sortOrder = sortHeader ? sortHeader.dataset.order : 'DESC';
                fetchAdminModUsers(tableType, page, sortField, sortOrder);
            }
        });
    });
}


function fetchUsers(table, page, period = 'all', sortField = currentUserSortField, sortOrder = currentUserSortOrder, search = '') {
    currentPage = page;
    currentUserSortField = sortField;
    currentUserSortOrder = sortOrder;
    
    let url = `user_manager.php?ajax=1&table=${table}&page=${page}&sort=${sortField}&order=${sortOrder}`;
    if (table === 'all') {
        url += `&period=${period}`;
        if (search) {
            url += `&search=${encodeURIComponent(search)}`;
        }
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }
            
            const totalPages = Math.ceil(data.total / data.perPage);
            
            if (table === 'todays') {
                renderUsers('todays-users-body', data.users);
                renderPagination('todays-users-pagination', data.page, totalPages, 'todays');
            } else if (table === 'all') {
                renderUsers('all-users-body', data.users);
                renderPagination('all-users-pagination', data.page, totalPages, 'all');
                
                if (totalUsersSpan) totalUsersSpan.textContent = data.total;
                if (bannedUsersSpan) {
                    fetch(`user_manager.php?ajax=1&table=all&count=banned&period=${period}`)
                        .then(resp => resp.json())
                        .then(countData => {
                            if (countData.success) {
                                bannedUsersSpan.textContent = countData.bannedCount ?? '0';
                            }
                        });
                }
            }
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}

function updateCharts(period) {
    if (!bannedActiveChart || !newUsersChart) return;

    fetch(`user_manager.php?ajax=1&chart=bannedActive&period=${period}`)
        .then(resp => resp.json())
        .then(data => {
            if (data.success) {
                bannedActiveChart.data.datasets[0].data = [data.active, data.banned];
                bannedActiveChart.update();
            }
        });

    fetch(`user_manager.php?ajax=1&chart=newUsers&period=${period}`)
        .then(resp => resp.json())
        .then(data => {
            if (data.success) {
                newUsersChart.data.labels = data.data.labels;
                newUsersChart.data.datasets[0].data = data.data.counts;
                newUsersChart.update();
            }
        });
}

// Event Table 
function renderEvents(events, tableId) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">Nav sludinājumu.</td></tr>';
        return;
    }

    const startNumber = (currentPage - 1) * perPage + 1;

    tbody.innerHTML = events.map((event, index) => `
        <tr class="${event.deleted ? 'table-danger' : ''}">
            <td>${startNumber + index}</td>
            <td>${escapeHtml(event.title)}</td>
            <td>${escapeHtml(event.name + ' ' + event.surname + ' (' + event.username + ')')}</td>
            <td>
                <span class="status-badge ${event.deleted ? 'status-deleted' : 'status-active'}">
                    ${event.deleted ? 'Jā' : 'Nē'}
                </span>
            </td>
            <td>${formatDate(event.created_at)}</td>
            <td>
                <a href="event_details.php?id=${event.ID_Event}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Apskatīt
                </a>
            </td>
        </tr>
    `).join('');

    // sorting
    const table = tbody.closest('table');
    if (table) {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index < 5 && !header.hasAttribute('data-sort-initialized')) {
                header.style.cursor = 'pointer';
                header.setAttribute('data-sort-initialized', 'true');
                header.addEventListener('click', () => {
                    const currentOrder = header.getAttribute('data-order') || 'desc';
                    const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
                    
                    header.setAttribute('data-order', newOrder);
                    const headerText = header.textContent.replace(/[↑↓]/, '').trim();
                    header.innerHTML = headerText + (newOrder === 'asc' ? ' ↑' : ' ↓');
                    
                    let sortField;
                    switch(index) {
                        case 0: sortField = 'ID_Event'; break;
                        case 1: sortField = 'title'; break;
                        case 2: sortField = 'username'; break;
                        case 3: sortField = 'deleted'; break;
                        case 4: sortField = 'created_at'; break;
                        default: sortField = 'created_at';
                    }
                    
                    currentSortField = sortField;
                    currentSortOrder = newOrder;
                    
                    const tableType = tableId === 'todays-events-body' ? 'todays' : 'all';
                    fetchEvents(currentPage, sortField, newOrder, tableType);
                });
            }
        });
    }
}

function fetchEvents(page = 1, sortField = currentSortField, sortOrder = currentSortOrder, table = 'all', search = '') {
    currentPage = page;
    currentSortField = sortField;
    currentSortOrder = sortOrder;
    
    let url = `event_manager.php?ajax=1&page=${page}&sort=${sortField}&order=${sortOrder}&table=${table}`;
    if (table === 'all' && search) {
        url += `&search=${encodeURIComponent(search)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }
            const totalPages = Math.ceil(data.total / data.perPage);
            const tableId = table === 'todays' ? 'todays-events-body' : 'events-body';
            const paginationId = table === 'todays' ? 'todays-events-pagination' : 'events-pagination';
            const tableType = table === 'todays' ? 'events-todays' : 'events-all';
            renderEvents(data.events, tableId);
            renderPagination(paginationId, data.page, totalPages, tableType);
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}

// Export 
function exportTableToCSV(filename) {
    let csv = [];
    const table = document.querySelector('.table');
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            let data = col.innerText.replace(/"/g, '""');
            if (data.search(/("|,|\n)/g) >= 0) data = `"${data}"`;
            rowData.push(data);
        });
        csv.push(rowData.join(','));
    });

    const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}


if (filterPeriodSelect) {
    filterPeriodSelect.addEventListener('change', () => {
        fetchUsers('all', 1, filterPeriodSelect.value);
        updateCharts(filterPeriodSelect.value);
    });
}

document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('pagination-btn')) {
        const page = parseInt(e.target.getAttribute('data-page'));
        const table = e.target.getAttribute('data-table');
        if (!isNaN(page)) {
            if (table === 'todays' || table === 'all') {
                fetchUsers(table, page, filterPeriodSelect?.value || 'all');
            } else if (table === 'events-todays' || table === 'events-all') {
                const tableType = table === 'events-todays' ? 'todays' : 'all';
                fetchEvents(page, currentSortField, currentSortOrder, tableType);
            } else {
                fetchEvents(page, currentSortField, currentSortOrder);
            }
        }
    }
});

// Export
document.querySelectorAll('.dropdown-item').forEach(item => {
    if (item.textContent.trim() === 'Eksportēt uz CSV') {
        item.addEventListener('click', () => {
            exportTableToCSV('events.csv');
        });
    }
    if (item.textContent.trim() === 'Drukāt') {
        item.addEventListener('click', () => {
            window.print();
        });
    }
});

// Event Statistics
function updateEventStatistics() {
    if (!eventsByDayChart) return;

    // daily statistics
    fetch('event_manager.php?ajax=1&stats=daily')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dailyData = data.data;
                eventsByDayChart.data.labels = dailyData.map(item => item.day);
                eventsByDayChart.data.datasets[0].data = dailyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching daily stats:', err));

    // weekly statistics
    fetch('event_manager.php?ajax=1&stats=weekly')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const weeklyData = data.data;
                eventsByDayChart.data.datasets[1].data = weeklyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching weekly stats:', err));

    // monthly statistics
    fetch('event_manager.php?ajax=1&stats=monthly')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const monthlyData = data.data;
                eventsByDayChart.data.datasets[2].data = monthlyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching monthly stats:', err));
}


if (document.getElementById('events-body')) {
    fetchEvents(1, currentSortField, currentSortOrder, 'all');
    fetchEvents(1, currentSortField, currentSortOrder, 'todays');
    updateEventStatistics();
}

if (document.getElementById('todays-users-body')) {
    fetchUsers('todays', 1);
    fetchUsers('all', 1, filterPeriodSelect?.value || 'all');
    updateCharts(filterPeriodSelect?.value || 'all');
}

// Admin/Mod Functions
function renderAdminModUsers(tableId, users) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Nav lietotāju.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr class="${user.is_blocked ? 'blocked-user' : ''}">
            <td>${user.ID_user}</td>
            <td>
                ${escapeHtml(user.username)}
                ${user.is_blocked ? '<span class="blocked-badge">Bloķēts</span>' : ''}
            </td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.surname)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${formatDate(user.created_at)}</td>
            <td class="text-center">
                <a href="admin-details.php?id=${user.ID_user}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                </a>
                <button onclick="deleteUser(${user.ID_user})" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    //  click handler for sorting
    const table = tbody.closest('table');
    if (table) {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index < 6 && !header.hasAttribute('data-sort-initialized')) {
                header.style.cursor = 'pointer';
                header.setAttribute('data-sort-initialized', 'true');
                header.addEventListener('click', () => {
                    const currentOrder = header.getAttribute('data-order') || 'desc';
                    const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
                    
                    header.setAttribute('data-order', newOrder);
                    const headerText = header.textContent.replace(/[↑↓]/, '').trim();
                    header.innerHTML = headerText + (newOrder === 'asc' ? ' ↑' : ' ↓');
                    
                    let sortField;
                    switch(index) {
                        case 0: sortField = 'ID_user'; break;
                        case 1: sortField = 'username'; break;
                        case 2: sortField = 'name'; break;
                        case 3: sortField = 'surname'; break;
                        case 4: sortField = 'email'; break;
                        case 5: sortField = 'created_at'; break;
                        default: sortField = 'created_at';
                    }
                    
                    const role = tableId === 'mod-body' ? 'mod' : 'admin';
                    if (role === 'mod') {
                        currentModSortField = sortField;
                        currentModSortOrder = newOrder;
                    } else {
                        currentAdminSortField = sortField;
                        currentAdminSortOrder = newOrder;
                    }
                    
                    // Get current page from pagination
                    const paginationId = role === 'mod' ? 'mod-pagination' : 'admin-pagination';
                    const paginationContainer = document.getElementById(paginationId);
                    const currentPageButton = paginationContainer?.querySelector('.pagination-btn:disabled');
                    const currentPage = currentPageButton ? parseInt(currentPageButton.dataset.page) : 1;
                    
                    fetchAdminModUsers(role, currentPage, sortField, newOrder);
                });
            }
        });
    }
}

function fetchAdminModUsers(role, page = 1, sortField = role === 'mod' ? currentModSortField : currentAdminSortField, sortOrder = role === 'mod' ? currentModSortOrder : currentAdminSortOrder) {
    if (role === 'mod') {
        currentModSortField = sortField;
        currentModSortOrder = sortOrder;
    } else {
        currentAdminSortField = sortField;
        currentAdminSortOrder = sortOrder;
    }

    fetch(`admin_manager.php?ajax=1&role=${role}&page=${page}&sort=${sortField}&order=${sortOrder}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }
            
            const totalPages = Math.ceil(data.total / data.perPage);
            const tableId = role === 'mod' ? 'mod-body' : 'admin-body';
            const paginationId = role === 'mod' ? 'mod-pagination' : 'admin-pagination';
            
            renderAdminModUsers(tableId, data.users);
            renderPagination(paginationId, data.page, totalPages, role);
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}

function deleteAdminMod(userId) {
    if (!confirm('Vai tiešām vēlaties dzēst šo lietotāju?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('user_id', userId);
    
    fetch('admin_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchAdminModUsers('mod', 1);
            fetchAdminModUsers('admin', 1);
        } else {
            alert('Kļūda: ' + (data.message || 'Neizdevās dzēst lietotāju'));
        }
    })
    .catch(err => alert('Kļūda: ' + err));
}

// Create User 
if (document.getElementById('create-user-form')) {
    document.getElementById('create-user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const feedback = document.getElementById('create-user-feedback');
        
        fetch('admin_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            feedback.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.success) {
                feedback.classList.add('alert-success');
                feedback.textContent = 'Lietotājs veiksmīgi izveidots!';
                this.reset();
                fetchAdminModUsers('mod', 1);
                fetchAdminModUsers('admin', 1);
            } else {
                feedback.classList.add('alert-danger');
                feedback.textContent = data.message || 'Kļūda izveidojot lietotāju!';
            }
        })
        .catch(err => {
            feedback.classList.remove('d-none', 'alert-success');
            feedback.classList.add('alert-danger');
            feedback.textContent = 'Kļūda: ' + err;
        });
    });
}


if (document.getElementById('mod-body')) {
    fetchAdminModUsers('mod', 1);
    fetchAdminModUsers('admin', 1);
}

// Admin Details 
function renderAdminActions(actions) {
    const tbody = document.getElementById('actions-body');
    if (!tbody) return;

    if (actions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nav veiktu darbību.</td></tr>';
        return;
    }

    let html = '';
    actions.forEach(action => {
        html += `
            <tr>
                <td>${escapeHtml(action.ID)}</td>
                <td>${escapeHtml(action.event_title)}</td>
                <td>${escapeHtml(action.reason)}</td>
                <td>${formatDate(action.deleted_at)}</td>
                <td>${action.undeleted_at ? formatDate(action.undeleted_at) : '-'}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function fetchAdminActions(userId, page = 1, sortField = 'deleted_at', sortOrder = 'DESC') {
    fetch(`admin-details.php?ajax=1&id=${userId}&page=${page}&sort=${sortField}&order=${sortOrder}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }
            
            const totalPages = Math.ceil(data.total / data.perPage);
            renderAdminActions(data.actions);
            renderPagination('actions-pagination', data.page, totalPages, 'actions');
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}


if (document.getElementById('actions-body')) {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');
    if (userId) {
        fetchAdminActions(userId, 1);
    }
}

// admin details page
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');
    
    if (userId) {
        
        fetchAdminActions(userId);
        
        //  click for sorting
        const table = document.querySelector('.table');
        if (table) {
            const headers = table.querySelectorAll('th.sortable');
            headers.forEach(header => {
                if (!header.hasAttribute('data-sort-initialized')) {
                    header.setAttribute('data-sort-initialized', 'true');
                    header.addEventListener('click', () => {
                        const currentOrder = header.getAttribute('data-order') || 'desc';
                        const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
                        
                        header.setAttribute('data-order', newOrder);
                        const sortField = header.getAttribute('data-sort');
                        
                        headers.forEach(h => {
                            if (h !== header) {
                                h.removeAttribute('data-order');
                            }
                        });
                        
                        fetchAdminActions(userId, 1, sortField, newOrder);
                    });
                }
            });
        }
        
      
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('pagination-btn')) {
                const page = parseInt(e.target.getAttribute('data-page'));
                const table = e.target.getAttribute('data-table');
                if (!isNaN(page) && table === 'actions') {
                    const sortHeader = document.querySelector('th.sortable[data-order]');
                    const sortField = sortHeader ? sortHeader.getAttribute('data-sort') : 'deleted_at';
                    const sortOrder = sortHeader ? sortHeader.getAttribute('data-order') : 'DESC';
                    fetchAdminActions(userId, page, sortField, sortOrder);
                }
            }
        });
    }
});

document.getElementById('searchUsers')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchUsers('all', 1, filterPeriodSelect?.value || 'all', currentUserSortField, currentUserSortOrder, e.target.value);
    }, 300);
});

document.getElementById('clearSearch')?.addEventListener('click', function() {
    const searchInput = document.getElementById('searchUsers');
    searchInput.value = '';
    fetchUsers('all', 1, filterPeriodSelect?.value || 'all', currentUserSortField, currentUserSortOrder);
});

document.getElementById('searchEvents')?.addEventListener('input', function(e) {
    clearTimeout(eventSearchTimeout);
    eventSearchTimeout = setTimeout(() => {
        fetchEvents(1, currentSortField, currentSortOrder, 'all', e.target.value);
    }, 300);
});

document.getElementById('clearEventSearch')?.addEventListener('click', function() {
    const searchInput = document.getElementById('searchEvents');
    searchInput.value = '';
    fetchEvents(1, currentSortField, currentSortOrder, 'all');
});

// Event Manager Functions
function initializeEventManager() {
    const filterPeriodSelect = document.getElementById('filter-period');
    const eventsByDayCtx = document.getElementById('eventsByDayChart');
    
    if (eventsByDayCtx) {
        const eventsByDayChart = new Chart(eventsByDayCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Pasākumi dienā',
                        data: [],
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Pasākumi nedēļā',
                        data: [],
                        borderColor: 'rgba(28, 200, 138, 1)',
                        backgroundColor: 'rgba(28, 200, 138, 0.2)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Pasākumi mēnesī',
                        data: [],
                        borderColor: 'rgba(231, 74, 59, 1)',
                        backgroundColor: 'rgba(231, 74, 59, 0.2)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        
        updateEventStatistics(filterPeriodSelect.value);

        filterPeriodSelect.addEventListener('change', function() {
            updateEventStatistics(this.value);
        });

        // Update every 5 minutes
        setInterval(() => {
            updateEventStatistics(filterPeriodSelect.value);
        }, 100000);
    }
}

function updateEventStatistics(period = 'all') {
    const eventsByDayChart = Chart.getChart('eventsByDayChart');
    if (!eventsByDayChart) return;

    
    eventsByDayChart.data.labels = [];
    eventsByDayChart.data.datasets.forEach(dataset => {
        dataset.data = [];
    });
    
    // Daily statistics
    fetch(`event_manager.php?ajax=1&stats=daily&period=${period}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                const dailyData = data.data;
                eventsByDayChart.data.labels = dailyData.map(item => {
                    const date = new Date(item.day);
                    return date.toLocaleDateString('lv-LV', { day: '2-digit', month: '2-digit' });
                });
                eventsByDayChart.data.datasets[0].data = dailyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching daily stats:', err));

    // Weekly statistics
    fetch(`event_manager.php?ajax=1&stats=weekly&period=${period}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                const weeklyData = data.data;
                eventsByDayChart.data.datasets[1].data = weeklyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching weekly stats:', err));

    // Monthly statistics
    fetch(`event_manager.php?ajax=1&stats=monthly&period=${period}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                const monthlyData = data.data;
                eventsByDayChart.data.datasets[2].data = monthlyData.map(item => item.count);
                eventsByDayChart.update();
            }
        })
        .catch(err => console.error('Error fetching monthly stats:', err));
}


document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('eventsByDayChart')) {
        initializeEventManager();
    }
});

// Event Details Functions
function initializeEventDetails() {
    let currentEventId = null;
    let currentVolunteerPage = 1;
    const volunteersPerPage = 2;
    let currentVolunteerSortField = 'created_at';
    let currentVolunteerSortOrder = 'DESC';

    
    const volunteersContainer = document.getElementById('volunteers-table-container');
    if (volunteersContainer) {
        fetchVolunteers(currentVolunteerPage);
    }

    
    const paginationContainer = document.getElementById('volunteers-pagination');
    if (paginationContainer) {
        paginationContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('pagination-btn')) {
                const page = parseInt(e.target.dataset.page);
                if (!isNaN(page)) {
                    currentVolunteerPage = page;
                    fetchVolunteers(page);
                }
            }
        });
    }


    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (field === currentVolunteerSortField) {
                currentVolunteerSortOrder = currentVolunteerSortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentVolunteerSortField = field;
                currentVolunteerSortOrder = 'ASC';
            }
            fetchVolunteers(currentVolunteerPage);
        });
    });

    // Delete reason select
    const deleteReasonSelect = document.getElementById('delete_reason');
    if (deleteReasonSelect) {
        deleteReasonSelect.addEventListener('change', function() {
            const customReasonContainer = document.getElementById('custom_reason_container');
            if (customReasonContainer) {
                customReasonContainer.style.display = this.value === 'Cits' ? 'block' : 'none';
            }
        });
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} mb-4`;
    alert.textContent = message;
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

function showDeleteModal(eventId) {
    window.currentEventId = eventId;
    const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
    modal.show();
    
    
    const deleteReasonSelect = document.getElementById('delete_reason');
    const customReasonContainer = document.getElementById('custom_reason_container');
    if (deleteReasonSelect && customReasonContainer) {
        deleteReasonSelect.value = '';
        customReasonContainer.style.display = 'none';
    }
}

function confirmDelete() {
    const reasonSelect = document.getElementById('delete_reason');
    let reason = reasonSelect.value;

    if (!reason) {
        alert('Lūdzu, izvēlies iemeslu dzēšanai');
        return;
    }

    if (reason === 'Cits') {
        const customReason = document.getElementById('custom_reason').value.trim();
        if (!customReason) {
            alert('Lūdzu, ievadi iemeslu');
            return;
        }
        reason = customReason;
    }

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete_event&event_id=${window.currentEventId}&reason=${encodeURIComponent(reason)}&ajax=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Sludinājums veiksmīgi dzēsts!', 'success');
            updateUIAfterDelete(window.currentEventId);
            bootstrap.Modal.getInstance(document.getElementById('deleteEventModal')).hide();
        } else {
            showAlert('Kļūda dzēšot sludinājumu!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Kļūda dzēšot sludinājumu!', 'danger');
    });
}

function updateUIAfterDelete(eventId) {
    const buttonContainer = document.querySelector('.d-flex.gap-2');
    buttonContainer.innerHTML = `
        <button onclick="undeleteEvent(${eventId})" class="btn btn-success">
            <i class="fas fa-undo me-2"></i>Atjaunot
        </button>
    `;
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.className = 'badge bg-danger status-badge';
        statusBadge.textContent = 'Dzēsts';
    }
}

function undeleteEvent(eventId) {
    if (!confirm('Vai tiešām vēlaties atjaunot šo sludinājumu?')) {
        return;
    }

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=undelete_event&event_id=${eventId}&ajax=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Sludinājums veiksmīgi atjaunots!', 'success');
            updateUIAfterUndelete(eventId);
        } else {
            showAlert('Kļūda atjaunojot sludinājumu!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Kļūda atjaunojot sludinājumu!', 'danger');
    });
}

function updateUIAfterUndelete(eventId) {
    const buttonContainer = document.querySelector('.d-flex.gap-2');
    buttonContainer.innerHTML = `
        <a href="event_edit.php?id=${eventId}" class="btn btn-primary-style">
            <i class="fas fa-edit me-2"></i>Rediģēt
        </a>
        <button onclick="showDeleteModal(${eventId})" class="btn btn-danger">
            <i class="fas fa-trash me-2"></i>Dzēst
        </button>
    `;
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge) {
        statusBadge.className = 'badge bg-success status-badge';
        statusBadge.textContent = 'Aktīvs';
    }
}

function renderVolunteersTable(volunteers) {
    const tableBody = document.getElementById('volunteersTableBody');
    if (!tableBody) return;

    if (!volunteers || volunteers.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nav brīvprātīgo</td></tr>';
        return;
    }

    tableBody.innerHTML = volunteers.map(volunteer => `
        <tr>
            <td>${escapeHtml(volunteer.name)}</td>
            <td>${escapeHtml(volunteer.surname)}</td>
            <td>${escapeHtml(volunteer.username)}</td>
            <td>${escapeHtml(volunteer.email)}</td>
            <td>${formatDate(volunteer.created_at)}</td>
            <td><span class="badge ${getStatusBadgeClass(volunteer.status)}">${getStatusText(volunteer.status)}</span></td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'accepted':
            return 'bg-success';
        case 'denied':
            return 'bg-danger';
        case 'waiting':
            return 'bg-warning';
        case 'left':
            return 'bg-secondary';
        default:
            return 'bg-warning';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'accepted':
            return 'Apstiprināts';
        case 'denied':
            return 'Noraidīts';
        case 'waiting':
            return 'Gaida';
        case 'left':
            return 'Izstājies';
        default:
            return status;
    }
}

function updateSortIcons() {
    document.querySelectorAll('.sortable').forEach(header => {
        const field = header.dataset.sort;
        const headerText = header.textContent.replace(/[↑↓]/, '').trim();
        
        if (field === currentVolunteerSortField) {
            header.innerHTML = headerText + (currentVolunteerSortOrder === 'ASC' ? ' ↑' : ' ↓');
        } else {
            header.innerHTML = headerText;
        }
    });
}

function fetchVolunteers(page = 1) {
    const eventId = new URLSearchParams(window.location.search).get('id');
    if (!eventId) return;

    const perPage = 2;
    const sortField = 'created_at';
    const sortOrder = 'DESC';

    fetch(`event_details.php?action=get_volunteers&event_id=${eventId}&page=${page}&per_page=${perPage}&sort_field=${sortField}&sort_order=${sortOrder}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderVolunteersTable(data.volunteers);
                const totalPages = Math.ceil(data.total / perPage);
                renderPagination('volunteersPagination', page, totalPages);
            } else {
                console.error('Error fetching volunteers:', data.message);
                showAlert('Neizdevās ielādēt brīvprātīgos', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Neizdevās ielādēt brīvprātīgos', 'danger');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    
    initializeEventDetails();
    
    
    fetchVolunteers(1);

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagination-btn')) {
            const page = parseInt(e.target.getAttribute('data-page'));
            if (!isNaN(page)) {
                fetchVolunteers(page);
            }
        }
    });
}); 