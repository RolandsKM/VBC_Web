<?php
session_start();


if (!isset($_SESSION['username'])) {
    header("Location: ../main/login.php"); 
    exit();
}

include '../database/con_db.php';


$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = '$username'";
$result = $savienojums->query($query);
$user = $result->fetch_assoc();

include '../main/header.php';
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs - Profils</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<div id="app">
  <header>
      <h1>Vietējais Brīvprātīgais Centrs</h1>
      <nav>
          <ul>
              <li><a href="../main/index.php">Sākums</a></li>
              <li><a href="../main/category.php">Kategorijas</a></li>
              <li><a href="../main/about.php">Par Mums</a></li>

              <?php if (isset($_SESSION['username'])): ?>
                  <li class="dropdown">
                      <a href="#" class="dropbtn"><?= htmlspecialchars($_SESSION['username']) ?> ▼</a>
                      <div class="dropdown-content">
                          <a href="../user/profile.php">Profils</a>
                          <a href="../database/logout.php" class="text-danger">Izlogoties</a>
                      </div>
                  </li>
              <?php else: ?>
                  <li><a href="login.php">Pieslēgties</a></li>
              <?php endif; ?>
          </ul>
      </nav>
  </header>

  <section>
    <div class="container py-5">
      <div class="row d-flex align-items-stretch">
        <div class="col-md-4">
          <div class="card mb-4">
            <div class="card-body text-center">
            
              <img src="<?= $user['profile_pic'] ? $user['profile_pic'] : 'default-profile.png' ?>" 
                alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
              <h5 class="my-3"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></h5>
              <p class="text-muted mb-1"><?= htmlspecialchars($user['bio']) ?></p>
              <p class="text-muted mb-4"><?= htmlspecialchars($user['location']) ?></p>
              <div class="d-flex justify-content-center mb-2">
                <button type="button" class="btn btn-outline-primary ms-1">Rakstīt</button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="card mb-4">
            <div class="card-body">
              <button class="settings-btn" id="toggleFormBtn">Settings</button>
              <div class="user-info">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>First Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Last Name:</strong> <?= htmlspecialchars($user['surname']) ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($user['location']) ?></p>
                <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio']) ?></p>
                <div class="social-links">
                  <a href="<?= htmlspecialchars($user['social_links']) ?>" target="_blank">Social Profile</a>
                </div>
            </div>

            </div>
          </div>

          <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div class="d-flex gap-3">
                  <button type="button" class="btn btn-link" id="showCreatedEventsBtn">Sludinājumi</button>
                  <button type="button" class="btn btn-link" id="showSignedUpEventsBtn">Pieteicies</button>
              </div>
              <button class="btn btn-primary" id="toggleFormBtnCreate">Izveidot</button>
            </div>
          </div>
        </div>
    </div>
  </section>

  
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this event? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  
  <h2>Create Event</h2>
  <form id="eventForm" style="display: none;">
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
                      <button type="submit" class="btn btn-success">Save Changes</button>
                  </form>
              </div>
          </div>
      </div>
  </div>
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Settings Content -->
                    <div class="col-md-12" id="settingsContent">
                        <div class="row">
                            <!-- Sidebar -->
                            <nav id="settingsSidebar" class="col-md-3 col-lg-2 sidebar bg-white">
                                <div class="position-sticky">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item py-2">
                                            <button class="btn btn-link w-100 text-start" id="profileInfoBtn">
                                                <i class="fas fa-user me-2"></i>Profila informācija
                                            </button>
                                        </li>
                                        <li class="list-group-item py-2">
                                            <button class="btn btn-link w-100 text-start" id="passwordBtn">
                                                <i class="fas fa-lock me-2"></i>Parole
                                            </button>
                                        </li>
                                        <li class="list-group-item py-2">
                                            <button class="btn btn-link w-100 text-start text-danger" id="deleteAccountBtn">
                                                <i class="fas fa-trash-alt me-2"></i>Dzēst kontu
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                         
                            <div class="col-md-9 col-lg-10">
                              
                                <div id="profileInfoContent">
                                    <form id="editUserForm">
                                        <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">

                                        <div class="mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="surname" value="<?= htmlspecialchars($user['surname']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Bio</label>
                                            <textarea class="form-control" name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($user['location']) ?>">
                                        </div>

                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                    </form>
                                </div>

                                
                                <div id="passwordContent" style="display: none;">
                                    <form id="changePasswordForm">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" class="form-control" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" name="confirm_new_password" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Change Password</button>
                                    </form>
                                </div>

                              
                                <div id="deleteAccountContent" style="display: none;">
                                    <p class="text-danger">Are you sure you want to delete your account? This action is irreversible.</p>
                                    <button class="btn btn-danger" id="confirmDeleteAccountBtn">Yes, Delete Account</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
