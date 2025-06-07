<?php

$host = 'localhost';
$dbname = 'login';
$username = 'root';
$port = 8888;
$password = 'root';

try {
    $bdd = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

session_start();

if (isset($_POST['login'])) { // Check if the form is submitted
    $email = $_POST['email']; 
    $password = $_POST['password'];

    // Get user by email
    $req = $bdd->prepare("SELECT * FROM users WHERE email = :email");
    $req->execute(['email' => $email]); 
    $user = $req->fetch(PDO::FETCH_ASSOC); // Fetch the user data

    if ($user && password_verify($password, $user['password'])) {// Verify the password and user are correct than 

        $_SESSION['user'] = $user['username'];
        header("Location: http://localhost:8888/php/API-spotify.php"); // Redirect to welcome page
       
    } else {
        echo "Invalid email or password.";
    }
}
?>
