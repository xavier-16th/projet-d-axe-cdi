<?php

$host = 'localhost';
$dbname = 'login';
$username = 'root';
$port = 8888;
$password = 'root';

try {
    $bdd = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) { // Catch any connection errors
    die("Error connecting to the database: " . $e->getMessage()); // Display error message
}

if (isset($_POST['ok'])) {// Check if the form is submitted
    $username = $_POST['username']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // makes the password hased in myphpadmin 

    $req = $bdd->prepare("INSERT INTO users VALUES (0, :username, :email, :password)");// Prepare the SQL statement
    $execute = $req->execute(array( // Execute the SQL statement
        // Bind the 3 parameters
        'username' => $username, 
        'email' => $email,
        'password' => $hashedPassword
    ));

header("location: http://localhost:8888/inscription%20/register%20and%20login.php"); // Redirect to login page
    exit(); // Stop further script execution
} else {
    echo "Please fill in all data.";
}
?>

<?php 



?>
