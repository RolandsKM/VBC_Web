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

<table class="table table-striped" id="waiting-table">
  <thead>
    <tr>
      <th><input type="checkbox" class="select-all" data-table="waiting"></th>
      <th>Nr.</th>
      <th>Lietotājvārds</th>
      <th>E-pasts</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>

  </tbody>
</table>

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

      <table class="table table-striped" id="accepted-table">
        <thead>
          <tr>
            <th><input type="checkbox" class="select-all" data-table="accepted"></th>
            <th>Nr.</th>
            <th>Lietotājvārds</th>
            <th>E-pasts</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
      
        </tbody>
      </table>
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

      <table class="table table-striped" id="denied-table">
        <thead>
          <tr>
            <th><input type="checkbox" class="select-all" data-table="denied"></th>
            <th>Nr.</th>
            <th>Lietotājvārds</th>
            <th>E-pasts</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
    
        </tbody>
      </table>
      <div class="pagination-controls" id="denied-pagination"></div>
    </div>
  </div>
</section>

<?php include '../main/footer.php'; ?>


</body>
</html>
