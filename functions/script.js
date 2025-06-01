// User Profile - Own/Joined
$(document).ready(function () {
    let ownOffset = 0;
    let joinedOffset = 0;
    const limit = 4;

    function loadEvents(append = false) {
        $.ajax({
            url: `../functions/event_functions.php?action=own&offset=${ownOffset}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (append) {
                    $("#own-events-grid").append(response.html);
                } else {
                    $("#own-events-grid").html(response.html);
                }


                if (!response.hasMore || $.trim(response.html) === "") {
                    $("#load-more-own").hide();
                } else {
                    $("#load-more-own").show();
                }
            }
        });
    }

    function loadJoinedEvents(append = false) {
        $.ajax({
            url: `../functions/event_functions.php?action=joined&offset=${joinedOffset}&limit=${limit}`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (append) {
                    $("#joined-events-grid").append(response.html);
                } else {
                    $("#joined-events-grid").html(response.html);
                }

                if (!response.hasMore || $.trim(response.html) === "") {
                    $("#load-more-joined").hide();
                } else {
                    $("#load-more-joined").show();
                }
            }
        });
    }

    $("#load-more-own").click(function () {
        ownOffset += limit;
        loadEvents(true);
    });

    $("#load-more-joined").click(function () {
        joinedOffset += limit;
        loadJoinedEvents(true);
    });

    $(".sludinajumi-btn").click(function () {
        ownOffset = 0;
        $(".event-container").show();
        $(".joined-container").hide();
        $(".action-btn button").removeClass("active");
        $(this).addClass("active");
        loadEvents();
    });

    $(".pieteicies-btn").click(function () {
        joinedOffset = 0;
        $(".event-container").hide();
        $(".joined-container").show();
        $(".action-btn button").removeClass("active");
        $(this).addClass("active");
        loadJoinedEvents();
    });

    $(".event-container").show();
    $(".joined-container").hide();
    $(".sludinajumi-btn").addClass("active");
    loadEvents();

    

});


// Event Creation and Category Loading
$(document).ready(function () {

    loadCategories(); 

   
    $("#event-form").submit(function (e) {
        e.preventDefault(); 

        let eventData = {
            title: $("#event-title").val(),
            description: $("#event-description").val(),
            location: $("#event-location").val(),
            city: $("#event-city").val(),
            zip: $("#event-zip").val(),
            category_id: $("#event-categories").val(),  
            date: $("#event-date").val()
        };

        $.ajax({
            url: '../functions/event_functions.php',
            type: 'POST',
            data: {
            ...eventData,
            action: 'create'
        },
            success: function (response) {
                console.log(response); 
            if (response === "success") {
               
                alert("Pasākums izveidots veiksmīgi!");
                window.location.href = "user/"; 
            } else {
                    alert("Kļūda: " + response); 
                }
            },
            error: function () {
                alert("Kļūda: Neizdevās izveidot pasākumu.");
            }
        });
        
    });
     
function loadCategories() {
    $.ajax({
        url: '../functions/get_categories.php',
        type: 'GET',
        success: function (response) {
            let tempSelect = $("<select>").html(response);
            let cardsHtml = "";

            tempSelect.find("option").each(function () {
                const val = $(this).val();
                const text = $(this).text();
                const styleAttr = $(this).attr("style") || "";
                let colorMatch = styleAttr.match(/background-color:\s*([^;]+)/i);
                let bgColor = colorMatch ? colorMatch[1].trim() : "#f8f9fa"; 

                if (val) {
                    cardsHtml += `
                        <div class="category-card" data-id="${val}">
                            <div class="icon" style="background-color: ${bgColor};">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                            <p class="category-name">${text}</p>
                        </div>
                    `;

                }
            });
               
            $("#category-cards").html(cardsHtml);

          
            $(".category-card").click(function () {
                $(".category-card").removeClass("selected");
                $(this).addClass("selected");

                const selectedId = $(this).data("id");
                $("#event-categories").val(selectedId); 
            });
        },
        error: function () {
            alert("Kļūda: Neizdevās ielādēt kategorijas.");
        }
    });
}


});


// Event Editing
$(document).ready(function () {
    let originalData = {};

    $(document).on("click", ".edit-event-btn.bi-pencil", function () {
        const dateText = $(".date").text().replace("🗓 Datums:", "").trim();
        
        const locationText = $(".location").text().replace("📍 Pilsēta:", "").trim();
        const locationParts = locationText.split("|");
        const city = locationParts[0]?.trim().split(",")[0]?.trim();
        const location = locationParts[0]?.trim().split(",")[1]?.trim();
        const zip = locationParts[1]?.replace("Zip:", "").trim();

        originalData = {
            title: $(".title").text(),
            description: $(".description").html().replace(/<br\s*\/?>/g, "\n"),
            city: city,
            location: location,
            zip: zip,
            date: dateText
        };

        $(".title").replaceWith(`<input type="text" class="form-control title" value="${originalData.title}">`);
        $(".description").replaceWith(`<textarea class="form-control description" rows="5">${originalData.description}</textarea>`);
        $(".location").replaceWith(`
            <div class="row location">
                <div class="col-md-4">
                    <input type="text" class="form-control city" placeholder="Pilsēta" value="${originalData.city}">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control location-field" placeholder="Atrašanās vieta" value="${originalData.location}">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control zip" placeholder="Zip" value="${originalData.zip}">
                </div>
            </div>
        `);
        $(".date").replaceWith(`
            <div class="date">
                <input type="datetime-local" class="form-control date-input" value="${convertToInputDatetime(originalData.date)}">
            </div>
        `);

        $(".edit-actions").show();
    });

    $(document).on("click", ".cancel-edit", function () {
        $(".title").replaceWith(`<h1 class="title">${originalData.title}</h1>`);
        $(".description").replaceWith(`<p class="description">${originalData.description.replace(/\n/g, "<br>")}</p>`);
        $(".location").replaceWith(`<p class="location"><strong><i class='bi bi-geo-alt'></i> Pilsēta:</strong> ${originalData.city}, ${originalData.location} | Zip: ${originalData.zip}</p>`);
        $(".date").replaceWith(`<p class="date"><strong><i class='bi bi-calendar-check'></i> Datums:</strong> ${originalData.date}</p>`);
        $(".edit-actions").hide();
    });

    $(document).on("click", ".save-edit", function () {
        const formData = {
            event_id: $("#edit-event-id").val(),
            title: $(".title").val(),
            description: $(".description").val(),
            city: $(".city").val(),
            location: $(".location-field").val(),
            zip: $(".zip").val(),
            date: $(".date-input").val()
        };

        $.ajax({
            url: '../functions/event_functions.php',
            type: 'POST',
            data: {
                ...formData,
                action: 'update'
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi atjaunināts!");

                    $(".title").replaceWith(`<h1 class="title">${formData.title}</h1>`);
                    $(".description").replaceWith(`<p class="description">${formData.description.replace(/\n/g, "<br>")}</p>`);
                    $(".location").replaceWith(`<p class="location"><strong><i class='bi bi-geo-alt'></i> Pilsēta:</strong> ${formData.city}, ${formData.location} | Zip: ${formData.zip}</p>`);
                    $(".date").replaceWith(`<p class="date"><strong><i class='bi bi-calendar-check'></i> Datums:</strong> ${formatDateTime(formData.date)}</p>`);

                    $(".edit-actions").hide();
                } else {
                    alert("Kļūda: " + response.message);
                }
            },
            error: function () {
                alert("Neizdevās atjaunināt notikumu.");
            }
        });
    });

    function formatDateTime(inputDate) {
        const date = new Date(inputDate);
        return date.toLocaleString("lv-LV", {
            year: "numeric", 
            month: "2-digit", 
            day: "2-digit",
            hour: "2-digit", 
            minute: "2-digit"
        });
    }

    function convertToInputDatetime(lvDateString) {
        const parts = lvDateString.match(/(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2})/);
        if (!parts) return '';
        const [, day, month, year, hour, minute] = parts;
        return `${year}-${month}-${day}T${hour}:${minute}`;
    }
});


// Event Deletion
$(document).ready(function () {
    $(document).on("click", ".edit-event-btn.bi-trash", function () {

        if (!confirm("Vai tiešām vēlies dzēst šo notikumu?")) return;

        const eventId = $("#edit-event-id").val();

        $.ajax({
            url: '../functions/event_functions.php',
            type: 'POST',
            data: {
                event_id: eventId,
                action: 'delete'
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi dzēsts!");
                    window.location.href = "../user/"; 
                } else {
                    alert("Kļūda: " + response.message);
                }
            },
            error: function () {
                alert("Neizdevās dzēst notikumu.");
            }
        });
    });
});
// uer-event.php
$(document).ready(function () {
    const eventId = $('#edit-event-id').val();

    
    $.get(`../functions/event_functions.php?action=fetch_event_details&id=${eventId}`, function (data) {
        $('#event-details').html(data);
    });

  
    $.getJSON(`../functions/event_functions.php?action=fetch_event_info&id=${eventId}`, function (data) {
        $('#joined-count').text(data.total_joined);
    });

   
   function loadJoinedUsers() {
    if (!eventId) {
        console.warn('No event ID found');
        return;
    }

    $.ajax({
        url: '../functions/event_functions.php',
        method: 'GET',
        data: { 
            action: 'fetch_joined_users',
            id: eventId
        },
        success: function (data) {
            let users;
            try {
                users = typeof data === 'string' ? JSON.parse(data) : data;
                if (!Array.isArray(users)) {
                    if (typeof users === 'object' && users.users) {
                        users = users.users; 
                    } else {
                        console.error('Expected array of users, got:', typeof users);
                        return;
                    }
                }
            } catch (e) {
                console.error('Error parsing users data:', e);
                return;
            }

            const waitingUsers = [];
            const acceptedUsers = [];
            const deniedUsers = [];

            users.forEach(user => {
                if (user.status === 'waiting') waitingUsers.push(user);
                else if (user.status === 'accepted') acceptedUsers.push(user);
                else if (user.status === 'denied') deniedUsers.push(user);
            });

            $('#count-waiting').text(waitingUsers.length);
            $('#count-accepted').text(acceptedUsers.length);
            $('#count-denied').text(deniedUsers.length);

            paginateData(waitingUsers, 'waiting-pagination', 'waiting');
            paginateData(acceptedUsers, 'accepted-pagination', 'accepted');
            paginateData(deniedUsers, 'denied-pagination', 'denied');
        }
    });
}

function paginateData(dataArray, containerId, tableId, rowsPerPage = 10) {
    let currentPage = 1;

    function renderPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedData = dataArray.slice(start, end);

        let tableHtml = '';
        paginatedData.forEach((user, index) => {
            const rowNumber = start + index + 1;
            const checkbox = `<td><input type="checkbox" class="user-checkbox" data-id="${user.id_volunteer}" data-status="${user.status}"></td>`;
            tableHtml += `
                <tr>
                    ${checkbox}
                    <td>${rowNumber}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>
                        <select class="form-select status-select" data-id="${user.id_volunteer}">
                            <option value="waiting" ${user.status === 'waiting' ? 'selected' : ''}>Pieteicies</option>
                            <option value="accepted" ${user.status === 'accepted' ? 'selected' : ''}>Apstiprināts</option>
                            <option value="denied" ${user.status === 'denied' ? 'selected' : ''}>Noraidīts</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm view-user" data-id="${user.user_id}">
                            <i class="bi bi-eye"></i> Apskatīt
                        </button>
                    </td>
                </tr>
            `;
        });

        $(`#${tableId}-table tbody`).html(tableHtml);

        // Pagination controls
        const totalPages = Math.ceil(dataArray.length / rowsPerPage);
        let paginationHtml = '';

        if (totalPages > 1) {
            paginationHtml += `<button class="btn btn-sm btn-outline-secondary me-1" ${page === 1 ? 'disabled' : ''} data-page="1">⏮</button>`;
            paginationHtml += `<button class="btn btn-sm btn-outline-secondary me-1" ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">⬅</button>`;

            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `<button class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-outline-secondary'} me-1" data-page="${i}">${i}</button>`;
            }

            paginationHtml += `<button class="btn btn-sm btn-outline-secondary me-1" ${page === totalPages ? 'disabled' : ''} data-page="${page + 1}">➡</button>`;
            paginationHtml += `<button class="btn btn-sm btn-outline-secondary" ${page === totalPages ? 'disabled' : ''} data-page="${totalPages}">⏭</button>`;
        }

        $(`#${containerId}`).html(paginationHtml);
    }

    
    renderPage(currentPage);

 
    $(document).off(`click`, `#${containerId} button`).on(`click`, `#${containerId} button`, function () {
        const selectedPage = parseInt($(this).data('page'));
        if (!isNaN(selectedPage)) {
            currentPage = selectedPage;
            renderPage(currentPage);
        }
    });
}

   
    loadJoinedUsers();

    // Handle view user button click
    $(document).on('click', '.view-user', function() {
        const userId = $(this).data('id');
        showUserDetails(userId);
    });

    function showUserDetails(userId) {
        $.ajax({
            url: '../functions/event_functions.php',
            method: 'GET',
            data: {
                action: 'get_user_details',
                user_id: userId
            },
            success: function(response) {
                const data = JSON.parse(response);
                
                // Update modal content
                $('#userProfilePic').attr('src', data.profile_pic || '../assets/default-profile.png');
                $('#userName').text(data.name + ' ' + data.surname);
                $('#userEmail').text(data.email);
                $('#userLocation').text(data.location || 'Nav norādīts');
                $('#userCreatedAt').text(new Date(data.created_at).toLocaleDateString('lv-LV'));
                
                // Update statistics
                $('#createdEvents').text(data.created_events);
                $('#completedEvents').text(data.completed_events);
                
                // Show modal
                new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
            },
            error: function() {
                alert('Kļūda ielādējot lietotāja informāciju');
            }
        });
    }

    $(document).on('change', '.select-all', function () {
        const table = $(this).data('table');
        $(`#${table}-table .user-checkbox`).prop('checked', this.checked);
    });


    $(document).on('click', '.batch-update-btn', function () {
        const table = $(this).data('table');
        const newStatus = $(`.batch-status[data-table="${table}"]`).val();

        const selectedIds = $(`#${table}-table .user-checkbox:checked`).map(function () {
            return $(this).data('id');
        }).get();

        if (selectedIds.length === 0) {
            alert('Nav izvēlēts neviens lietotājs!');
            return;
        }

        $.ajax({
            url: '../functions/event_functions.php',
            method: 'POST',
            data: {
                action: 'batch_update_status',
                ids: selectedIds,
                status: newStatus
            },
            success: function (response) {
                if (response.trim() === 'success') {
                    loadJoinedUsers();
                } else {
                    alert('Kļūda: ' + response);
                }
            }
        });
    });

    $(document).on('change', '.status-select', function () {
        const volunteerId = $(this).data('id');
        const newStatus = $(this).val();

         $.ajax({
            url: '../functions/event_functions.php',
            method: 'POST',
            data: {
                action: 'update_volunteer_status',
                volunteer_id: volunteerId,
                status: newStatus
            },
            success: function (response) {
                if (response.trim() === 'success') {
                    loadJoinedUsers(); 
                } else {
                    alert('Kļūda atjauninot statusu: ' + response);
                }
            }
        });
    });
    
});


//Event Filtering and Search
// -------------------------
// HANDLE FILTER
// -------------------------
$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category_id');
    const city = urlParams.get('city');
    const dateFrom = urlParams.get('date_from');
    const dateTo = urlParams.get('date_to');
    const searchInput = $('#search_input'); 

    if (categoryId) {
        $('#filter_category').val(categoryId);
    }

    if (city) {
        $('#city').val(city);
    }

    if (dateFrom) {
        $('#date_from').val(dateFrom);
    }

    if (dateTo) {
        $('#date_to').val(dateTo);
    }

   
    $.ajax({
        url: '../functions/get_categories.php',
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (data) {
            $('#filter_category').append(data);
            if (categoryId) {
                $('#filter_category').val(categoryId); 
                $('#filter_category').trigger('change');
            }
        },
        error: function () {
            $('#filter_category').html('<option>Kļūda, ielādējot kategorijas</option>');
        }
    });

    
    searchInput.on('input', function () {
        const selectedCatId = $('#filter_category').val();
        const selectedCity = $('#city').val();
        const selectedDateFrom = $('#date_from').val();
        const selectedDateTo = $('#date_to').val();
        const searchTerm = searchInput.val();  
    
        loadEvents(selectedCatId, selectedCity, selectedDateFrom, selectedDateTo, searchTerm);
    });
    


    $('#filter_category, #city, #date_from, #date_to').on('change', function () {
        const selectedCatId = $('#filter_category').val();
        const selectedCity = $('#city').val();
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();

        const newUrl = new URL(window.location);
        newUrl.searchParams.set('category_id', selectedCatId);
        newUrl.searchParams.set('city', selectedCity);
        newUrl.searchParams.set('date_from', dateFrom);
        newUrl.searchParams.set('date_to', dateTo);
        newUrl.searchParams.delete('page'); 
        window.history.pushState({}, '', newUrl);  

        loadEvents(selectedCatId, selectedCity, dateFrom, dateTo);
    });

    $('#clear_filters').on('click', function () {
        $('#filter_form')[0].reset();
        
        const newUrl = new URL(window.location);
        newUrl.searchParams.delete('category_id');
        newUrl.searchParams.delete('city');
        newUrl.searchParams.delete('date_from');
        newUrl.searchParams.delete('date_to');
        window.history.pushState({}, '', newUrl);

        loadEvents('', '', '', '', '');
    });

    function loadEvents(catId, city, dateFrom, dateTo, searchTerm = '') {
        $.ajax({
            url: '../functions/get_events_by_category.php',
            method: 'GET',
            data: {
                category_id: catId || '',
                city: city || '',
                date_from: dateFrom || '',
                date_to: dateTo || '',
                search: searchTerm || ''  
            },
            success: function (data) {
                $('#events').html(data);
            },
            error: function () {
                $('#events').html('<p>Kļūda, ielādējot pasākumus.</p>');
            }
        });
    }

    if (categoryId || city || dateFrom || dateTo) {
        loadEvents(categoryId, city, dateFrom, dateTo);
    } else {
        loadEvents('', '', '', '', ''); 
    }
});



// Event Detail Page - Join/Leave Functionality
// -------------------------
// post-event.php join
// -------------------------
$(document).ready(function() {
    const eventId = APP_DATA.eventId; 
    
    function updateButton(status) {
        if (status === 'waiting' || status === 'accepted') {
            $('#applyButton').text('Atcelt dalību').removeClass('btn-success').addClass('btn-danger');
        } else {
            $('#applyButton').text('Pieteikties').removeClass('btn-danger').addClass('btn-success');
        }
    }

    function checkIfJoined() {
        if (!eventId) {
            console.warn('No event ID found');
            return;
        }

        $.ajax({
            url: '../functions/event_functions.php',
            method: 'POST',
            data: {
                user_id: userId,
                event_id: eventId,
                action: 'check'
            },
            success: function(response) {
                if (response === 'waiting' || response === 'accepted') {
                    updateButton('waiting'); 
                } else if (response === 'denied' || response === 'left') {
                    updateButton('denied'); 
                }
            }
        });
    }

    $('#applyButton').click(function() {
        if (!eventId) {
            console.warn('No event ID found');
            return;
        }

        if (userId === null) {
            alert("Lūdzu, piesakieties, lai pievienotos pasākumam.");
            return;
        }

        const currentText = $(this).text().trim();
        let action = ''; 

        if (currentText === 'Pieteikties') {
            action = 'join';
        } else if (currentText === 'Atcelt dalību') {
            action = 'leave';
        }

        $.ajax({
            url: '../functions/event_functions.php',
            method: 'POST',
            data: {
                user_id: userId,
                event_id: eventId,
                action: action
            },
            success: function(response) {
                if (response === 'joined') {
                    updateButton('waiting');
                } else if (response === 'left') {
                    updateButton('left'); 
                } else {
                    alert('Kļūda: Neizdevās pieteikties vai atcelt dalību.');
                }
            }
        });
    });

    if (userId !== null && eventId) {
        checkIfJoined();
    }
});




$(document).ready(function() {
    let retryCount = 0;
    const maxRetries = 3;
    const retryDelay = 1000; 
    let lastUpdateTime = 0;

    function loadStats() {
        
        $('#post-count').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#joined-count').html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '../functions/UserController.php',
        method: 'GET',
            data: { 
                action: 'get_stats',
                _: new Date().getTime() 
            },
            dataType: 'json',
            timeout: 5000, 
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
    })
    .done(function(response) {
        if (response.error) {
            console.error('Error fetching stats:', response.error);
                retryLoad();
            return;
        }

          
            if (response.timestamp > lastUpdateTime) {
                lastUpdateTime = response.timestamp;
                
              
                updateStatWithAnimation('#post-count', response.events || '0');
                updateStatWithAnimation('#joined-count', response.volunteers || '0');
            }
          
            retryCount = 0;
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX error:', textStatus, errorThrown);
            retryLoad();
        });
    }

    function updateStatWithAnimation(elementId, value) {
        const element = $(elementId);
        const currentValue = parseInt(element.text()) || 0;
        const newValue = parseInt(value) || 0;
        
        if (currentValue !== newValue) {
            element.fadeOut(200, function() {
                element.text(value).fadeIn(200);
            });
        }
    }

    function retryLoad() {
        if (retryCount < maxRetries) {
            retryCount++;
            console.log(`Retrying stats load (attempt ${retryCount}/${maxRetries})...`);
            setTimeout(loadStats, retryDelay * retryCount);
        } else {
            console.error('Failed to load stats after maximum retries');
            // Show error state instead of 0
            $('#post-count').html('<i class="fas fa-exclamation-circle" title="Failed to load"></i>');
            $('#joined-count').html('<i class="fas fa-exclamation-circle" title="Failed to load"></i>');
        }
    }

    loadStats();

    
    setInterval(loadStats, 15000);

    $(window).on('focus', function() {
        loadStats();
    });
});


// |||||||||||||
// ||  CHAT  ||
// |||||||||||||
$(document).ready(function() {
    const chatSidebar = $('#chatSidebar');
    const chatMessages = $('#chatMessages');
    const chatInput = $('#chatInput');
    const userId = APP_DATA.userId;
    const eventUserId = APP_DATA.eventUserId;
    const eventId = APP_DATA.eventId;


    $('#msgButton').click(function() {
        if (!userId) {
            alert('Lūdzu, piesakieties, lai rakstītu ziņu.');
            return;
        }
        chatSidebar.show();
        loadChatMessages();
    });

    $('#closeChatBtn').click(function() {
        chatSidebar.hide();
    });

    $('#sendChatBtn').click(function() {
        sendMessage();
    });

    chatInput.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

function loadChatMessages() {
    chatMessages.html('<p>Čats ielādējas...</p>');

    $.ajax({
        url: '../functions/chat_functions.php',
        method: 'POST',
         data: {
            action: 'fetch_messages',
            user1: userId,
            user2: eventUserId,
            event_id: eventId  
        },
        success: function(response) {
            try {
                const json = JSON.parse(response);
                if (json.status === 'success') {
                    chatMessages.empty();
                    json.messages.forEach(msg => {
                        const isUser = msg.from_user_id == userId;
                        const msgClass = isUser ? 'user' : 'other';
                        chatMessages.append(`
                            <div class="message ${msgClass}">
                                <div class="text">${escapeHtml(msg.message)}</div>
                            </div>
                        `);
                    });
                    chatMessages.scrollTop(chatMessages[0].scrollHeight);
                } else {
                    chatMessages.html('<p>Kļūda ielādējot ziņas.</p>');
                    console.error(json.message);
                }
            } catch (e) {
                console.error("Kļūda parsējot servera atbildi", e, response);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX kļūda:", error, xhr.responseText);
            chatMessages.html('<p>Neizdevās ielādēt čatu.</p>');
        }
    });
}

function sendMessage() {
    const msg = chatInput.val().trim();
    if (!msg) return;
    chatMessages.append(`<div class="message user"><div class="text">${escapeHtml(msg)}</div></div>`);
    chatInput.val('');
    chatMessages.scrollTop(chatMessages[0].scrollHeight);

    $.ajax({
        url: '../functions/chat_functions.php',
        method: 'POST',
        data: {
            action: 'send_message',
            from_user: userId,
            to_user: eventUserId,
            event_id: eventId, 
            message: msg
        },
        success: function(response) {
            console.log("Server response:", response);
            try {
                const json = JSON.parse(response);
                if (json.status !== 'success') {
                    console.warn("Send failed:", json.message);
                }
            } catch (e) {
                console.error("Invalid JSON from server", e, response);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", error, xhr.responseText);
            alert('Kļūda, ziņa netika nosūtīta.');
        }
    });
}


    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }
});
// ----- REPORT -----
$(document).ready(function () {
    // Show modal
    $("#reportBtn").click(function () {
        $("#reportModalOverlay").show();
        $("#reportModal").show();
    });

  
    $("input[name='reportReason'], #reportReason").change(function () {
        const reason = $(this).val();
        if (reason === "Citi") {
            $("#reportCustomReason").show();
        } else {
            $("#reportCustomReason").hide();
        }
    });

    $("#cancelReport, #reportModalOverlay").click(function () {
        closeReportModal();
    });

   
    $("#submitReport").click(function () {
        let selectedReason;

    
        if ($("input[name='reportReason']").length > 0) {
            selectedReason = $("input[name='reportReason']:checked").val();
        } else {
            selectedReason = $("#reportReason").val();
        }

        let customReason = $("#reportCustomReason").val().trim();

        if (!selectedReason) {
            showNotification("Lūdzu, izvēlieties iemeslu!", "error");
            return;
        }

        let finalReason = selectedReason;
        if (selectedReason === "Citi") {
            if (!customReason) {
                showNotification("Lūdzu, aprakstiet iemeslu!", "error");
                return;
            }
            finalReason = "Citi: " + customReason;
        }

        $.ajax({
            url: '../functions/event_functions.php',
            method: 'POST',
            data: {
                action: 'report',
                event_id: eventId,
                user_id: userId,
                reason: finalReason
            },
            success: function (response) {
                if (response === "success") {
                    showNotification("Paldies! Jūsu ziņojums ir saņemts.");
                    closeReportModal();
                } else {
                    showNotification("Kļūda: " + response, "error");
                }
            },
            error: function () {
                showNotification("Neizdevās iesniegt ziņojumu.", "error");
            }
        });
    });

    function closeReportModal() {
        $("#reportModal").hide();
        $("#reportModalOverlay").hide();
        $("input[name='reportReason']").prop("checked", false);
        $("#reportReason").val(""); // For select dropdown
        $("#reportCustomReason").val("").hide();
    }


    function showNotification(message, type = "success") {
        const notification = $("#notification");
        const icon = notification.find(".notification-icon");
        const messageEl = notification.find(".notification-message");


        icon
            .removeClass("fas fa-check fas fa-exclamation-circle")
            .addClass(type === "success" ? "fas fa-check" : "fas fa-exclamation-circle");


        messageEl.text(message);

        notification.removeClass("success error").addClass(type).addClass("show");

        setTimeout(() => {
            notification.removeClass("show");
        }, 3000);
    }
});

// ||||||||||||||
// || SETTINGS ||
// ||||||||||||||

$(document).ready(function() {
   $('#profileForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '../functions/upload_profile_pic.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                alert("Veiksmīgi Augšupielādēji!");
                location.reload();
            } else {
                alert('Kļūda: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Kļūda: ' + xhr.responseText);
        }
    });
});

    $('#toggleEmailPassword').click(function() {
        const input = $('#emailPassword');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });


    $('#editMainButton').click(function() {
        $('#username, #name, #surname').prop('readonly', false).addClass('bg-light');
        $('#editMainButton').hide();
        $('#saveMainButton').show();
    });

    $('#saveMainButton').click(function() {
        $.post('../functions/UserController.php', {
            action: 'update_main',
            username: $('#username').val(),
            name: $('#name').val(),
            surname: $('#surname').val()
        }, function(response) {
            if (response.success) {
                alert('Informācija saglabāta!');
                $('#username, #name, #surname').prop('readonly', true).removeClass('bg-light');
                $('#saveMainButton').hide();
                $('#editMainButton').show();
            } else {
                alert('Kļūda: ' + response.error);
            }
        }, 'json').fail(function(xhr) {
            alert('Kļūda: ' + (xhr.responseJSON?.error || xhr.responseText));
        });
    });

    
    $('#editEmailButton').click(function() {
        $('#email').prop('readonly', false).addClass('bg-light');
        $('#emailPasswordGroup').show();
        $('#editEmailButton').hide();
        $('#saveEmailButton').show();
    });

    $('#saveEmailButton').click(function() {
        const password = $('#emailPassword').val();
        if (!password) {
            alert("Lūdzu ievadiet paroli!");
            return;
        }

        $.post('../functions/UserController.php', {
            action: 'update_email',
            email: $('#email').val(),
            password: password
        }, function(response) {
            if (response.success) {
                alert(response.message);
                $('#email').prop('readonly', true).removeClass('bg-light');
                $('#emailPasswordGroup').hide();
                $('#emailPassword').val('');
                $('#saveEmailButton').hide();
                $('#editEmailButton').show();
            } else {
                alert('Kļūda: ' + response.error);
            }
        }, 'json').fail(function(xhr) {
            alert('Kļūda: ' + (xhr.responseJSON?.error || xhr.responseText));
        });
    });


    $('#editLocationButton').click(function() {
        $('#location').prop('readonly', false).addClass('bg-light');
        $('#editLocationButton').hide();
        $('#saveLocationButton').show();
    });

    $('#saveLocationButton').click(function() {
        $.post('../functions/UserController.php', {
            action: 'update_location',
            location: $('#location').val()
        }, function(response) {
            if (response.success) {
                alert('Atrašanās vieta atjaunināta!');
                $('#location').prop('readonly', true).removeClass('bg-light');
                $('#saveLocationButton').hide();
                $('#editLocationButton').show();
            } else {
                alert('Kļūda: ' + response.error);
            }
        }, 'json').fail(function(xhr) {
            alert('Kļūda: ' + (xhr.responseJSON?.error || xhr.responseText));
        });
    });
});

