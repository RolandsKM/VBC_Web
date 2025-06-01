

     console.log('Script loaded and DOM ready');
    const perPage = 5;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
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
}


function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV') + ' ' + date.toLocaleTimeString('lv-LV');
}

    function renderPagination(containerId, currentPage, totalPages, table) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '';

        
        html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" data-table="${table}" class="pagination-btn btn btn-sm  me-1">First</button>`;
        html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" data-table="${table}" class="pagination-btn btn btn-sm  me-1">Prev</button>`;

         
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

        
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" data-table="${table}" class="pagination-btn btn btn-sm  me-1">Next</button>`;
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" data-table="${table}" class="pagination-btn btn btn-sm ">Last</button>`;

        container.innerHTML = html;
    }

    const filterPeriodSelect = document.getElementById('filter-period');
    const totalUsersSpan = document.getElementById('total-users');
    const bannedUsersSpan = document.getElementById('banned-users');

    function fetchUsers(table, page, period = 'all') {
        let url = `user_manager.php?ajax=1&table=${table}&page=${page}`;
        if (table === 'all') {
            url += `&period=${period}`;
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

                    
                    fetch(`user_manager.php?ajax=1&table=all&count=banned&period=${period}`)
                        .then(resp => resp.json())
                        .then(countData => {
                            if (countData.success) {
                                totalUsersSpan.textContent = data.total;
                                bannedUsersSpan.textContent = countData.bannedCount ?? '0';
                            }
                        });
                }
            })
            .catch(err => alert('Kļūda ielādējot datus: ' + err));
    }

    filterPeriodSelect.addEventListener('change', () => {
        fetchUsers('all', 1, filterPeriodSelect.value);
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('pagination-btn')) {
            const page = parseInt(e.target.getAttribute('data-page'));
            const table = e.target.getAttribute('data-table');
            const period = table === 'all' ? filterPeriodSelect.value : 'all';
            fetchUsers(table, page, period);
        }
    });

  
    fetchUsers('todays', 1);
    fetchUsers('all', 1, filterPeriodSelect.value);

    const bannedActiveCtx = document.getElementById('bannedActiveChart').getContext('2d');
    const newUsersCtx = document.getElementById('newUsersChart').getContext('2d');

let bannedActiveChart = new Chart(bannedActiveCtx, {
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
        maintainAspectRatio: false, // << Important
        plugins: {
            legend: { position: 'bottom' },
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
});


    let newUsersChart = new Chart(newUsersCtx, {
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
            scales: {
                x: { ticks: { maxRotation: 90, minRotation: 45 }},
                y: { beginAtZero: true }
            }
        }
    });

    function updateCharts(period) {
      
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

  
    filterPeriodSelect.addEventListener('change', () => {
        updateCharts(filterPeriodSelect.value);
    });


    updateCharts(filterPeriodSelect.value);

document.addEventListener('DOMContentLoaded', () => {
  // Export to CSV
  document.querySelectorAll('.dropdown-item').forEach(item => {
    if (item.textContent.trim() === 'Eksportēt uz CSV') {
      item.addEventListener('click', () => {
        exportTableToCSV('users.csv');
      });
    }
    if (item.textContent.trim() === 'Drukāt') {
      item.addEventListener('click', () => {
        window.print();
      });
    }
  });
});

// Helper function to export HTML table to CSV
function exportTableToCSV(filename) {
  let csv = [];
  const table = document.querySelector('.table'); // assumes you want to export the first table (you can be more specific)
  const rows = table.querySelectorAll('tr');

  rows.forEach(row => {
    const cols = row.querySelectorAll('td, th');
    const rowData = [];
    cols.forEach(col => {
      // Escape double quotes by doubling them
      let data = col.innerText.replace(/"/g, '""');
      // Wrap data containing commas or quotes in double quotes
      if (data.search(/("|,|\n)/g) >= 0) data = `"${data}"`;
      rowData.push(data);
    });
    csv.push(rowData.join(','));
  });

  // Create a blob and download it
  const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
  const downloadLink = document.createElement('a');
  downloadLink.download = filename;
  downloadLink.href = URL.createObjectURL(csvFile);
  downloadLink.style.display = 'none';
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}
