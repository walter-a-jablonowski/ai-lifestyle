<?php
// Get avatar URL
$avatarUrl = $widget['avatar_id'] 
  ? "$baseUrl/uploads/{$widget['avatar_id']}" 
  : "$baseUrl/assets/img/default-avatar.png";
?>

<div class="container py-4">
  <div class="row">
    <div class="col-lg-8">
      <div class="widget-detail">
        <div class="widget-header">
          <div class="d-flex align-items-center">
            <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>">
              <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($widget['username']) ?>" class="widget-avatar">
            </a>
            <div>
              <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>" class="widget-username text-decoration-none">
                <?= htmlspecialchars($widget['username']) ?>
              </a>
              <div class="widget-time"><?= formatDate($widget['created_at']) ?></div>
            </div>
          </div>
          
          <?php if ($isLoggedIn && $widget['user_id'] !== $auth->getCurrentUserId()): ?>
            <div class="follow-button-container">
              <button class="btn <?= $widget['is_following'] ? 'btn-outline-primary' : 'btn-primary' ?> follow-button" 
                      data-user-id="<?= $widget['user_id'] ?>">
                <i class="bi <?= $widget['is_following'] ? 'bi-person-check-fill' : 'bi-person-plus' ?>"></i>
                <?= $widget['is_following'] ? 'Following' : 'Follow' ?>
              </button>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="widget-content">
          <div class="widget-text">
            <h4><?= htmlspecialchars($widget['short_text']) ?></h4>
            
            <?php if (!empty($widget['full_text'])): ?>
              <div class="mt-3">
                <?php if ($widget['is_html']): ?>
                  <?= $widget['full_text'] ?>
                <?php else: ?>
                  <?= nl2br(htmlspecialchars($widget['full_text'])) ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          
          <?php if ($widget['media_type'] !== 'none' && !empty($widget['media_content'])): ?>
            <div class="widget-media">
              <?php if ($widget['media_type'] === 'image'): ?>
                <img src="<?= $baseUrl ?>/uploads/<?= $widget['media_content'] ?>" 
                     alt="<?= htmlspecialchars($widget['original_file_name'] ?? 'Image') ?>"
                     class="img-fluid">
              <?php elseif ($widget['media_type'] === 'video'): ?>
                <?php 
                  // Extract video ID from YouTube URL
                  $videoId = '';
                  if (strpos($widget['media_content'], 'youtube.com') !== false) {
                    parse_str(parse_url($widget['media_content'], PHP_URL_QUERY), $params);
                    $videoId = $params['v'] ?? '';
                  } elseif (strpos($widget['media_content'], 'youtu.be') !== false) {
                    $videoId = substr(parse_url($widget['media_content'], PHP_URL_PATH), 1);
                  }
                ?>
                <?php if ($videoId): ?>
                  <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" 
                            title="YouTube video" allowfullscreen></iframe>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning">Invalid video URL</div>
                <?php endif; ?>
              <?php elseif ($widget['media_type'] === 'weblink'): ?>
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">External Link</h5>
                    <a href="<?= htmlspecialchars($widget['media_content']) ?>" 
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-outline-primary">
                      <i class="bi bi-box-arrow-up-right"></i> Visit Link
                    </a>
                  </div>
                </div>
              <?php elseif ($widget['media_type'] === 'map'): ?>
                <div class="ratio ratio-4x3">
                  <iframe src="<?= htmlspecialchars($widget['media_content']) ?>" 
                          title="Google Maps" allowfullscreen></iframe>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($tags)): ?>
            <div class="widget-tags">
              <?php foreach ($tags as $tag): ?>
                <a href="<?= $baseUrl ?>/?page=search&tag=<?= urlencode($tag['name']) ?>" 
                   class="widget-tag">
                  #<?= htmlspecialchars($tag['name']) ?>
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
          
          <a href="#comments-section" class="widget-action-btn">
            <i class="bi bi-chat"></i>
            <span id="comment-count"><?= $widget['comment_count'] ?></span>
          </a>
          
          <a href="#" class="widget-action-btn share-button" data-bs-toggle="modal" data-bs-target="#shareModal">
            <i class="bi bi-share"></i>
            <span>Share</span>
          </a>
        </div>
      </div>
      
      <div class="comments-section mt-4" id="comments-section">
        <h4 class="mb-3">Comments (<?= $widget['comment_count'] ?>)</h4>
        
        <?php if ($isLoggedIn): ?>
          <form class="comment-form mb-4" data-widget-id="<?= $widget['id'] ?>">
            <div class="input-group">
              <input type="text" class="form-control comment-input" 
                     placeholder="Add a comment..." required>
              <button class="btn btn-primary" type="submit">Post</button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-info mb-4">
            <a href="<?= $baseUrl ?>/?page=login" class="alert-link">Sign in</a> to add a comment.
          </div>
        <?php endif; ?>
        
        <div id="comments-container">
          <?php if (empty($comments)): ?>
            <div class="text-center py-4">
              <p class="text-muted">No comments yet. Be the first to comment!</p>
            </div>
          <?php else: ?>
            <?php foreach ($comments as $comment):
              $commentAvatarUrl = $comment['avatar_id'] 
                ? "$baseUrl/uploads/{$comment['avatar_id']}" 
                : "$baseUrl/assets/img/default-avatar.png";
            ?>
              <div class="comment">
                <a href="<?= $baseUrl ?>/?page=profile&id=<?= $comment['user_id'] ?>">
                  <img src="<?= $commentAvatarUrl ?>" alt="<?= htmlspecialchars($comment['username']) ?>" class="comment-avatar">
                </a>
                <div class="comment-content">
                  <div class="comment-header">
                    <a href="<?= $baseUrl ?>/?page=profile&id=<?= $comment['user_id'] ?>" class="comment-username text-decoration-none">
                      <?= htmlspecialchars($comment['username']) ?>
                    </a>
                    <span class="comment-time"><?= formatDate($comment['created_at']) ?></span>
                  </div>
                  <p class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">About the Author</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>">
              <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($widget['username']) ?>" 
                   class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
            </a>
            <div>
              <h5 class="mb-1">
                <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>" class="text-decoration-none">
                  <?= htmlspecialchars($widget['username']) ?>
                </a>
              </h5>
              <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                View Profile
              </a>
            </div>
          </div>
          
          <?php if (!empty($widget['summary'])): ?>
            <p><?= htmlspecialchars($widget['summary']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if (!empty($relatedWidgets)): ?>
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Related Content</h5>
          </div>
          <div class="list-group list-group-flush">
            <?php foreach ($relatedWidgets as $relatedWidget):
              $relatedAvatarUrl = $relatedWidget['avatar_id'] 
                ? "$baseUrl/uploads/{$relatedWidget['avatar_id']}" 
                : "$baseUrl/assets/img/default-avatar.png";
            ?>
              <a href="<?= $baseUrl ?>/?page=widget&id=<?= $relatedWidget['id'] ?>" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><?= htmlspecialchars(substr($relatedWidget['short_text'], 0, 50)) ?><?= strlen($relatedWidget['short_text']) > 50 ? '...' : '' ?></h6>
                  <small class="text-muted"><?= formatDate($relatedWidget['created_at']) ?></small>
                </div>
                <div class="d-flex align-items-center mt-2">
                  <img src="<?= $relatedAvatarUrl ?>" alt="<?= htmlspecialchars($relatedWidget['username']) ?>" 
                       class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                  <small><?= htmlspecialchars($relatedWidget['username']) ?></small>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel">Share this content</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Share this content:</p>
        
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="share-url" 
                 value="<?= $baseUrl ?>/?page=widget&id=<?= $widget['id'] ?>" readonly>
          <button class="btn btn-outline-secondary copy-link-btn" type="button">Copy</button>
        </div>
        
        <div class="share-buttons mt-3">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($baseUrl . '/?page=widget&id=' . $widget['id']) ?>" 
             target="_blank" class="btn btn-outline-primary me-2">
            <i class="bi bi-facebook"></i> Facebook
          </a>
          
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode($baseUrl . '/?page=widget&id=' . $widget['id']) ?>&text=<?= urlencode(substr($widget['short_text'], 0, 100)) ?>" 
             target="_blank" class="btn btn-outline-info me-2">
            <i class="bi bi-twitter"></i> Twitter
          </a>
          
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($baseUrl . '/?page=widget&id=' . $widget['id']) ?>" 
             target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-linkedin"></i> LinkedIn
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
