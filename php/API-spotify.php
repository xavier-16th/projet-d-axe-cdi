<?php
require 'config.php'; // Include configuration file for Spotify credentials
require 'vendor/autoload.php'; // Load Guzzle via Composer
session_start(); // Start the PHP session to store the access token

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Handle logout request by destroying the session
if (isset($_GET['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to self (refresh page)
    exit();
}

// Check if we already have an access token in session
if (isset($_SESSION['access_token'])) {
    // User is already authenticated
} else {
    // If no authorization code is present, redirect to Spotify authorization page
    if (!isset($_GET['code'])) {
        $auth_url = "https://accounts.spotify.com/authorize?" . http_build_query([// Parameters for Spotify authorization
            "client_id" => SPOTIFY_CLIENT_ID,
            "response_type" => "code",
            "redirect_uri" => SPOTIFY_REDIRECT_URI,
            "scope" => "user-top-read playlist-modify-private playlist-modify-public"
        ]);
        header("Location: $auth_url");
        exit();
    }

    // Handle Spotify callback with authorization code
    $code = $_GET['code'];
    $token_url = "https://accounts.spotify.com/api/token";// exchange URL for the authorization code
    $client = new Client();// Create a new Guzzle HTTP client instance


    try{ // Attempt to exchange the authorization code for an access token
        $response = $client->post($token_url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [// Parameters for the token request
                'grant_type'    => 'authorization_code',// Grant type for the token request
                'code'          => $code,// Authorization code received from Spotify
                'redirect_uri'  => SPOTIFY_REDIRECT_URI,// Redirect URL registered in Spotify Developer Dashboard
            ]
        ]);

        $data = json_decode($response->getBody(), true);// Decode the JSON response from Spotify

        if (!isset($data['access_token'])) {
            die("Error: No access token returned.");
        }

        $_SESSION['access_token'] = $data['access_token'];// Store the access token in the session

        header("Location: http://localhost:8888/php/API-spotify.php");
        exit();

    } catch (RequestException $e) {
        die("Error exchanging token: " . $e->getMessage());
    }
}











$host = 'localhost';
$dbname = 'crud';
$username = 'root';
$port = 8888;
$password = 'root';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}



try {
    $bdd = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

// Check if user is logged in (session already started in main file)
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit();
}

// Get the logged-in user's username from session
$logged_username = $_SESSION['user'];

if (isset($_POST['send'])) { // check if the form is submitted
    if (isset($_POST['username']) && // check if the username and email is set
        isset($_POST['email']) &&
        $_POST['username'] != "" && // check if the username and email is not empty
        $_POST['email'] != ""
    ) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        // Update the user in the database using PDO prepared statements
        $sql = "UPDATE users SET username = :username, email = :email WHERE username = :current_username";
        $req = $bdd->prepare($sql);
        
        try {
            $req->execute([
                'username' => $username,
                'email' => $email,
                'current_username' => $logged_username
            ]);
            
            // Update session with new username if it changed
            $_SESSION['user'] = $username;
            
            header("Location: API-spotify.php"); // Redirect back to main page
            exit();
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    }
}



// Handle account deletion
if (isset($_POST['confirm_delete'])) {
    $sql = "DELETE FROM users WHERE username = :username";
    $req = $bdd->prepare($sql);
    
    try {
        $req->execute(['username' => $logged_username]);
        session_destroy();
        
        $redirect_url = $_POST['redirect_url'];
        header("Location: " . $redirect_url);
        exit();
    } catch (PDOException $e) {
        $error_message = "Error deleting account: " . $e->getMessage();
    }
}





?>




<!-- Spotify Playlist Embed Container  -->
<div id="fixed-iframe-container">
    <div id="iframe-container"></div>
</div>

<script>
 // Retrieve the access token stored in the session
const token = '<?= $_SESSION['access_token'] ?>';

// Function to fetch data from the Spotify Web API
async function fetchWebApi(endpoint, method, body) {
  const res = await fetch(`https://api.spotify.com/${endpoint}`, {
    headers: {
      Authorization: `Bearer ${token}`, // Attach the access token in the Authorization header
      'Content-Type': 'application/json' // Set content type to JSON
    },
    method, // HTTP method (GET, POST, etc.)
    body: body ? JSON.stringify(body) : null // If body is provided, convert it to JSON, otherwise null
  });
  return await res.json(); // Return the parsed JSON response
}

// Function to get the user's top tracks from Spotify
async function getTopTracks() {
  // Fetch the top tracks with a long-term time range, limiting the result to 5 tracks
  const data = await fetchWebApi('v1/me/top/tracks?time_range=long_term&limit=5', 'GET');
  return data.items; // Return the list of top tracks
}

// Function to create a new playlist with the top tracks
async function createPlaylist(tracksUri) {
  // Fetch the current user's ID
  const { id: user_id } = await fetchWebApi('v1/me', 'GET');
  
  // Create a new playlist for the user
  const playlist = await fetchWebApi(`v1/users/${user_id}/playlists`, 'POST', {
    name: "My Top Tracks Playlist",
    description: "Generated from my top 5 tracks",
    public: false
  });
  
  // Add the top tracks to the newly created playlist
  await fetchWebApi(`v1/playlists/${playlist.id}/tracks?uris=${tracksUri.join(',')}`, 'POST');
  return playlist; // Return the playlist object
}

// Main function to execute the steps
async function run() {
  // Get the top tracks
  const topTracks = await getTopTracks();
  
  // Extract the URIs of the top tracks
  const uris = topTracks.map(t => t.uri);
  
  // Create the playlist with the top tracks
  const playlist = await createPlaylist(uris);
  
  // Embed the playlist into the webpage using a responsive iframe
  const iframeContainer = document.getElementById('iframe-container');
  
  // Calculate responsive dimensions and scale
  const screenWidth = window.innerWidth;
  const maxWidth = 800;
  const baseHeight = 500;
  
  // Calculate scale factor based on screen width
  let containerWidth = Math.min(screenWidth * 0.95, maxWidth); // 95% of screen or max 800px
  let scaleFactor = containerWidth / maxWidth;
  let containerHeight = baseHeight * scaleFactor;
  
  // Minimum height to ensure usability
  if (containerHeight < 280) {
    containerHeight = 280;
    scaleFactor = containerHeight / baseHeight;
  }
  
  // Apply styles with calculated dimensions
  iframeContainer.style.cssText = `
    position: relative;
    width: ${containerWidth}px;
    height: ${containerHeight}px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: ${12 * scaleFactor}px;
    transform-origin: center center;
  `;
  
  // Create the responsive iframe with scaling
  iframeContainer.innerHTML = `
    <iframe
      title="Spotify Embed: Playlist"
      src="https://open.spotify.com/embed/playlist/${playlist.id}?utm_source=generator&theme=0"
      allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
      loading="lazy"
      style="
        width: ${maxWidth}px;
        height: ${baseHeight}px;
        border: none;
        border-radius: 12px;
        transform: scale(${scaleFactor});
        transform-origin: top left;
        display: block;
      "
    ></iframe>
  `;
}

document.addEventListener("DOMContentLoaded", () => { // in case it hasn't loaded yet
  run();
});
</script>












<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="code/spot.css"/>
    <title>Spotify home page </title>
    <meta name="description" content="spotify home page with artist cards and login feature . 
    This page showcases various artists with their photos, descriptions, albums, and songs released in 2024.">
</head> 
<body>





<!-- dark mode and account button   and booster -->
    <div class="top-right">
        <button onclick="myFunction()">Dark Mode</button>
        <button onclick="document.getElementById('id01').style.display='block'">account</button>
        <button class="add-button" onclick="addArtistCard()">open booster</button>
    

    </div>




<div id="id01" class="modal">
    <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
    <div class="modal-content">
        <div class="container">
            <?php
            // Display error message if there was an update error
            if (isset($error_message)) {
                echo "<p style='color: red;'>$error_message</p>";
            }
            if (isset($success_message)) {
                echo "<p style='color: green;'>$success_message</p>";
            }
            
            // Get current user info from database
            $sql = "SELECT * FROM users WHERE username = :username";
            $req = $bdd->prepare($sql);
            $req->execute(['username' => $logged_username]);
            $user = $req->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
            ?>
            <!-- Profile Update Form -->
            <div id="profileSection">
                <form action="" method="POST">
                    <h1>Modify Your Profile</h1>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" placeholder="username" required>
                    <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required>
                    <input type="submit" value="Update Profile" name="send">
                    <a class="link back" href="API-spotify.php">Back</a>

                </form>
                
                <!-- Delete Account Button -->
                    <p style="color: #666;">Once you delete your account, there is no going back.</p>
                    <button onclick="showDeleteConfirmation()" style="background-color: #d32f2f; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
                        Delete Account
                    </button>
                </div>
            </div>
            
            <!-- Delete Confirmation Section hidden  -->
            <div id="deleteSection" style="display: none;">
                <h2 style="color: #d32f2f;">⚠️ Delete Account</h2>
                <div style="background-color: #ffcdd2; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #d32f2f; ">
                    <strong>Warning!</strong> This action cannot be undone. Deleting your account will permanently remove all your data.
                </div>
                <p>Are you sure you want to delete your account <strong><?= htmlspecialchars($user['username']) ?></strong>?</p>
                
                <form action="" method="POST" onsubmit="return confirm('Last chance! Are you absolutely sure you want to delete your account forever?');">
                    <input type="hidden" name="redirect_url" value="http://localhost:8888/inscription%20/register%20and%20login.php">
                    <button type="submit" name="confirm_delete" style="background-color: #d32f2f; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                        Yes, Delete Forever
                    </button>
                    <button type="button" onclick="showProfile()" style="background-color: #757575; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        Cancel
                    </button>
                </form>
            </div>
            
            <?php
            } else {
                echo "<p>User profile not found.</p>";
                echo '<a href="API-spotify.php">Back to main page</a>';
            }
            ?>
        </div>
    </div>
</div>




<!-- Spotify logo -->
<img src="images /spotify.png" class="avatar">

<!-- Side nav -->
<div class="sidenav">
    <a href="#">About</a>
    <a href="#">Services</a>
    <a href="#">Clients</a>
    <a href="#">Contact</a>
    <a href="playlist.html">playlist</a> 
    
</div>


<!-- search bar -->
<div> 
    
        <div class="circle-search">
            <input type="search" placeholder="  search for artist ">
            <ul id="artist-card"> </ul>
        </div>
</div>



<!-- Tabs section centered under the top of the screen -->
<div class="tabs-wrapper">
    <div class="tabs">
        <button class="tab active" onclick="showContent('all')">All</button>
        <button class="tab" onclick="showContent('music')">Music</button>
        <button class="tab" onclick="showContent('podcasts')">Podcasts</button>
        <button class="tab" onclick="showContent('favorite')">favorite </button>
        
    </div>
</div>




<!-- Content id all  -->
<div class="content" id="all">
    <!-- Artist Cards for All Tab  -->
    <div class="card-container">
        <!-- Artist Card 1: Travis Scott -->
        <div class="artist-card">
            
            <img src="images /travis  copy.jpeg" alt="Travis Scott's Photo"  class="artist-photo">
            <div class="artist-info" > 
                <h2 class="artist-name">Travis Scott</h2>
                <p class="artist-description">Travis Scott is an American rapper, singer, and record producer known for his heavily auto-tuned voice and his impact on the sound of modern hip-hop.</p>
                <div class="albums">
                    <h3>Albums 2024 :</h3>
                    <ul>
                        <li>Days Before Rodeo</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Songs Released in 2024:</h3>
                    <ul>
                        <li>Parking Lot</li>
                    </ul>
                </div>
            </div>
        </div>
<!-- Artist Card 2: Drake -->
<div class="artist-card">
    
    <img src="images /drake.jpg" alt="Drake's Photo" class="artist-photo">
    <div class="artist-info"> 
        <h2 class="artist-name">Drake</h2>
        <p class="artist-description">Drake is a Canadian rapper, singer, and songwriter known for blending rap with R&B and pop influences, becoming one of the most commercially successful artists of his era.</p>
        <div class="albums">
            <h3>Albums 2024:</h3>
            <ul>
                <li>For All the Dogs Scary Hours Edition</li>
            </ul>
        </div>
        <div class="songs">
            <h3>Songs Released in 2024:</h3>
            <ul>
                <li>Red Button</li>
            </ul>
        </div>
    </div>
</div>

        <!-- Artist Card 2: Playboi Carti -->
        <div class="artist-card">
            
            <img src="images /iamliar copy.jpg" alt="Playboi Carti's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">Playboi Carti</h2>
                <p class="artist-description">Playboi Carti is an American rapper, singer, and songwriter. He is known for his unique delivery, high-energy beats, and punk-inspired aesthetic.</p>
                <div class="albums">
                    <h3>Albums 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Songs Released in 2024:</h3>
                    <ul>
                        <li>Whole Lotta Red</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Artist Card 3: Chief Keef -->
        <div class="artist-card">
            
            <img src="images /keef.jpeg" alt="Chief Keef's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">Chief Keef</h2>
                <p class="artist-description">Chief Keef, born Keith Cozart, is a Chicago yapper pioneer. Known for his creativity and loyalty, he inspires others while staying focused on his art and lasting legacy.</p>
                <div class="albums">
                    <h3>Podcasts 2024 :</h3>
                    <ul>
                        <li>Almighty So</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Podcasts Released in 2024:</h3>
                    <ul>
                        <li>Faneto</li>
                        <li>Sosa</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Artist Card 4: King Von -->
        <div class="artist-card">
           
            <img src="images /von.jpeg" alt="King Von's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">King Von</h2>
                <p class="artist-description">King Von, born Dayvon Bennett, was a Chicago yapper known for his storytelling, loyalty, and generosity. Loved for his humor and energy, he was driven to uplift others and create a better future for those around him.</p>
                <div class="albums">
                    <h3>Podcasts 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Podcasts Released in 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content id music  -->
<div class="content" id="music" style="display: none;">
    <!-- Artist Cards for Music Tab -->
    <div class="card-container">
        <!-- Travis Scott -->
        <div class="artist-card">
            <img src="images /travis  copy.jpeg" alt="Travis Scott's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">Travis Scott</h2>
                <p class="artist-description">Travis Scott is an American rapper, singer, and record producer known for his heavily auto-tuned voice and his impact on the sound of modern hip-hop.</p>
                <div class="albums">
                    <h3>Albums 2024 :</h3>
                    <ul>
                        <li>Days Before Rodeo</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Songs Released in 2024:</h3>
                    <ul>
                        <li>Parking Lot</li>
                    </ul>
                </div>
            </div>
        </div>

        <!--  Playboi Carti -->
        <div class="artist-card">
            <img src="images /iamliar copy.jpg" alt="Playboi Carti's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">Playboi Carti</h2>
                <p class="artist-description">Playboi Carti is an American rapper, singer, and songwriter. He is known for his unique delivery, high-energy beats, and punk-inspired aesthetic.</p>
                <div class="albums">
                    <h3>Albums 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Songs Released in 2024:</h3>
                    <ul>
                        <li>Whole Lotta Red</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content id podcasts  -->
<div class="content" id="podcasts" style="display: none;">
    <!-- Artist Cards for Podcasts Tab -->
    <div class="card-container">
        <!--  Chief Keef -->
        <div class="artist-card">
            <img src="images /keef.jpeg" alt="Chief Keef's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">Chief Keef</h2>
                <p class="artist-description">Chief Keef, born Keith Cozart, is a Chicago yapper pioneer. Known for his creativity and loyalty, he inspires others while staying focused on his art and lasting legacy.</p>
                <div class="albums">
                    <h3>Podcasts 2024 :</h3>
                    <ul>
                        <li>Almighty So</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Podcasts Released in 2024:</h3>
                    <ul>
                        <li>Faneto</li>
                        <li>Sosa</li>
                    </ul>
                </div>
            </div>
        </div>

        <!--  King Von -->
        <div class="artist-card">
            <img src="images /von.jpeg" alt="King Von's Photo" class="artist-photo">
            <div class="artist-info">
                <h2 class="artist-name">King Von</h2>
                <p class="artist-description">King Von, born Dayvon Bennett, was a Chicago yapper known for his storytelling, loyalty, and generosity. Loved for his humor and energy, he was driven to uplift others and create a better future for those around him.</p>
                <div class="albums">
                    <h3>Podcasts 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
                <div class="songs">
                    <h3>Podcasts Released in 2024:</h3>
                    <ul>
                        <li>0</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Favorites Tab -->
<div class="content" id="favorite" style="display: none;">
    <div class="card-container"></div> 
</div>




</div>

<script src="code/main.js"></script>
</body>
</html>
