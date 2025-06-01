<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notikums</title>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>
</head>
<body id="bg">

<section id="my-event">
    <div class="container p-0 m-0">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>
        <input type="hidden" id="edit-event-id" value="<?= htmlspecialchars($_GET['id']) ?>">

        <div class="card shadow p-4" id="event-details">
           
        </div>
    </div>
</section>

<section id="my-event-info">
    <section id="my-event-info">
    <div class="group">
        <div class="amount-joined  p-4">
            <p id="count-waiting">0</p>
            <i class="fa-solid fa-people-group wait"></i>
            <h5>Pieteikušies</h5>
        </div>
        <div class="amount-joined  p-4">
            <p id="count-accepted">0</p>
            <i class="fa-solid fa-people-group"></i>
            <h5>Apstiprināti</h5>
        </div>
        <div class="amount-joined  p-4">
            <p id="count-denied">0</p>
            <i class="fa-solid fa-people-group den"></i>
            <h5>Noraidīti</h5>
        </div>
    </div>
</section>

</section>

<section id="table">
  <h4 class="mb-3">Pieteikušies</h4>
  <div class="d-flex mb-2">
    <select class="form-select me-2 batch-status" data-table="waiting" style="max-width: 200px;">
      <option disabled selected>Izvēlies statusu</option>
      <option value="accepted">Apstiprināt</option>
      <option value="denied">Noraidīt</option>
    </select>
    <button class="btn-stat batch-update-btn" data-table="waiting">Mainīt izvēlētajiem</button>
  </div>

  <div class="table-responsive">
    <table class="table table-hover" id="waiting-table">
      <thead>
        <tr>
          <th><input type="checkbox" class="select-all" data-table="waiting"></th>
          <th>Nr.</th>
          <th>Lietotājvārds</th>
          <th>E-pasts</th>
          <th>Status</th>
          <th>Darbības</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
  <div class="pagination-controls" id="waiting-pagination"></div>
  
  <div class="row mt-5">
    <div class="col-md-6 mb-4">
      <h4 class="mb-3">Apstiprināti</h4>
      <div class="d-flex mb-2">
        <select class="form-select me-2 batch-status" data-table="accepted" style="max-width: 200px;">
          <option disabled selected>Izvēlies statusu</option>
          <option value="waiting">Pieteicies</option>
          <option value="denied">Noraidīt</option>
        </select>
        <button class="btn-stat batch-update-btn" data-table="accepted">Mainīt izvēlētajiem</button>
      </div>

      <div class="table-responsive">
        <table class="table table-hover" id="accepted-table">
          <thead>
            <tr>
              <th><input type="checkbox" class="select-all" data-table="accepted"></th>
              <th>Nr.</th>
              <th>Lietotājvārds</th>
              <th>E-pasts</th>
              <th>Status</th>
              <th>Darbības</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div class="pagination-controls" id="accepted-pagination"></div>
    </div>

    <div class="col-md-6 mb-4">
      <h4 class="mb-3">Noraidītie</h4>
      <div class="d-flex mb-2">
        <select class="form-select me-2 batch-status" data-table="denied" style="max-width: 200px;">
          <option disabled selected>Izvēlies statusu</option>
          <option value="waiting">Pieteicies</option>
          <option value="accepted">Apstiprināt</option>
        </select>
        <button class="btn-stat batch-update-btn" data-table="denied">Mainīt izvēlētajiem</button>
      </div>

      <div class="table-responsive">
        <table class="table table-hover" id="denied-table">
          <thead>
            <tr>
              <th><input type="checkbox" class="select-all" data-table="denied"></th>
              <th>Nr.</th>
              <th>Lietotājvārds</th>
              <th>E-pasts</th>
              <th>Status</th>
              <th>Darbības</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div class="pagination-controls" id="denied-pagination"></div>
    </div>
  </div>
</section>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userDetailsModalLabel">Lietotāja informācija</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4 text-center">
            <img id="userProfilePic" src="" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
          </div>
          <div class="col-md-8">
            <h4 id="userName"></h4>
            <p><strong>E-pasts:</strong> <span id="userEmail"></span></p>
            <p><strong>Atrašanās vieta:</strong> <span id="userLocation"></span></p>
            <p><strong>Reģistrējies:</strong> <span id="userCreatedAt"></span></p>
          </div>
        </div>
        <hr>
        <div class="row mt-3">
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Izveidotie pasākumi</h5>
                <p class="card-text" id="createdEvents">0</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Pabeigtie pasākumi</h5>
                <p class="card-text" id="completedEvents">0</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aizvērt</button>
      </div>
    </div>
  </div>
</div>

<style>
:root {
    --primary-color: #4CAF50;
    --secondary-color: #f8f9fc;
    --text-color: #333;
    --border-color: #e0e0e0;
    --hover-color: #45a049;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --success-color: #28a745;
    --info-color: #17a2b8;
}

.table {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    width: 100%;
    margin-bottom: 1rem;
    background-color: #fff;
}

.table th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    border: none;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
}

.table-hover tbody tr:hover {
    background-color: rgba(76, 175, 80, 0.05);
    transition: background-color 0.2s ease;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.view-user {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.view-user:hover {
    background-color: var(--hover-color);
    border-color: var(--hover-color);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.form-select {
    border-radius: 6px;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    transition: border-color 0.2s ease;
}

.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.pagination-controls {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.pagination-controls button {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    background-color: white;
    color: var(--text-color);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.pagination-controls button:hover:not(:disabled) {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination-controls button:disabled {
    background-color: #f5f5f5;
    color: #999;
    cursor: not-allowed;
}

.modal-body img {
    border: 3px solid #dee2e6;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php include '../main/footer.php'; ?>

<script>
$(document).ready(function() {
    loadJoinedUsers();
});
</script>

</body>
</html>
