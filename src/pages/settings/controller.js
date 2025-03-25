document.addEventListener('DOMContentLoaded', function() {
  // Handle avatar preview
  const avatarInput = document.getElementById('avatar');
  
  if (avatarInput) {
    avatarInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          const profileImage = document.querySelector('.card-body img.rounded-circle');
          if (profileImage) {
            profileImage.src = e.target.result;
          }
        };
        
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
  
  // Password strength validation
  const newPasswordInput = document.getElementById('new_password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  
  if (newPasswordInput && confirmPasswordInput) {
    // Check password strength
    newPasswordInput.addEventListener('input', function() {
      const password = this.value;
      const strengthMeter = document.getElementById('password-strength');
      
      if (!strengthMeter) {
        const meterDiv = document.createElement('div');
        meterDiv.id = 'password-strength';
        meterDiv.className = 'mt-2';
        this.parentNode.appendChild(meterDiv);
      }
      
      const strength = calculatePasswordStrength(password);
      updatePasswordStrengthUI(strength);
    });
    
    // Check password match
    confirmPasswordInput.addEventListener('input', function() {
      const password = newPasswordInput.value;
      const confirmPassword = this.value;
      const feedbackElement = document.getElementById('password-match-feedback');
      
      if (!feedbackElement) {
        const feedbackDiv = document.createElement('div');
        feedbackDiv.id = 'password-match-feedback';
        feedbackDiv.className = 'form-text mt-2';
        this.parentNode.appendChild(feedbackDiv);
      }
      
      if (password === confirmPassword) {
        document.getElementById('password-match-feedback').textContent = 'Passwords match';
        document.getElementById('password-match-feedback').className = 'form-text text-success mt-2';
      } else {
        document.getElementById('password-match-feedback').textContent = 'Passwords mismatch';
        document.getElementById('password-match-feedback').className = 'form-text text-danger mt-2';
      }
    });
  }
  
  // Tab persistence using URL hash
  const hash = window.location.hash;
  if (hash) {
    const tab = document.querySelector(`a[href="${hash}"]`);
    if (tab) {
      const tabInstance = new bootstrap.Tab(tab);
      tabInstance.show();
    }
  }
  
  // Update URL hash when tab changes
  const tabEls = document.querySelectorAll('a[data-bs-toggle="list"]');
  tabEls.forEach(tabEl => {
    tabEl.addEventListener('shown.bs.tab', function(event) {
      window.location.hash = event.target.getAttribute('href');
    });
  });
  
  // Helper functions
  function calculatePasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length >= 8) {
      strength += 1;
    }
    if (password.length >= 12) {
      strength += 1;
    }
    
    // Character type checks
    if (/[A-Z]/.test(password)) {
      strength += 1;
    }
    if (/[a-z]/.test(password)) {
      strength += 1;
    }
    if (/[0-9]/.test(password)) {
      strength += 1;
    }
    if (/[^A-Za-z0-9]/.test(password)) {
      strength += 1;
    }
    
    return strength;
  }
  
  function updatePasswordStrengthUI(strength) {
    const strengthMeter = document.getElementById('password-strength');
    if (!strengthMeter) return;
    
    let strengthText = '';
    let strengthClass = '';
    
    if (strength < 2) {
      strengthText = 'Weak';
      strengthClass = 'text-danger';
    } else if (strength < 4) {
      strengthText = 'Moderate';
      strengthClass = 'text-warning';
    } else if (strength < 6) {
      strengthText = 'Strong';
      strengthClass = 'text-success';
    } else {
      strengthText = 'Very Strong';
      strengthClass = 'text-success';
    }
    
    strengthMeter.innerHTML = `
      <div class="progress" style="height: 5px;">
        <div class="progress-bar ${strengthClass === 'text-danger' ? 'bg-danger' : 
                                   strengthClass === 'text-warning' ? 'bg-warning' : 'bg-success'}" 
             role="progressbar" 
             style="width: ${(strength / 6) * 100}%"></div>
      </div>
      <small class="${strengthClass} mt-1 d-inline-block">${strengthText}</small>
    `;
  }
});
