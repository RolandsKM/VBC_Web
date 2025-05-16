<?php 

include '../database/admin_stats.php';
?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC-Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <?php include 'header.php'; ?>
<!-- BODY----------------------------------------------------------------------- -->
        <div class="admin-body ">
            
            <div class="card-box px-4 py-3 d-flex flex-wrap gap-4">

            <div class="stat-card d-flex justify-content-between align-items-center p-3">
                <div class="text-start">
                    <h4>Lietotāji (Kopā)</h4>
                    <p><?php echo $total_users; ?></p>
                </div>
                <i class="fas fa-users fa-2x"></i>
            </div>

            <div class="stat-card d-flex justify-content-between align-items-center p-3">
                <div class="text-start">
                    <h4>Sludinājumi (Šodien)</h4>
                    <p><?php echo $today_events; ?></p>
                </div>
                <i class="fas fa-file-alt fa-2x"></i>
            </div>

            <div class="stat-card d-flex justify-content-between align-items-center p-3">
                <div class="text-start">
                    <h4>Sludinājumi (Kopā)</h4>
                    <p><?php echo $total_events; ?></p>
                </div>
                <i class="fas fa-calendar-alt fa-2x"></i>
            </div>

            <div class="stat-card d-flex justify-content-between align-items-center p-3">
                <div class="text-start">
                    <h4>Pieteikušies (Šodien)</h4>
                    <p><?php echo $today_volunteers; ?></p>
                </div>
                <i class="fas fa-user-check fa-2x"></i>
            </div>

            </div>
            <div class="card p-4 my-4">
                <h4 class="mb-3">Statistikas Grafiks</h4>
                <canvas id="statsChart" height="100"></canvas>
            </div>

        </div>

    </div>
</div>
<script>
const ctx = document.getElementById('statsChart').getContext('2d');
const statsChart = new Chart(ctx, {
    type: 'bar', 
    data: {
        labels: ['Lietotāji', 'Sludinājumi (Šodien)', 'Sludinājumi (Kopā)', 'Pieteikušies (Šodien)'],
        datasets: [{
            label: 'Skaits',
            data: [
                <?php echo $total_users; ?>, 
                <?php echo $today_events; ?>, 
                <?php echo $total_events; ?>, 
                <?php echo $today_volunteers; ?>
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 99, 132, 0.7)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

</body>
</html>
