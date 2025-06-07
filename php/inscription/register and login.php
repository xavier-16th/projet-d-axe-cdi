<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register and login.css">
    <title>register</title>
</head>
<body>
   
<form>
</form>
    
 
<div class="container">
        <h1>Log in to Spotify</h1>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="login-email">Email address</label>
                <input type="email" id="login-email" name="email" placeholder="Email address" required />
            </div>
            
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Password" required />
            </div>
            

            
            <button type="submit" name="login">LOG IN</button>
        </form>
        
        <div class="divider">
            <span class="divider-text">OR</span>
        </div>
        
        <div class="register-prompt">
            Don't have an account? <a href="#">Sign up for Spotify</a>
        </div>
    </div>
    
    <div class="container" style="margin-top: 30px; display: none;" id="register-form">
        <h1>Sign up for Spotify</h1>
        
        <form method="post" action="register.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            
            <button type="submit" value="Register" name="ok">SIGN UP</button>
        </form>
        
        <div class="register-prompt">
            Already have an account? <a href="#" onclick="toggleForms()">Log in</a>
        </div>
    </div>
    


    <script src="register.js"></script>
</body>
</html>

