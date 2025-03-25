<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="auth-form">
      <div class="auth-form-header">
        <h2 class="auth-form-title">Create an Account</h2>
        <p class="text-muted">Join AI Lifestyle and share your meaningful life</p>
      </div>
      
      <?php if( !empty( $error ) ): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars( $error ) ?>
        </div>
      <?php endif; ?>
      
      <?php if( !empty( $success ) ): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars( $success ) ?>
        </div>
      <?php endif; ?>
      
      <form method="post" action="">
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
          <label for="username">Username</label>
        </div>
        
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
          <label for="email">Email address</label>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          <label for="password">Password</label>
          <div class="form-text">Password must be at least 8 characters long.</div>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
          <label for="confirm_password">Confirm Password</label>
        </div>
        
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">Register</button>
        </div>
      </form>
      
      <div class="text-center mt-4">
        <p>Already have an account? <a href="<?= $baseUrl ?>/?page=login">Login</a></p>
      </div>
    </div>
  </div>
</div>
