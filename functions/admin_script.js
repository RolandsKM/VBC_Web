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
                label: 'Events per Day',
                data: [],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            },
            {
                label: 'Events per Week',
                data: [],
                borderColor: 'rgba(28, 200, 138, 1)',
                backgroundColor: 'rgba(28, 200, 138, 0.2)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            },
            {
                label: 'Events per Month',
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
        <tr class="${user.banned ? 'table-danger' : ''}">
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(formatDate(user.created_at || user.registration_date))}</td>
            <td>
                ${user.banned ? 
                    '<span class="badge bg-danger">Bloķēts</span>' : 
                    '<span class="badge bg-success">Aktīvs</span>'}
            </td>
            <td class="text-center">
                <a href="user-details.php?id=${user.ID_user}" class="btn btn-sm btn-primary">
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

function renderPagination(containerId, currentPage, totalPages, table = null) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '';
    
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" data-table="${table}" class="pagination-btn btn btn-sm me-1">First</button>`;
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Prev</button>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (endPage - startPage < 4) {
        if (startPage === 1) {
            endPage = Math.min(totalPages, startPage + 4);
        } else if (endPage === totalPages) {
            startPage = Math.max(1, endPage - 4);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button ${i === currentPage ? 'disabled' : ''} data-page="${i}" data-table="${table}" class="pagination-btn btn btn-sm me-1">${i}</button>`;
    }

    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Next</button>`;
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" data-table="${table}" class="pagination-btn btn btn-sm">Last</button>`;

    container.innerHTML = html;
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
        <tr>
            <td>${user.ID_user}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.surname)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${formatDate(user.created_at)}</td>
            <td class="text-center">
                <a href="admin-details.php?id=${user.ID_user}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Apskatīt
                </a>
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
                    
                    fetchAdminModUsers(role, 1, sortField, newOrder);
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
        
        // click  pagination
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