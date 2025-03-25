<?php if( !$profileUser ): ?>
  <div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars( $error ) ?>
  </div>
<?php else: ?>
  <?php
    $avatarUrl = $profileUser['avatar_id'] 
      ? "$baseUrl/uploads/{$profileUser['avatar_id']}" 
      : "$baseUrl/assets/img/default-avatar.png";
  ?>
  
  <!-- Profile Header -->
  <div class="profile-header">
    <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars( $profileUser['username'] ) ?>" class="profile-avatar">
    <h1 class="profile-username"><?= htmlspecialchars( $profileUser['username'] ) ?></h1>
    
    <?php if( !empty( $profileUser['summary'] ) ): ?>
      <p class="profile-summary"><?= nl2br( htmlspecialchars( $profileUser['summary'] ) ) ?></p>
    <?php endif; ?>
    
    <div class="profile-stats">
      <div class="profile-stat">
        <span class="profile-stat-value"><?= count( $widgets ) ?></span>
        <span class="profile-stat-label">Widgets</span>
      </div>
      <div class="profile-stat">
        <span class="profile-stat-value"><?= $followerCount ?? 0 ?></span>
        <span class="profile-stat-label">Followers</span>
      </div>
      <div class="profile-stat">
        <span class="profile-stat-value"><?= $followingCount ?? 0 ?></span>
        <span class="profile-stat-label">Following</span>
      </div>
    </div>
    
    <?php if( $isLoggedIn && ! $isOwnProfile ): ?>
      <div class="mt-3">
        <form action="<?= $baseUrl ?>/ajax.php?action=profile&handler=follow" method="post" id="follow-form">
          <input type="hidden" name="user_id" value="<?= $profileUser['id'] ?>">
          <button type="button" class="btn <?= $isFollowing ? 'btn-secondary' : 'btn-primary' ?>" id="follow-button" data-user-id="<?= $profileUser['id'] ?>" data-following="<?= $isFollowing ? '1' : '0' ?>">
            <i class="bi <?= $isFollowing ? 'bi-person-dash' : 'bi-person-plus' ?>"></i>
            <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>
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
  
  <div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
      <!-- Create Widget Form (only for own profile) -->
      <?php if( $isOwnProfile ): ?>
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Create New Widget</h5>
          </div>
          <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data" id="create-widget-form">
              <input type="hidden" name="action" value="create_widget">
              
              <div class="mb-3">
                <label for="short_text" class="form-label">Short Text</label>
                <input type="text" class="form-control" id="short_text" name="short_text" required>
              </div>
              
              <div class="mb-3">
                <label for="full_text" class="form-label">Full Text (Optional)</label>
                <textarea class="form-control" id="full_text" name="full_text" rows="4"></textarea>
              </div>
              
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_html" name="is_html">
                <label class="form-check-label" for="is_html">
                  Full text contains HTML
                </label>
              </div>
              
              <div class="mb-3">
                <label for="media_type" class="form-label">Media Type</label>
                <select class="form-select" id="media_type" name="media_type">
                  <option value="none">None</option>
                  <option value="image">Image</option>
                  <option value="video">Video (YouTube)</option>
                  <option value="weblink">Web Link</option>
                  <option value="map">Google Maps</option>
                </select>
              </div>
              
              <div id="media-content-container" class="mb-3 d-none">
                <!-- This will be populated by JavaScript based on selected media type -->
              </div>
              
              <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="lifestyle, mindfulness, etc.">
              </div>
              
              <button type="submit" class="btn btn-primary">Create Widget</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
      
      <!-- Widgets -->
      <h2 class="h4 mb-3">Widgets</h2>
      
      <div id="widgets-container">
        <?php if( empty( $widgets ) ): ?>
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <?php if( $isOwnProfile ): ?>
              You haven't created any widgets yet. Create your first widget to share your lifestyle!
            <?php else: ?>
              <?= htmlspecialchars( $profileUser['username'] ) ?> hasn't created any widgets yet.
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php foreach( $widgets as $widget ): ?>
            <?php 
              $tags = getWidgetTags( $widget['id'], $db );
            ?>
            <div class="widget">
              <div class="widget-header">
                <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars( $profileUser['username'] ) ?>" class="widget-avatar">
                <div>
                  <span class="widget-username"><?= htmlspecialchars( $profileUser['username'] ) ?></span>
                  <div class="widget-time"><?= formatDate( $widget['created_at'] ) ?></div>
                </div>
                
                <?php if( $isOwnProfile ): ?>
                  <div class="ms-auto dropdown">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item edit-widget-btn" href="#" data-widget-id="<?= $widget['id'] ?>">
                          <i class="bi bi-pencil"></i> Edit
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item delete-widget-btn" href="#" data-widget-id="<?= $widget['id'] ?>">
                          <i class="bi bi-trash"></i> Delete
                        </a>
                      </li>
                    </ul>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="widget-content">
                <div class="widget-text">
                  <?= htmlspecialchars( $widget['short_text'] ) ?>
                  
                  <?php if( !empty( $widget['full_text'] ) ): ?>
                    <div id="full-text-<?= $widget['id'] ?>" class="d-none mt-3">
                      <?php if( $widget['is_html'] ): ?>
                        <?= $widget['full_text'] ?>
                      <?php else: ?>
                        <?= nl2br( htmlspecialchars( $widget['full_text'] ) ) ?>
                      <?php endif; ?>
                    </div>
                    <a href="#" class="full-text-toggle text-primary d-block mt-2" data-widget-id="<?= $widget['id'] ?>">
                      Show More
                    </a>
                  <?php endif; ?>
                </div>
                
                <?php if( $widget['media_type'] !== 'none' && !empty( $widget['media_content'] ) ): ?>
                  <div class="widget-media">
                    <?php if( $widget['media_type'] === 'image' ): ?>
                      <img src="<?= $baseUrl ?>/uploads/<?= $widget['media_content'] ?>" 
                           alt="<?= htmlspecialchars( $widget['original_file_name'] ?? 'Image' ) ?>"
                           class="img-fluid">
                    <?php elseif( $widget['media_type'] === 'video' ): ?>
                      <?php 
                        // Extract video ID from YouTube URL
                        $videoId = '';
                        if( strpos( $widget['media_content'], 'youtube.com' ) !== false ) {
                          parse_str( parse_url( $widget['media_content'], PHP_URL_QUERY ), $params );
                          $videoId = $params['v'] ?? '';
                        } elseif( strpos( $widget['media_content'], 'youtu.be' ) !== false ) {
                          $videoId = substr( parse_url( $widget['media_content'], PHP_URL_PATH ), 1 );
                        }
                      ?>
                      <?php if( $videoId ): ?>
                        <div class="ratio ratio-16x9">
                          <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" 
                                  title="YouTube video" allowfullscreen></iframe>
                        </div>
                      <?php else: ?>
                        <div class="alert alert-warning">Invalid video URL</div>
                      <?php endif; ?>
                    <?php elseif( $widget['media_type'] === 'weblink' ): ?>
                      <div class="card">
                        <div class="card-body">
                          <h5 class="card-title">External Link</h5>
                          <a href="<?= htmlspecialchars( $widget['media_content'] ) ?>" 
                             target="_blank" rel="noopener noreferrer"
                             class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right"></i> Visit Link
                          </a>
                        </div>
                      </div>
                    <?php elseif( $widget['media_type'] === 'map' ): ?>
                      <div class="ratio ratio-4x3">
                        <iframe src="<?= htmlspecialchars( $widget['media_content'] ) ?>" 
                                title="Google Maps" allowfullscreen></iframe>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                
                <?php if( !empty( $tags ) ): ?>
                  <div class="widget-tags">
                    <?php foreach( $tags as $tag ): ?>
                      <a href="<?= $baseUrl ?>/?page=search&tag=<?= urlencode( $tag['name'] ) ?>" 
                         class="widget-tag">
                        #<?= htmlspecialchars( $tag['name'] ) ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="widget-actions">
                <a href="#" class="widget-action-btn <?= $widget['is_liked'] ? 'liked' : '' ?> like-button" 
                   data-widget-id="<?= $widget['id'] ?>">
                  <i class="bi <?= $widget['is_liked'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                  <span class="like-count"><?= $widget['like_count'] ?></span>
                </a>
                
                <a href="#" class="widget-action-btn comment-toggle" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#comments-section-<?= $widget['id'] ?>">
                  <i class="bi bi-chat"></i>
                  <span id="comment-count-<?= $widget['id'] ?>"><?= $widget['comment_count'] ?></span>
                </a>
              </div>
              
              <div class="collapse" id="comments-section-<?= $widget['id'] ?>">
                <div class="comments-section">
                  <div id="comments-container-<?= $widget['id'] ?>">
                    <?php
                      // Get comments for this widget
                      $commentQuery = $db->prepare(
                        "SELECT c.*, u.username, u.avatar_id
                         FROM comments c
                         JOIN users u ON c.user_id = u.id
                         WHERE c.widget_id = ?
                         ORDER BY c.created_at ASC
                         LIMIT 5"
                      );
                      $commentQuery->execute([$widget['id']]);
                      $comments = $commentQuery->fetchAll();
                      
                      foreach( $comments as $comment ):
                        $commentAvatarUrl = $comment['avatar_id'] 
                          ? "$baseUrl/uploads/{$comment['avatar_id']}" 
                          : "$baseUrl/assets/img/default-avatar.png";
                    ?>
                      <div class="comment">
                        <a href="<?= $baseUrl ?>/?page=profile&id=<?= $comment['user_id'] ?>">
                          <img src="<?= $commentAvatarUrl ?>" alt="<?= htmlspecialchars( $comment['username'] ) ?>" class="comment-avatar">
                        </a>
                        <div class="comment-content">
                          <div class="comment-header">
                            <a href="<?= $baseUrl ?>/?page=profile&id=<?= $comment['user_id'] ?>" class="comment-username text-decoration-none">
                              <?= htmlspecialchars( $comment['username'] ) ?>
                            </a>
                            <span class="comment-time"><?= formatDate( $comment['created_at'] ) ?></span>
                          </div>
                          <p class="comment-text"><?= nl2br( htmlspecialchars( $comment['content'] ) ) ?></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  
                  <?php if( $widget['comment_count'] > 5 ): ?>
                    <div class="text-center my-3">
                      <button class="btn btn-sm btn-outline-secondary load-more-btn" 
                              data-content-type="comments" 
                              data-widget-id="<?= $widget['id'] ?>">
                        Load More Comments
                      </button>
                    </div>
                  <?php endif; ?>
                  
                  <?php if( $isLoggedIn ): ?>
                    <form class="comment-form mt-3" data-widget-id="<?= $widget['id'] ?>">
                      <div class="input-group">
                        <input type="text" class="form-control comment-input" 
                               placeholder="Add a comment..." required>
                        <button class="btn btn-primary" type="submit">Post</button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <?php if( $hasMoreWidgets ): ?>
        <div class="load-more-container">
          <button class="btn btn-outline-primary load-more-btn" 
                  data-content-type="widgets" 
                  data-page="1"
                  data-user-id="<?= $profileUser['id'] ?>">
            Load More
          </button>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Edit Profile Form (only for own profile) -->
      <?php if( $isOwnProfile ): ?>
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Edit Profile</h5>
          </div>
          <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data">
              <input type="hidden" name="action" value="update_profile">
              
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" 
                       value="<?= htmlspecialchars( $profileUser['username'] ) ?>" required>
              </div>
              
              <div class="mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="3"><?= htmlspecialchars( $profileUser['summary'] ?? '' ) ?></textarea>
              </div>
              
              <div class="mb-3">
                <label for="avatar" class="form-label">Avatar</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                <?php if( $profileUser['avatar_id'] ): ?>
                  <div class="mt-2">
                    <img src="<?= $avatarUrl ?>" alt="Current Avatar" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                  </div>
                <?php endif; ?>
              </div>
              
              <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
      
      <!-- Following List -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Following</h5>
        </div>
        <div class="card-body">
          <?php if( empty( $following ) ): ?>
            <p class="text-muted">
              <?= $isOwnProfile ? "You're no following anyone yet." : htmlspecialchars( $profileUser['username'] ) . " isn't following anyone yet." ?>
            </p>
          <?php else: ?>
            <div class="row g-2">
              <?php foreach( $following as $followedUser ): ?>
                <?php 
                  $followedAvatarUrl = $followedUser['avatar_id'] 
                    ? "$baseUrl/uploads/{$followedUser['avatar_id']}" 
                    : "$baseUrl/assets/img/default-avatar.png";
                ?>
                <div class="col-4 text-center">
                  <a href="<?= $baseUrl ?>/?page=profile&id=<?= $followedUser['id'] ?>" class="text-decoration-none">
                    <img src="<?= $followedAvatarUrl ?>" alt="<?= htmlspecialchars( $followedUser['username'] ) ?>" 
                         class="rounded-circle mb-2" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="small text-truncate"><?= htmlspecialchars( $followedUser['username'] ) ?></div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            
            <?php if( $followingCount > 6 ): ?>
              <div class="text-center mt-3">
                <a href="#" class="btn btn-sm btn-outline-secondary view-all-following-btn"
                   data-user-id="<?= $profileUser['id'] ?>">
                  View All
                </a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Delete Widget Confirmation Modal -->
  <?php if( $isOwnProfile ): ?>
    <div class="modal fade" id="deleteWidgetModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Widget</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this widget? This action can't be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <form method="post" action="">
              <input type="hidden" name="action" value="delete_widget">
              <input type="hidden" name="widget_id" id="delete-widget-id">
              <button type="submit" class="btn btn-danger">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>
