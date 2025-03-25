<div class="container">
  <div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
      <!-- Search Form -->
      <div class="card mb-4">
        <div class="card-body">
          <form action="" method="get" class="search-form">
            <input type="hidden" name="page" value="search">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search for widgets, users, or content..." value="<?= htmlspecialchars($query) ?>">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Search Results -->
      <?php if ($hasSearch): ?>
        <div class="search-results-header mb-3">
          <?php if (!empty($tag)): ?>
            <h2 class="h4">Widgets tagged with <span class="badge bg-primary">#<?= htmlspecialchars($tag) ?></span></h2>
          <?php else: ?>
            <h2 class="h4">Search results for "<?= htmlspecialchars($query) ?>"</h2>
          <?php endif; ?>
          
          <?php if (count($results) === 0): ?>
            <div class="alert alert-info mt-3">
              <i class="bi bi-info-circle"></i> No results found. Try a different search term or browse trending tags.
            </div>
          <?php else: ?>
            <p class="text-muted"><?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> found</p>
          <?php endif; ?>
        </div>
        
        <?php if (!empty($relatedTags)): ?>
          <div class="related-tags mb-4">
            <h5>Related Tags:</h5>
            <div class="tags-container">
              <?php foreach ($relatedTags as $relatedTag): ?>
                <a href="<?= $baseUrl ?>/?page=search&tag=<?= urlencode($relatedTag['name']) ?>" class="tag-badge">
                  #<?= htmlspecialchars($relatedTag['name']) ?>
                  <span class="tag-count">(<?= $relatedTag['count'] ?>)</span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="search-intro mb-4">
          <h2 class="h4">Search AI Lifestyle</h2>
          <p class="text-muted">Search for widgets, users, or content. You can also browse by tags.</p>
        </div>
      <?php endif; ?>
      
      <!-- Results List -->
      <div id="search-results-container">
        <?php foreach ($results as $widget): ?>
          <?php 
            $tags = getWidgetTags($widget['id'], $db);
            $avatarUrl = $widget['avatar_id'] 
              ? "$baseUrl/uploads/{$widget['avatar_id']}" 
              : "$baseUrl/assets/img/default-avatar.png";
          ?>
          <div class="widget">
            <div class="widget-header">
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
            
            <div class="widget-content">
              <div class="widget-text">
                <?= htmlspecialchars($widget['short_text']) ?>
                
                <?php if (!empty($widget['full_text'])): ?>
                  <div id="full-text-<?= $widget['id'] ?>" class="d-none mt-3">
                    <?php if ($widget['is_html']): ?>
                      <?= $widget['full_text'] ?>
                    <?php else: ?>
                      <?= nl2br(htmlspecialchars($widget['full_text'])) ?>
                    <?php endif; ?>
                  </div>
                  <a href="#" class="full-text-toggle text-primary d-block mt-2" data-widget-id="<?= $widget['id'] ?>">
                    Show More
                  </a>
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
                    
                    foreach ($comments as $comment):
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
                </div>
                
                <?php if ($widget['comment_count'] > 5): ?>
                  <div class="text-center my-3">
                    <button class="btn btn-sm btn-outline-secondary load-more-btn" 
                            data-content-type="comments" 
                            data-widget-id="<?= $widget['id'] ?>">
                      Load More Comments
                    </button>
                  </div>
                <?php endif; ?>
                
                <?php if ($isLoggedIn): ?>
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
      </div>
      
      <?php if (count($results) >= 20): ?>
        <div class="load-more-container">
          <button class="btn btn-outline-primary load-more-btn" 
                  data-content-type="search" 
                  data-page="1"
                  data-query="<?= htmlspecialchars($query) ?>"
                  data-tag="<?= htmlspecialchars($tag) ?>">
            Load More
          </button>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Trending Tags -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Trending Tags</h5>
        </div>
        <div class="card-body">
          <?php if (empty($trendingTags)): ?>
            <p class="text-muted">No trending tags at the moment.</p>
          <?php else: ?>
            <div class="tags-cloud">
              <?php foreach ($trendingTags as $trendingTag): ?>
                <a href="<?= $baseUrl ?>/?page=search&tag=<?= urlencode($trendingTag['name']) ?>" 
                   class="tag-badge" style="font-size: <?= min(1.5, 0.8 + ($trendingTag['count'] / 10)) ?>rem">
                  #<?= htmlspecialchars($trendingTag['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Search Tips -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Search Tips</h5>
        </div>
        <div class="card-body">
          <ul class="search-tips">
            <li>Use specific keywords to find relevant content</li>
            <li>Search for usernames to find specific users</li>
            <li>Click on tags to see related content</li>
            <li>Trending tags show what's popular right now</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
