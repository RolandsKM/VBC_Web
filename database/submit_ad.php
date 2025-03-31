<?php
require "con_db.php";
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $location = htmlspecialchars($_POST['location']);
    $date = $_POST['date'];
    $category = htmlspecialchars($_POST['category']);

    $user_id = $_SESSION['ID_user'];

    $query = $savienojums->prepare("INSERT INTO `Events` (`title`, `description`, `location`, `date`, `user_id`, `created_at`) VALUES (?, ?, ?, ?, ?, NOW())");
    $query->bind_param("ssssi", $title, $description, $location, $date, $user_id);

    if ($query->execute()) {
        $event_id = $query->insert_id;

        
        $category_query = $savienojums->prepare("SELECT `Kategorijas_ID` FROM `VBC_Kategorijas` WHERE `Nosaukums` = ?");
        $category_query->bind_param("s", $category);
        $category_query->execute();
        $category_result = $category_query->get_result();

        if ($category_result->num_rows > 0) {
            $category_row = $category_result->fetch_assoc();
            $category_id = $category_row['Kategorijas_ID'];

           
            $event_category_query = $savienojums->prepare("INSERT INTO `Event_Categories` (`event_id`, `category_id`) VALUES (?, ?)");
            $event_category_query->bind_param("ii", $event_id, $category_id);
            $event_category_query->execute();
        }

        
        echo json_encode([
            'success' => true,
            'event_id' => $event_id,
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'date' => $date,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        exit();
    } else {
        echo json_encode(['error' => 'Error adding event']);
        exit();
    }

   
    $query->close();
    $category_query->close();
    $event_category_query->close();
    $savienojums->close();
}
?>
