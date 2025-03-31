$(document).ready(function () {
    function fetchEvents() {
        $.get("../database/fetch_events.php", function (data) {
            $("#eventList").html(data);
        });
    }
    fetchEvents();

    let eventIdToDelete = null; 

    
    $(document).on("click", ".delete-btn", function () {
        eventIdToDelete = $(this).data("id");
        $('#confirmDeleteModal').modal('show');
    });

   
    $("#confirmDeleteBtn").click(function () {
        if (eventIdToDelete) {
           
            $.ajax({
                url: "../database/delete_event.php",  
                type: "POST",
                data: { id: eventIdToDelete },
                success: function (response) {
                    if (response === 'success') {
                        fetchEvents(); 
                        $('#confirmDeleteModal').modal('hide'); 
                    } else {
                        alert("Failed to delete the event.");
                    }
                }
            });
        }
    });

    $("#eventForm").submit(function (e) {
        e.preventDefault();
        $.post("../database/insert_event.php", $(this).serialize(), function () {
            fetchEvents();
            $("#eventForm")[0].reset();
        });
    });

    $(document).on("click", ".edit-btn", function () {
        let eventId = $(this).data("id");
        $.get("../database/get_event.php", { id: eventId }, function (data) {
            let event = JSON.parse(data);
            $("#edit_event_id").val(event.ID_Event);
            $("#edit_title").val(event.title);
            $("#edit_description").val(event.description);
            $("#edit_location").val(event.location);
            $("#edit_date").val(event.date);

            $("#editEventModal").modal("show");
        });
    });

    $("#editEventForm").submit(function (e) {
        e.preventDefault();
        $.post("../database/update_event.php", $(this).serialize(), function () {
            fetchEvents();
            $("#editEventModal").modal("hide");
        });
    });

    $("#toggleFormBtnCreate").click(function () {
        $("#eventForm").toggle(); 
        if ($("#eventForm").is(":visible")) {
            $(this).text("Aizvert"); 
        } else {
            $(this).text("Izveidot");  
        }
    });
});
$(document).ready(function () {
    
    $("#toggleFormBtn").click(function () {
        $("#editUserModal").modal("show");  
    });

    
    $("#editUserForm").submit(function (e) {
        e.preventDefault(); 

        $.ajax({
            url: "../database/update_users.php",  
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response === 'success') {
                    
                    alert("Profile updated successfully!");

                   
                    let name = $("input[name='name']").val();
                    let surname = $("input[name='surname']").val();
                    let bio = $("textarea[name='bio']").val();
                    let location = $("input[name='location']").val();

                   
                    $(".card-body .my-3").text(name + " " + surname);  
                    $(".user-info p strong:contains('Bio:')").next().text(bio);  
                    $(".user-info p strong:contains('Location:')").next().text(location);  

                  
                    $("#editUserForm input[name='name']").val(name);
                    $("#editUserForm input[name='surname']").val(surname);
                    $("#editUserForm textarea[name='bio']").val(bio);
                    $("#editUserForm input[name='location']").val(location);

                    $("#editUserModal").modal("hide"); 
                } else {
                    alert("Failed to update the profile. Please try again.");
                }
            },
            error: function () {
                alert("An error occurred while updating your profile.");
            }
        });
    });


    $("#profileInfoBtn").click(function () {
        $("#profileInfoContent").show();
        $("#passwordContent").hide();
        $("#deleteAccountContent").hide();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $("#passwordBtn").click(function () {
        $("#passwordContent").show();
        $("#profileInfoContent").hide();
        $("#deleteAccountContent").hide();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $("#deleteAccountBtn").click(function () {
        $("#deleteAccountContent").show();
        $("#profileInfoContent").hide();
        $("#passwordContent").hide();
        $(this).addClass('active').siblings().removeClass('active');
    });

    
    $("#profileInfoBtn").trigger('click');

   
    $("#changePasswordForm").submit(function (e) {
        e.preventDefault();

        const currentPassword = $("input[name='current_password']").val();
        const newPassword = $("input[name='new_password']").val();
        const confirmPassword = $("input[name='confirm_new_password']").val();

       
        if (newPassword !== confirmPassword) {
            alert("New passwords do not match.");
            return;
        }

       
        $.ajax({
            url: "../database/change_password.php", 
            type: "POST",
            data: {
                current_password: currentPassword,
                new_password: newPassword
            },
            success: function (response) {
                if (response === 'success') {
                    alert("Password changed successfully!");
                    $("#editUserModal").modal("hide");
                } else {
                    alert("Current password is incorrect or an error occurred.");
                }
            },
            error: function () {
                alert("An error occurred while changing your password.");
            }
        });
    });
});

