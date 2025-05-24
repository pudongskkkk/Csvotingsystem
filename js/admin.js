document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
        }
    });
    
    // Form submission
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Here you would typically make an AJAX request to your backend
        // For demo purposes, we'll use hardcoded credentials
        const validUsername = 'admin';
        const validPassword = 'Cssadmin@2025';
        
        if (username === validUsername && password === validPassword) {
            // Successful login - redirect to admin dashboard
            window.location.href = '../html/index.html';
        } else {
            // Show error toast
            const toast = new bootstrap.Toast(document.getElementById('loginToast'));
            document.getElementById('toastMessage').textContent = 
                'Invalid username or password. Please try again.';
            toast.show();
            
            // Clear password field
            document.getElementById('password').value = '';
        }
    });
});