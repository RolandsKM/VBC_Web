let currentPage = 1;
let currentSortField = 'reported_at';
let currentSortOrder = 'DESC';
let reportSearchTimeout;
let currentReportSearch = '';
const perPage = 5;

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

function loadReports() {
    $.ajax({
        url: '../functions/ReportController.php',
        method: 'POST',
        data: {
            ajax: true,
            action: 'get_reports',
            limit: perPage,
            offset: (currentPage - 1) * perPage,
            sort_by: currentSortField,
            sort_order: currentSortOrder,
            search: currentReportSearch
        },
        success: function(response) {
            if (response.success) {
                renderReportsTable(response.reports);
                const totalPages = Math.ceil(response.total / perPage);
                renderPagination('pagination', currentPage, totalPages, 'reports');
            } else {
                console.error('Failed to load reports:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading reports:', error);
        }
    });
}

function loadReportStatistics() {
    $.ajax({
        url: '../functions/ReportController.php',
        method: 'POST',
        data: {
            ajax: true,
            action: 'get_report_statistics'
        },
        success: function(response) {
            if (response.success) {
                const stats = response.statistics;
                // Update reports count
                $('#totalReports').text(stats.total_reports);
                
                // Update top reported table
                const topReportedHtml = stats.top_event_owners.map((owner, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${owner.username}</td>
                        <td>${owner.reported_events_count}</td>
                    </tr>
                `).join('');
                $('#topReportedTable').html(topReportedHtml);
            } else {
                console.error('Failed to load statistics:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading statistics:', error);
        }
    });
}

// reports table
function renderReportsTable(reports) {
    const tbody = document.getElementById('reports-body');
    if (!tbody) return;

    if (reports.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nav ziņojumu</td></tr>';
        return;
    }

    
    const startNumber = (currentPage - 1) * perPage + 1;

    tbody.innerHTML = reports.map((report, index) => `
        <tr>
            <td>${startNumber + index}</td>
            <td>${escapeHtml(report.title)}</td>
            <td>${escapeHtml(report.creator_username)}</td>
            <td>${formatDate(report.reported_at)}</td>
            <td>
                <span class="badge ${report.event_deleted ? 'bg-danger' : 'bg-success'}">
                    ${report.event_deleted ? 'Dzēsts' : 'Aktīvs'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary view-report" data-report-id="${report.ID_report}">
                    <i class="fas fa-eye"></i> Apskatīt
                </button>
            </td>
        </tr>
    `).join('');
}

// pagination
function renderPagination(containerId, currentPage, totalPages, table) {
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

// Show report info
function showReportDetails(reportId) {
    $.ajax({
        url: '../functions/ReportController.php',
        method: 'POST',
        data: {
            ajax: true,
            action: 'get_report_details',
            report_id: reportId
        },
        success: function(response) {
            if (response.success) {
                const report = response.report;
              
                $('#reportDetailsModal .modal-body').html(`
                    <div class="report-details">
                        <!-- Report Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-primary text-white p-3 rounded-top">
                                <h5 class="mb-0"><i class="fas fa-flag me-2"></i>Ziņojuma informācija</h5>
                            </div>
                            <div class="section-body p-3 border border-top-0 rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-exclamation-circle me-2"></i>Iemesls:</strong>
                                            <span class="ms-2">${report.reason}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-calendar me-2"></i>Ziņojuma datums:</strong>
                                            <span class="ms-2">${formatDate(report.reported_at)}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-info-circle me-2"></i>Status:</strong>
                                            <span class="ms-2 badge ${report.status === 'waiting' ? 'bg-warning' : 'bg-success'}">
                                                ${report.status === 'waiting' ? 'Gaida izskatīšanu' : 'Atrisināts'}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Information Section -->
                        <div class="section mb-4">
                            <div class="section-header bg-info text-white p-3 rounded-top">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Pasākuma informācija</h5>
                            </div>
                            <div class="section-body p-3 border border-top-0 rounded-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-heading me-2"></i>Nosaukums:</strong>
                                            <span class="ms-2">${report.event_title}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-map-marker-alt me-2"></i>Vieta:</strong>
                                            <span class="ms-2">${report.event_location}, ${report.event_city}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-clock me-2"></i>Datums:</strong>
                                            <span class="ms-2">${formatDate(report.event_date)}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-info-circle me-2"></i>Status:</strong>
                                            <span class="ms-2 badge ${report.event_deleted ? 'bg-danger' : 'bg-success'}">
                                                ${report.event_deleted ? 'Dzēsts' : 'Aktīvs'}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-align-left me-2"></i>Apraksts:</strong>
                                        </p>
                                        <div class="p-2 bg-light rounded">
                                            ${report.event_description}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Information Section -->
                        <div class="section mb-4">
                            <div class="row">
                                <!-- Reporter Information -->
                                <div class="col-md-6">
                                    <div class="section-header bg-success text-white p-3 rounded-top">
                                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Ziņotāja informācija</h5>
                                    </div>
                                    <div class="section-body p-3 border border-top-0 rounded-bottom">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-user-circle me-2"></i>Lietotājvārds:</strong>
                                            <span class="ms-2">${report.reporter_username}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-envelope me-2"></i>E-pasts:</strong>
                                            <span class="ms-2">${report.reporter_email}</span>
                                        </p>
                                    </div>
                                </div>
                                <!-- Creator Information -->
                                <div class="col-md-6">
                                    <div class="section-header bg-warning text-dark p-3 rounded-top">
                                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Pasākuma veidotāja informācija</h5>
                                    </div>
                                    <div class="section-body p-3 border border-top-0 rounded-bottom">
                                        <p class="mb-2">
                                            <strong><i class="fas fa-user-circle me-2"></i>Lietotājvārds:</strong>
                                            <span class="ms-2">${report.creator_username}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-envelope me-2"></i>E-pasts:</strong>
                                            <span class="ms-2">${report.creator_email}</span>
                                        </p>
                                        <p class="mb-2">
                                            <strong><i class="fas fa-user-shield me-2"></i>Status:</strong>
                                            <span class="ms-2 badge ${report.creator_banned ? 'bg-danger' : 'bg-success'}">
                                                ${report.creator_banned ? 'Bloķēts' : 'Aktīvs'}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Owner's Report History Section -->
                        <div class="section">
                            <div class="section-header bg-danger text-white p-3 rounded-top">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Iepriekšējie atrisinātie ziņojumi par pasākumu veidotāju</h5>
                            </div>
                            <div class="section-body p-3 border border-top-0 rounded-bottom">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-header-style">
                                            <tr>
                                                <th>Pasākums</th>
                                                <th>Datums</th>
                                                <th>Ziņojumu skaits</th>
                                                <th>Iemesli</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${report.owner_history && report.owner_history.length > 0 ? 
                                                report.owner_history.map(event => `
                                                    <tr>
                                                        <td>${event.event_title}</td>
                                                        <td>${formatDate(event.event_date)}</td>
                                                        <td>${event.report_count}</td>
                                                        <td>
                                                            <div class="small">
                                                                ${event.report_reasons.split(',').map(reason => 
                                                                    `<span class="badge bg-secondary me-1 mb-1">${reason}</span>`
                                                                ).join('')}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge ${event.event_deleted ? 'bg-danger' : 'bg-success'}">
                                                                ${event.event_deleted ? 'Dzēsts' : 'Aktīvs'}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                `).join('') : 
                                                '<tr><td colspan="5" class="text-center">Nav iepriekšējo atrisināto ziņojumu</td></tr>'
                                            }
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

              
                let buttons = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Aizvērt
                    </button>
                `;
                
                if (!report.event_deleted) {
                    buttons += `
                        <button type="button" class="btn btn-danger delete-event" data-event-id="${report.ID_event}">
                            <i class="fas fa-trash me-2"></i>Dzēst pasākumu
                        </button>
                    `;
                }
                
                if (!report.creator_banned) {
                    buttons += `
                        <button type="button" class="btn btn-warning ban-user" data-user-id="${report.creator_id}">
                            <i class="fas fa-ban me-2"></i>Bloķēt lietotāju
                        </button>
                    `;
                }
                
                buttons += `
                    <button type="button" class="btn btn-success solve-report" data-report-id="${report.ID_report}">
                        <i class="fas fa-check me-2"></i>Atrisināts
                    </button>
                `;

                $('#reportDetailsModal .modal-footer').html(buttons);

              
                const modal = new bootstrap.Modal(document.getElementById('reportDetailsModal'));
                modal.show();
            } else {
                alert('Kļūda ielādējot ziņojuma detaļas: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading report details:', error);
            alert('Kļūda ielādējot ziņojuma detaļas');
        }
    });
}


function showDeleteConfirmation(eventId) {
    
    $.ajax({
        url: '../functions/ReportController.php',
        method: 'POST',
        data: {
            ajax: true,
            action: 'get_deletion_reasons'
        },
        success: function(response) {
            if (response.success) {
                const reasons = response.reasons;
                let options = '';
                
                for (const [key, value] of Object.entries(reasons)) {
                    options += `<option value="${key}">${value}</option>`;
                }

                const modalHtml = `
                    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Dzēst pasākumu</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="deleteReason" class="form-label">Iemesls:</label>
                                        <select class="form-select" id="deleteReason">
                                            <option value="">Izvēlieties iemeslu</option>
                                            ${options}
                                        </select>
                                    </div>
                                    <div class="mb-3" id="customReasonContainer" style="display: none;">
                                        <label for="customReason" class="form-label">Cits iemesls:</label>
                                        <textarea class="form-control" id="customReason" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                                    <button type="button" class="btn btn-danger" id="confirmDelete" data-event-id="${eventId}">
                                        <i class="fas fa-trash"></i> Dzēst
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                
                $('#deleteConfirmationModal').remove();
                
                
                $('body').append(modalHtml);

                const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
                modal.show();

                
                $('#deleteReason').on('change', function() {
                    const selectedReason = $(this).val();
                    if (selectedReason === 'other') {
                        $('#customReasonContainer').show();
                    } else {
                        $('#customReasonContainer').hide();
                    }
                });


                $('#confirmDelete').on('click', function() {
                    const selectedReason = $('#deleteReason').val();
                    let reason = selectedReason;

                    if (selectedReason === 'other') {
                        reason = $('#customReason').val().trim();
                        if (!reason) {
                            alert('Lūdzu, ievadiet iemeslu');
                            return;
                        }
                    }

                    if (!reason) {
                        alert('Lūdzu, izvēlieties iemeslu');
                        return;
                    }

                    $.ajax({
                        url: '../functions/ReportController.php',
                        method: 'POST',
                        data: {
                            ajax: true,
                            action: 'delete_event',
                            event_id: eventId,
                            reason: reason
                        },
                        success: function(response) {
                            if (response.success) {

                                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
                                deleteModal.hide();
                                const detailsModal = bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal'));
                                detailsModal.hide();

                                loadReports();
                            } else {
                                alert('Kļūda dzēšot pasākumu: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting event:', error);
                            alert('Kļūda dzēšot pasākumu');
                        }
                    });
                });
            } else {
                alert('Kļūda ielādējot iemeslus: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading deletion reasons:', error);
            alert('Kļūda ielādējot iemeslus');
        }
    });
}


$(document).ready(function() {
  
    loadReports();
    loadReportStatistics();

   
    $('#reportsTable thead tr').prepend(`
        <th>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllReports">
            </div>
        </th>
    `);

    $('.card-body').prepend(`
        <div class="bulk-actions mb-3" style="display: none;">
            <button class="btn btn-success" id="solveSelectedReports">
                <i class="fas fa-check me-2"></i>Atrisināt izvēlētos
            </button>
        </div>
    `);


    $(document).on('change', '#selectAllReports', function() {
        const isChecked = $(this).prop('checked');
        $('.report-checkbox').prop('checked', isChecked);
        updateBulkActionsVisibility();
    });

  
    $(document).on('change', '.report-checkbox', function() {
        updateBulkActionsVisibility();
   
        const totalCheckboxes = $('.report-checkbox').length;
        const checkedCheckboxes = $('.report-checkbox:checked').length;
        $('#selectAllReports').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    
    function updateBulkActionsVisibility() {
        const hasCheckedReports = $('.report-checkbox:checked').length > 0;
        $('.bulk-actions').toggle(hasCheckedReports);
    }

   
    $(document).on('click', '#solveSelectedReports', function() {
        const selectedReports = $('.report-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedReports.length === 0) {
            alert('Lūdzu, izvēlieties vismaz vienu ziņojumu');
            return;
        }

        if (confirm(`Vai tiešām vēlaties atrisināt ${selectedReports.length} ziņojumu(s)?`)) {
            $.ajax({
                url: '../functions/ReportController.php',
                method: 'POST',
                data: {
                    ajax: true,
                    action: 'solve_reports_bulk',
                    report_ids: selectedReports
                },
                success: function(response) {
                    if (response.success) {
                       
                        $('.report-checkbox, #selectAllReports').prop('checked', false);
                       
                        $('.bulk-actions').hide();
                       
                        loadReports();
                    } else {
                        alert('Kļūda atrisinot ziņojumus: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error solving reports:', error);
                    alert('Kļūda atrisinot ziņojumus');
                }
            });
        }
    });

    
    $(document).on('click', '.sortable', function() {
        const sortField = $(this).data('sort');
        
        // Remove active class and arrows from all headers
        $('.sortable').removeClass('active').find('i').remove();
        
        if (currentSortField === sortField) {
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSortField = sortField;
            currentSortOrder = 'ASC';
        }
        
        // Add active class and arrow to current header
        $(this).addClass('active').append(`<i>${currentSortOrder === 'ASC' ? '↑' : '↓'}</i>`);
        
        currentPage = 1;
        loadReports();
    });

    $(document).on('click', '.pagination-btn', function() {
        if (!$(this).prop('disabled')) {
            currentPage = parseInt($(this).data('page'));
            loadReports();
        }
    });

    $(document).on('click', '.view-report', function() {
        const reportId = $(this).data('report-id');
        showReportDetails(reportId);
    });


    $(document).on('click', '.delete-event', function() {
        const eventId = $(this).data('event-id');
        showDeleteConfirmation(eventId);
    });

    
    $(document).on('click', '.solve-report', function() {
        const reportId = $(this).data('report-id');
        $.ajax({
            url: '../functions/ReportController.php',
            method: 'POST',
            data: {
                ajax: true,
                action: 'solve_report',
                report_id: reportId
            },
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal'));
                    modal.hide();
                    loadReports();
                } else {
                    alert('Kļūda atzīmējot ziņojumu kā atrisinātu: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error solving report:', error);
                alert('Kļūda atzīmējot ziņojumu kā atrisinātu');
            }
        });
    });

 
    $(document).on('click', '.ban-user', function() {
        if (confirm('Vai tiešām vēlaties bloķēt šo lietotāju?')) {
            const userId = $(this).data('user-id');
            $.ajax({
                url: '../functions/ReportController.php',
                method: 'POST',
                data: {
                    ajax: true,
                    action: 'ban_user',
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal'));
                        modal.hide();
                        loadReports();
                    } else {
                        alert('Kļūda bloķējot lietotāju: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error banning user:', error);
                    alert('Kļūda bloķējot lietotāju');
                }
            });
        }
    });

    // Add search functionality
    $(document).on('input', '#searchReports', function() {
        clearTimeout(reportSearchTimeout);
        const value = $(this).val();
        reportSearchTimeout = setTimeout(() => {
            currentReportSearch = value;
            currentPage = 1;
            loadReports();
        }, 300);
    });

    $(document).on('click', '#clearReportSearch', function() {
        $('#searchReports').val('');
        currentReportSearch = '';
        currentPage = 1;
        loadReports();
    });

    // Update statistics when reports are solved or deleted
    $(document).on('click', '.solve-report, .delete-event, #solveSelectedReports', function() {
        setTimeout(loadReportStatistics, 500); // Reload statistics after action
    });
}); 