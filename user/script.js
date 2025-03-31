$(document).ready(function () {
    function fetchEvents() {
        $.get("../database/fetch_events.php", function (data) {
            $("#eventList").html(data);
        });
    }
    fetchEvents();

    let eventIdToDelete = null; // Store the event ID that needs to be deleted

    // When the delete button is clicked, store the event ID and show the confirmation modal
    $(document).on("click", ".delete-btn", function () {
        eventIdToDelete = $(this).data("id");
        $('#confirmDeleteModal').modal('show');
    });

    // Confirm deletion when the "Delete" button in the modal is clicked
    $("#confirmDeleteBtn").click(function () {
        if (eventIdToDelete) {
            // Send delete request to the server
            $.ajax({
                url: "../database/delete_event.php",  // PHP script to handle deletion
                type: "POST",
                data: { id: eventIdToDelete },
                success: function (response) {
                    if (response === 'success') {
                        fetchEvents(); // Refresh event list after deletion
                        $('#confirmDeleteModal').modal('hide'); // Hide the confirmation modal
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

    // Toggle Create Event Form visibility when the "Izveidot" button is clicked
    $("#toggleFormBtnCreate").click(function () {
        $("#eventForm").toggle();  // This will show or hide the form
        if ($("#eventForm").is(":visible")) {
            $(this).text("Aizvert");  // Change button text to "Aizvert"
        } else {
            $(this).text("Izveidot");  // Change button text back to "Izveidot"
        }
    });
});
$(document).ready(function () {
    // Show the edit profile modal when the Settings button is clicked
    $("#toggleFormBtn").click(function () {
        $("#editUserModal").modal("show");  // Show the modal
    });

    // Handle the form submission for editing user information
    $("#editUserForm").submit(function (e) {
        e.preventDefault(); // Prevent the default form submission

        // Send an AJAX request to update the user data
        $.ajax({
            url: "../database/update_users.php",  // PHP script to handle the update
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response === 'success') {
                    // Optionally update the user profile on the page without reloading
                    alert("Profile updated successfully!");

                    // Dynamically update the profile data on the page
                    let name = $("input[name='name']").val();
                    let surname = $("input[name='surname']").val();
                    let bio = $("textarea[name='bio']").val();
                    let location = $("input[name='location']").val();

                    // Update the profile card with new data (name, surname, bio, location)
                    $(".card-body .my-3").text(name + " " + surname);  // Update name and surname
                    $(".user-info p strong:contains('Bio:')").next().text(bio);  // Update bio
                    $(".user-info p strong:contains('Location:')").next().text(location);  // Update location

                    // Also update the modal with the new values in case the user opens it again
                    $("#editUserForm input[name='name']").val(name);
                    $("#editUserForm input[name='surname']").val(surname);
                    $("#editUserForm textarea[name='bio']").val(bio);
                    $("#editUserForm input[name='location']").val(location);

                    $("#editUserModal").modal("hide"); // Close the modal after successful update
                } else {
                    alert("Failed to update the profile. Please try again.");
                }
            },
            error: function () {
                alert("An error occurred while updating your profile.");
            }
        });
    });

    // Switch between profile info, password change, and delete account sections
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

    // Initialize the modal by showing profile info content by default
    $("#profileInfoBtn").trigger('click');

    // Handle password change form submission
    $("#changePasswordForm").submit(function (e) {
        e.preventDefault();

        const currentPassword = $("input[name='current_password']").val();
        const newPassword = $("input[name='new_password']").val();
        const confirmPassword = $("input[name='confirm_new_password']").val();

        // Validate password fields
        if (newPassword !== confirmPassword) {
            alert("New passwords do not match.");
            return;
        }

        // Send password change request
        $.ajax({
            url: "../database/change_password.php",  // PHP script to handle the password change
            type: "POST",
            data: {
                current_password: currentPassword,
                new_password: newPassword
            },
            success: function (response) {
                if (response === 'success') {
                    alert("Password changed successfully!");
                    $("#editUserModal").modal("hide"); // Close the modal after password change
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

