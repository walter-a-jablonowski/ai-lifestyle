<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="auth-form">
      <div class="auth-form-header">
        <h2 class="auth-form-title">Login to AI Lifestyle</h2>
        <p class="text-muted">Share your AI-age lifestyle</p>
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
          <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
          <label for="email">Email address</label>
        </div>
        
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          <label for="password">Password</label>
        </div>
        
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary btn-lg">Login</button>
          <a href="<?= $googleAuthUrl ?>" class="btn btn-outline-danger btn-lg">
            <i class="bi bi-google"></i> Login with Google
          </a>
        </div>
      </form>
      
      <div class="text-center mt-4">
        <p>Don't have an account? <a href="<?= $baseUrl ?>/?page=register">Register</a></p>
      </div>
    </div>
  </div>
</div>
