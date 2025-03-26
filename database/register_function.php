<?php
require "con_db.php";

if (isset($_POST['registracija'])) {
    // Getting form inputs and sanitizing them
    $lietotajvards = htmlspecialchars($_POST['username']);
    $parole = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = htmlspecialchars($_POST['name']); // Assuming there's a 'name' field
    $surname = htmlspecialchars($_POST['surname']); // Assuming there's a 'surname' field
    $email = htmlspecialchars($_POST['email']); // Assuming there's an 'email' field
    $profile_pic = NULL; // Setting profile_pic to NULL by default if no picture uploaded
    $bio = NULL; // Assuming bio is optional, set to NULL by default
    $location = NULL; // Same for location
    $social_links = NULL; // Assuming social links are optional
    $role = 'user'; // Default role

    // Prepare SQL query
    $vaicajums = $savienojums->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, bio, location, social_links, role) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $vaicajums->bind_param("ssssssssss", $lietotajvards, $parole, $name, $surname, $email, $profile_pic, $bio, $location, $social_links, $role);

    // Execute the query and check if it was successful
    if ($vaicajums->execute()) {
        header("Location: ../main/login.php");
    } else {
        echo "Reģistrācija neizdevās!"; // Registration failed message
    }

    // Close the statement and the database connection
    $vaicajums->close();
    $savienojums->close();
}
?>
