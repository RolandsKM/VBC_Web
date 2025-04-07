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

  
    $(".event-container").show();
    $(".joined-container").hide();
    $(".action-btn button").removeClass("active");
    $(".sludinajumi-btn").addClass("active");

    // Click event for "Sludinājumi"
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

$(document).ready(function () {
    $(".edit-pop-up").hide();

    $(".edit-event-btn").click(function () {
        $(".edit-pop-up").fadeIn();
    });

    $(".close-edit-btn").click(function () {
        $(".edit-pop-up").fadeOut();
    });

    $("#edit-event-form").submit(function (e) {
        e.preventDefault();

        let formData = {
            event_id: $("#edit-event-id").val(),
            title: $("#edit-event-title").val(),
            description: $("#edit-event-description").val(),
            location: $("#edit-event-location").val(),
            city: $("#edit-event-city").val(),
            zip: $("#edit-event-zip").val(),
            date: $("#edit-event-date").val()
        };

        $.ajax({
            url: '../database/update_event.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === "success") {
                    alert("Notikums veiksmīgi atjaunināts!");

                
                    $(".title").text(formData.title);
                    $(".description p").text(formData.description);
                    $(".location").text(formData.location + ", " + formData.zip);
                    $(".event-info p:first").text(formData.city);
                    $(".event-info p:last").text("Datums/Laiks: " + formatDate(formData.date));

                    $(".edit-pop-up").fadeOut();
                } else {
                    alert("Kļūda: " + response.message);
                }
            },
            error: function () {
                alert("Neizdevās atjaunināt notikumu.");
            }
        });
    });

    function formatDate(inputDate) {
        let dateObj = new Date(inputDate);
        return dateObj.toLocaleDateString("lv-LV", { year: 'numeric', month: '2-digit', day: '2-digit' });
    }
});
$(document).ready(function () {
    $(".edit-pop-up").hide();

    
    $(".edit-event-btn").click(function () {
        $(".edit-pop-up").fadeIn();
    });

    $(".close-edit-btn").click(function () {
        $(".edit-pop-up").fadeOut();
    });

    
    $(".bi-trash").click(function () {
        var eventId = $("#edit-event-id").val();

        if (confirm("Vai jūs tiešām vēlaties dzēst šo notikumu?")) {
            $.ajax({
                url: '../database/delete_event.php',  
                type: 'POST',
                data: { event_id: eventId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === "success") {
                        alert("Notikums ir veiksmīgi dzēsts!");
                        window.location.href = 'user.php';  
                    } else {
                        alert("Kļūda: " + response.message);
                    }
                },
                error: function () {
                    alert("Neizdevās dzēst notikumu.");
                }
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

