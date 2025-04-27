// Lietotāja profila pieteiktie un izveidotie sludinājumi
$(document).ready(function () {
    function loadEvents() {
        $.ajax({
            url: '../database/fetch_events.php', //Izvediotie sludinājumi
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
            url: '../database/fetch_joined_events.php', //Sludinājumi kur pieteicās
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

// Parāda izveidotos sludinājumus
    $(".sludinajumi-btn").click(function () {
        $(".event-container").show();
        $(".joined-container").hide();
        $(".action-btn button").removeClass("active");
        $(this).addClass("active");
        loadEvents();
    });

    //Parāda piteicies sludinājumus
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
    $(".pop-up-creat").hide();

    $(".create-btn button").click(function () {
        $(".pop-up-creat").fadeIn();
        loadCategories();
    });

    $(".close-btn").click(function () {
        $(".pop-up-creat").fadeOut();
    });

    $(document).click(function (event) {
        if (!$(event.target).closest(".pop-up-content, .create-btn button").length) {
            $(".pop-up-creat").fadeOut();
        }
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
    

    $("#event-form").submit(function (e) {
        e.preventDefault(); 

        let formData = {
            title: $("#event-title").val(),
            description: $("#event-description").val(),
            location: $("#event-location").val(),
            date: $("#event-date").val(),
            city: $("#event-city").val(),
            zip: $("#event-zip").val(),
            categories: $("#event-categories").val()
        };

        $.ajax({
            url: '../database/fetch_insert.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi izveidots!");
                    $(".pop-up-creat").fadeOut();
                    loadEvents();
                } else {
                    alert("Kļūda: " + response.message);
                }
            },
            error: function () {
                alert("Neizdevās saglabāt notikumu.");
            }
        });
    });

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

    loadEvents();
    setInterval(loadEvents, 30000);
});
// Editot savu izveidoto sludinājuma informāciju
$(document).ready(function () {
    let originalData = {};

    $(document).on("click", ".edit-event-btn.bi-pencil", function () {

        const dateText = $(".date").text().replace("🗓 Datums:", "").trim();
    
        originalData = {
            title: $(".title").text(),
            description: $(".description").html().replace(/<br\s*\/?>/g, "\n"),
            city: $(".location").text().split(":")[1]?.split("|")[0]?.trim(),
            zip: $(".location").text().split("Zip:")[1]?.trim(),
            date: dateText
        };
    
        $(".title").replaceWith(`<input type="text" class="form-control title" value="${originalData.title}">`);
        $(".description").replaceWith(`<textarea class="form-control description" rows="5">${originalData.description}</textarea>`);
        $(".location").replaceWith(`
            <div class="row location">
                <div class="col-md-6">
                    <input type="text" class="form-control city" placeholder="Pilsēta" value="${originalData.city}">
                </div>
                <div class="col-md-6">
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
        $(".location").replaceWith(`<p class="location"><strong>📍 Pilsēta:</strong> ${originalData.city} | Zip: ${originalData.zip}</p>`);
        $(".date").replaceWith(`<p class="date"><strong>🗓 Datums:</strong> ${originalData.date}</p>`);
        $(".edit-actions").hide();
    });

    $(document).on("click", ".save-edit", function () {
        const formData = {
            event_id: $("#edit-event-id").val(),
            title: $(".title").val(),
            description: $(".description").val(),
            city: $(".city").val(),
            zip: $(".zip").val(),
            location: $(".city").val(), 
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
                    $(".location").replaceWith(`<p class="location"><strong>📍 Pilsēta:</strong> ${formData.city} | Zip: ${formData.zip}</p>`);
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

    // Informācija par sludinājumu
    $.get(`../database/fetch_event_details.php?id=${eventId}`, function (data) {
        $('#event-details').html(data);
    });

    // Pieteikušo daudzums
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
// posts.php sludinājuma filtrēšana 
$(document).ready(function () {
    
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category_id');
    const city = urlParams.get('city');
    const dateFrom = urlParams.get('date_from');
    const dateTo = urlParams.get('date_to');
    let page = urlParams.get('page') || 1; 

   
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
        newUrl.searchParams.set('page', 1); 
        window.history.pushState({}, '', newUrl);  

        loadEvents(selectedCatId, selectedCity, dateFrom, dateTo, 1);
    });

   
    $('#clear_filters').on('click', function () {
        $('#filter_form')[0].reset();
        
        const newUrl = new URL(window.location);
        newUrl.searchParams.delete('category_id');
        newUrl.searchParams.delete('city');
        newUrl.searchParams.delete('date_from');
        newUrl.searchParams.delete('date_to');
        window.history.pushState({}, '', newUrl);

        loadEvents('', '', '', '', 1);
    });

    
    $(document).on('click', '.page-link', function () {
        const selectedPage = $(this).data('page'); 
        const selectedCatId = $('#filter_category').val();
        const selectedCity = $('#city').val();
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();

        
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('category_id', selectedCatId);
        newUrl.searchParams.set('city', selectedCity);
        newUrl.searchParams.set('date_from', dateFrom);
        newUrl.searchParams.set('date_to', dateTo);
        newUrl.searchParams.set('page', selectedPage);
        window.history.pushState({}, '', newUrl);  

        loadEvents(selectedCatId, selectedCity, dateFrom, dateTo, selectedPage); 
    });

    function loadEvents(catId, city, dateFrom, dateTo, page) {
        $.ajax({
            url: '../database/get_events_by_category.php',
            method: 'GET',
            data: {
                category_id: catId,
                city: city || '',
                date_from: dateFrom || '',
                date_to: dateTo || '',
                page: page
            },
            success: function (data) {
                $('#events').html(data);
            },
            error: function () {
                $('#events').html('<p>Kļūda, ielādējot pasākumus.</p>');
            }
        });
    }


    if (categoryId) {
        loadEvents(categoryId, city, dateFrom, dateTo, page);
    }
    
});

// Pieteikties sluninājumam
$(document).ready(function() {
   
    $('#applyButton').click(function() {
 
        if (userId === null) {
            alert("Lūdzu, piesakieties, lai pievienotos pasākumam.");
            return;
        }

        
        console.log('Sending data:', { user_id: userId, event_id: eventId });

        
        $.ajax({
            url: '../database/join_event.php',
            method: 'POST',
            data: {
                user_id: userId,
                event_id: eventId
            },
            success: function(response) {
                console.log('Response from server:', response);
                if (response === 'success') {
                    alert('Jūs esat veiksmīgi pieteicies uz šo pasākumu!');
                } else if (response === 'already_joined') {
                    alert('Jūs jau esat pieteicies uz šo pasākumu.');
                } else {
                    alert('Kļūda: nevarēja pieteikties. Atbilde no servera: ' + response);
                }
            }
        });
    });
});
