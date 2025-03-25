<?php
// Get avatar URL
$avatarUrl = $user['avatar_id'] 
  ? "$baseUrl/uploads/{$user['avatar_id']}" 
  : "$baseUrl/assets/img/default-avatar.png";
?>

<div class="container py-4">
  <div class="row">
    <div class="col-lg-3 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($user['username']) ?>" class="rounded-circle img-fluid mb-3" style="width: 120px; height: 120px; object-fit: cover;">
          <h5><?= htmlspecialchars($user['username']) ?></h5>
          <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="list-group list-group-flush">
          <a href="#profile-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
            <i class="bi bi-person me-2"></i> Profile Settings
          </a>
          <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
            <i class="bi bi-shield-lock me-2"></i> Security
          </a>
          <a href="#msgs" class="list-group-item list-group-item-action" data-bs-toggle="list">
            <i class="bi bi-bell me-2"></i> Msgs
          </a>
          <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
            <i class="bi bi-eye me-2"></i> Privacy
          </a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-9">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= $error ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $success ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <div class="tab-content">
        <!-- Profile Settings -->
        <div class="tab-pane fade show active" id="profile-settings">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Profile Settings</h5>
            </div>
            <div class="card-body">
              <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="mb-3">
                  <label for="summary" class="form-label">Bio</label>
                  <textarea class="form-control" id="summary" name="summary" rows="3"><?= htmlspecialchars($user['summary'] ?? '') ?></textarea>
                  <div class="form-text">Tell people a bit about yourself</div>
                </div>
                
                <div class="mb-3">
                  <label for="avatar" class="form-label">Profile Picture</label>
                  <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                  <div class="form-text">Maximum file size: 2MB. Recommended dimensions: 300x300 pixels.</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Security -->
        <div class="tab-pane fade" id="security">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
              <form action="" method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="mb-3">
                  <label for="current_password" class="form-label">Current Password</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                
                <div class="mb-3">
                  <label for="new_password" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                  <div class="form-text">Password must be at least 8 characters long</div>
                </div>
                
                <div class="mb-3">
                  <label for="confirm_password" class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
              </form>
            </div>
          </div>
          
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="mb-0">Connected Accounts</h5>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <i class="bi bi-google me-2 text-danger"></i>
                  <span>Google</span>
                </div>
                <?php if ($user['google_id']): ?>
                  <span class="badge bg-success">Connected</span>
                <?php else: ?>
                  <a href="<?= $baseUrl ?>/?page=login&action=google" class="btn btn-sm btn-outline-primary">Connect</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Msgs -->
        <div class="tab-pane fade" id="msgs">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Msg Settings</h5>
            </div>
            <div class="card-body">
              <form action="" method="POST">
                <input type="hidden" name="action" value="msg_settings">
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="email_msgs" name="email_msgs" 
                         <?= ($msgSettings['email_msgs'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="email_msgs">
                    Email Msgs
                  </label>
                  <div class="form-text">Receive msgs via email</div>
                </div>
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="comment_msgs" name="comment_msgs"
                         <?= ($msgSettings['comment_msgs'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="comment_msgs">
                    Comments
                  </label>
                  <div class="form-text">Get msg when someone comments on your widgets</div>
                </div>
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="like_msgs" name="like_msgs"
                         <?= ($msgSettings['like_msgs'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="like_msgs">
                    Likes
                  </label>
                  <div class="form-text">Get msg when someone likes your widgets</div>
                </div>
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="follow_msgs" name="follow_msgs"
                         <?= ($msgSettings['follow_msgs'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="follow_msgs">
                    Follows
                  </label>
                  <div class="form-text">Get msg when someone follows you</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Preferences</button>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Privacy -->
        <div class="tab-pane fade" id="privacy">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Privacy Settings</h5>
            </div>
            <div class="card-body">
              <form action="" method="POST">
                <input type="hidden" name="action" value="privacy_settings">
                
                <div class="mb-3">
                  <label class="form-label">Profile Visibility</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_public" value="public"
                           <?= ($privacySettings['profile_visibility'] ?? 'public') === 'public' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="visibility_public">
                      Public
                    </label>
                    <div class="form-text">Anyone can view your profile and widgets</div>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_followers" value="followers"
                           <?= ($privacySettings['profile_visibility'] ?? 'public') === 'followers' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="visibility_followers">
                      Followers Only
                    </label>
                    <div class="form-text">Only your followers can view your profile and widgets</div>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_private" value="private"
                           <?= ($privacySettings['profile_visibility'] ?? 'public') === 'private' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="visibility_private">
                      Private
                    </label>
                    <div class="form-text">Only you can view your profile and widgets</div>
                  </div>
                </div>
                
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" id="show_email" name="show_email"
                         <?= ($privacySettings['show_email'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="show_email">
                    Show Email Address
                  </label>
                  <div class="form-text">Allow people to see your email address on your profile</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
