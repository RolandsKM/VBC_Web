
$(document).ready(function () {
    function loadEvents() {
        $.ajax({
            url: '../database/fetch_events.php', 
            type: 'GET',
            success: function (response) {
                $(".event-container").html(response);
            },
            error: function () {
                alert("Kļūda: Neizdevās ielādēt notikumus.");
            }
        });
    }

    function loadJoinedEvents() {
        $.ajax({
            url: '../database/fetch_joined_events.php', 
            type: 'GET',
            success: function (response) {
                $(".joined-container").html(response);
            },
            error: function () {
                alert("Kļūda: Neizdevās ielādēt pieteiktos notikumus.");
            }
        });
    }

    $(".event-container").show();
    $(".joined-container").hide();
    $(".action-btn button").removeClass("active");
    $(".sludinajumi-btn").addClass("active");


    $(".sludinajumi-btn").click(function () {
        $(".event-container").show();
        $(".joined-container").hide();
        $(".action-btn button").removeClass("active");
        $(this).addClass("active");
        loadEvents();
    });

    
    $(".pieteicies-btn").click(function () {
        $(".event-container").hide();
        $(".joined-container").show();
        $(".action-btn button").removeClass("active");
        $(this).addClass("active");
        loadJoinedEvents(); 
    });

  
    loadEvents();

    
    setInterval(loadEvents, 30000);
});
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
            url: '../database/create_event.php',
            type: 'POST',
            data: eventData,
            success: function (response) {
                console.log(response); 
                if (response === "success") {
                    alert("Pasākums izveidots veiksmīgi!");
                  
                    $("#event-form")[0].reset();
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
            url: '../database/get_categories.php', 
            type: 'GET',
            success: function (response) {
                $("#event-categories").html(response); 
            },
            error: function () {
                alert("Kļūda: Neizdevās ielādēt kategorijas.");
            }
        });
    }
});


// Editot savu izveidoto sludinājuma informāciju
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
        $(".location").replaceWith(`<p class="location"><strong>📍 Pilsēta:</strong> ${originalData.city}, ${originalData.location} | Zip: ${originalData.zip}</p>`);
        $(".date").replaceWith(`<p class="date"><strong>🗓 Datums:</strong> ${originalData.date}</p>`);
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
            url: '../database/update_event.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi atjaunināts!");

                    $(".title").replaceWith(`<h1 class="title">${formData.title}</h1>`);
                    $(".description").replaceWith(`<p class="description">${formData.description.replace(/\n/g, "<br>")}</p>`);
                    $(".location").replaceWith(`<p class="location"><strong>📍 Pilsēta:</strong> ${formData.city}, ${formData.location} | Zip: ${formData.zip}</p>`);
                    $(".date").replaceWith(`<p class="date"><strong>🗓 Datums:</strong> ${formatDateTime(formData.date)}</p>`);

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
            year: "numeric", month: "2-digit", day: "2-digit",
            hour: "2-digit", minute: "2-digit"
        });
    }

    function convertToInputDatetime(lvDateString) {
        const parts = lvDateString.match(/(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2})/);
        if (!parts) return '';
        const [, day, month, year, hour, minute] = parts;
        return `${year}-${month}-${day}T${hour}:${minute}`;
    }
});


// Dzēst ārā sludinājumu (nomainīs 0 uz 1 datbāzē)
$(document).ready(function () {
    $(document).on("click", ".edit-event-btn.bi-trash", function () {

        if (!confirm("Vai tiešām vēlies dzēst šo notikumu?")) return;

        const eventId = $("#edit-event-id").val();

        $.ajax({
            url: '../database/delete_event.php',
            type: 'POST',
            data: { event_id: eventId },
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi dzēsts!");
                    window.location.href = "user.php"; 
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

    
    $.get(`../database/fetch_event_details.php?id=${eventId}`, function (data) {
        $('#event-details').html(data);
    });

    
    $.getJSON(`../database/fetch_event_info.php?id=${eventId}`, function (data) {
        $('#joined-count').text(data.total_joined);
    });
    $.getJSON(`../database/fetch_joined_users.php?id=${eventId}`, function (data) {
        const tableBody = $('#joined-users-table');
        tableBody.empty();
        
        if (data.length === 0) {
            tableBody.append(`<tr><td colspan="4">Nav pieteikušos lietotāju.</td></tr>`);
        } else {
            data.forEach((user, index) => {
                tableBody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${user.status}</td>
                    </tr>
                `);
            });
        }
    });
    
});
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
        url: '../database/get_categories.php',
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
        const searchTerm = searchInput.val();  // Get the current input
    
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
            url: '../database/get_events_by_category.php',
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




$(document).ready(function() {
    
    function updateButton(status) {
        if (status === 'waiting' || status === 'accepted') {
            $('#applyButton').text('Atcelt dalību').removeClass('btn-success').addClass('btn-danger');
        } else {
            $('#applyButton').text('Pieteikties').removeClass('btn-danger').addClass('btn-success');
        }
    }

    
    function checkIfJoined() {
        $.ajax({
            url: '../database/check_join_status.php',
            method: 'POST',
            data: {
                user_id: userId,
                event_id: eventId
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
            url: '../database/join_event.php',
            method: 'POST',
            data: {
                user_id: userId,
                event_id: eventId,
                action: action
            },
            success: function(response) {
                if (response === 'joined') {
                    alert('Jūs esat veiksmīgi pieteicies!');
                    updateButton('waiting');
                } else if (response === 'left') {
                    alert('Jūs esat atcēlis dalību.');
                    updateButton('left'); 
                } else {
                    alert('Kļūda: ' + response);
                }
            }
        });
    });

    
    if (userId !== null) {
        checkIfJoined();
    }
});


