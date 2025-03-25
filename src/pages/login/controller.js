/**
 * Login page controller
 * Handles client-side form validation and submission
 */

document.addEventListener('DOMContentLoaded', function() {
  // Get form elements
  const loginForm = document.querySelector('form');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  
  // Form validation
  loginForm.addEventListener('submit', function(event) {
    let isValid = true;
    const emailValue = emailInput.value.trim();
    const passwordValue = passwordInput.value.trim();
    
    // Reset previous error states
    emailInput.classList.remove('is-invalid');
    passwordInput.classList.remove('is-invalid');
    
    // Validate email
    if (!emailValue) {
      emailInput.classList.add('is-invalid');
      isValid = false;
    } else if (!isValidEmail(emailValue)) {
      emailInput.classList.add('is-invalid');
      isValid = false;
    }
    
    // Validate password
    if (!passwordValue) {
      passwordInput.classList.add('is-invalid');
      isValid = false;
    }
    
    // If form is not valid, prevent submission
    if (!isValid) {
      event.preventDefault();
    }
  });
  
  // Helper function to validate email format
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
  
  // Add input event listeners to clear error state when user types
  emailInput.addEventListener('input', function() {
    this.classList.remove('is-invalid');
  });
  
  passwordInput.addEventListener('input', function() {
    this.classList.remove('is-invalid');
  });
});
