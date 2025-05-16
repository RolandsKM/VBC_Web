<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Create Event</h2>
    <form id="eventForm">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" class="form-control" name="location" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categories</label>
            <select class="form-control" name="categories[]" required>
    <option value="" disabled selected>Select a category</option>
    <?php
    include 'database/con_db.php';
    $result = $savienojums->query("SELECT * FROM VBC_Kategorijas");
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['Kategorijas_ID']}'>{$row['Nosaukums']}</option>";
    }
    ?>
</select>

        </div>
        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>

    <h2 class="mt-5">Events</h2>
    <div id="eventList" class="row"></div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" name="event_id" id="edit_event_id">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="edit_location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="edit_date" required>
                        </div>
                        <!-- <div class="mb-3">
                            <label class="form-label">Categories</label>
                            <select class="form-control" name="categories[]" id="edit_categories" multiple required>
                            </select>
                        </div> -->
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
        function fetchEvents() {
            $.get("database/fetch_events.php", function (data) {
                $("#eventList").html(data);
            });
        }
        fetchEvents();

        $("#eventForm").submit(function (e) {
            e.preventDefault();
            $.post("database/insert_event.php", $(this).serialize(), function () {
                fetchEvents();
                $("#eventForm")[0].reset();
            });
        });

        $(document).on("click", ".edit-btn", function () {
            let eventId = $(this).data("id");
            $.get("database/get_event.php", { id: eventId }, function (data) {
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
            $.post("database/update_event.php", $(this).serialize(), function () {
                fetchEvents();
                $("#editEventModal").modal("hide");
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
