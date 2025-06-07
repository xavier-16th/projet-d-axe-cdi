        function toggleForms() {
            const loginForm = document.querySelector('.container'); 
            const registerForm = document.getElementById('register-form');// Get the register form
            
            if (registerForm.style.display === 'none') {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';// Show register form
            } else {
                loginForm.style.display = 'block'; // Show login form
                registerForm.style.display = 'none'; 
            }
        }
        
        document.querySelector('.register-prompt a').addEventListener('click', function(e) {// Prevents th default action of the link while listening for the click event
            e.preventDefault(); 
            toggleForms();
        });