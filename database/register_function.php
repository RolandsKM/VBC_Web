<?php
require "con_db.php";

if (isset($_POST['registracija'])) {
 
    $lietotajvards = htmlspecialchars($_POST['username']);
    $parole = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = htmlspecialchars($_POST['name']); 
    $surname = htmlspecialchars($_POST['surname']); 
    $email = htmlspecialchars($_POST['email']);
    $profile_pic = NULL; 
    $bio = NULL; 
    $location = NULL; 
    $social_links = NULL; 
    $role = 'user';

   
    $vaicajums = $savienojums->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, bio, location, social_links, role) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  
    $vaicajums->bind_param("ssssssssss", $lietotajvards, $parole, $name, $surname, $email, $profile_pic, $bio, $location, $social_links, $role);

    
    if ($vaicajums->execute()) {
        header("Location: ../main/login.php");
    } else {
        echo "Reģistrācija neizdevās!"; 
    }

    
    $vaicajums->close();
    $savienojums->close();
}
?>
