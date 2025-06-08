document.addEventListener('DOMContentLoaded', function() {
    
    const eventsCtx = document.getElementById('eventsChart').getContext('2d');
    const usersCtx = document.getElementById('usersChart').getContext('2d');

    console.log('Chart data:', {
        usersChartData: window.usersChartData,
        eventsChartLabels: window.eventsChartLabels,
        eventsChartData: window.eventsChartData
    });

    // Events chart
    const eventsChart = new Chart(eventsCtx, {
        type: 'line',
        data: {
            labels: window.eventsChartLabels || [],
            datasets: [{
                label: 'Sludinājumi pa stundām',
                data: window.eventsChartData || [],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                fill: true,
                tension: 0.3
            }]
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

    // Users chart
    try {
        const usersChart = new Chart(usersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Jauni', 'Bloķēti', 'Aktīvi'],
                datasets: [{
                    data: window.usersChartData || [0, 0, 0],
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(78, 115, 223, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error initializing users chart:', error);
    }

    function fetchData(type, page) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        url.searchParams.set(type + '_page', page);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(type + '-body').innerHTML = data[type].html;
                    updatePagination(type, data[type].currentPage, data[type].totalPages);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function updatePagination(type, currentPage, totalPages) {
        const paginationContainer = document.getElementById(type + '-pagination');
        if (!paginationContainer) return;

        let html = '';

        
        html += `<button class="pagination-btn" data-page="1" ${currentPage === 1 ? 'disabled' : ''}>First</button>`;
        html += `<button class="pagination-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;

        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination-btn" data-page="${i}" ${i === currentPage ? 'disabled' : ''}>${i}</button>`;
        }

        html += `<button class="pagination-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
        html += `<button class="pagination-btn" data-page="${totalPages}" ${currentPage === totalPages ? 'disabled' : ''}>Last</button>`;

        paginationContainer.innerHTML = html;

        
        paginationContainer.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    const page = parseInt(this.dataset.page);
                    fetchData(type, page);
                }
            });
        });
    }

  
    if (document.getElementById('events-body')) {
        fetchData('events', 1);
    }
    if (document.getElementById('users-body')) {
        fetchData('users', 1);
    }
    if (document.getElementById('volunteers-body')) {
        fetchData('volunteers', 1);
    }
}); 