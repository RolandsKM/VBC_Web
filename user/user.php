

<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php';  ?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="user-style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #user {
                
                padding: 5rem 0 0;
            }
    </style>
</head>
<body id="user">

<section id="profile-header" class="py-4">
    <div class="container">
        <div class="profile-card">
            <div class="profile-main">


                <div class="profile-avatar">
                  <img src="../functions/assets/<?= htmlspecialchars($_SESSION['profile_pic']) ?>">


                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($_SESSION['username']) ?></h1>
                    <p class="text-muted"><?= htmlspecialchars($_SESSION['email']) ?></p>
                </div>
                <div class="profile-actions">
                    <a href="messages.php" class="btn-action" title="Ziņas">
                        <i class="bi bi-chat-dots"></i>
                    </a>
                    <a href="account_info.php" class="btn-action" title="Iestatījumi">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number" id="post-count">0</span>
                    <span class="stat-label">Sludinājumi</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="joined-count">0</span>
                    <span class="stat-label">Pieteikumi</span>
                </div>
            </div>


             
        </div>
    </div>
</section>

<!-- Events Section -->
<section id="event" class="py-3">
    <div class="container">

        <div class="button-box">
            <div class="action-btn">
                <button class="sludinajumi-btn active">Sludinājumi</button>
                <button class="pieteicies-btn">Pieteicies</button>
            </div>

            <div class="create-btn">
                <a href="create.php" class="btn">Izveidot</a>
            </div>
        </div>

        <div class="event-container active">
            <div class="events-grid" id="own-events-grid">
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <p>Nav sludinājuma</p>
                </div>
            </div>
            <div class="text-center mt-3 load-btn">
                <button id="load-more-own" class="btn">Ielādēt vēl</button>
            </div>
        </div>

        <div class="joined-container">
            <div class="events-grid" id="joined-events-grid">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Pagaidām nav pieteikumu</p>
                </div>
            </div>
            <div class="text-center mt-3 load-btn">
                <button id="load-more-joined" class="btn">Ielādēt vēl</button>
            </div>


        </div>
    </div>
    
</section>


<style>

</style>

<script>
  const icon = document.querySelector('.msg-btn i');

  document.querySelector('.msg-btn').addEventListener('mouseenter', () => {
    icon.classList.remove('bi-chat-dots-fill');
    icon.classList.add('bi-chat-dots');
  });

  document.querySelector('.msg-btn').addEventListener('mouseleave', () => {
    icon.classList.remove('bi-chat-dots');
    icon.classList.add('bi-chat-dots-fill');
  });


</script>

<?php include '../css/templates/footer.php'; ?>
</body>
</html>


