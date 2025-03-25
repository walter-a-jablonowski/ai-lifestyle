<?php
use AILifestyle\Config\Database;

// Get database connection
$db = Database::getInstance()->getConnection();

// Get parameters
$page = $_GET['page'] ?? 0;
$query = $_GET['q'] ?? '';
$tag = $_GET['tag'] ?? '';
$offset = $page * 20;
$limit = 20;

// Prepare base query
$baseQuery = "
  SELECT w.*, u.username, u.avatar_id,
  (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
  (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
  " . ($isLoggedIn ? "(SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked" : "0 as is_liked") . "
  FROM widgets w
  JOIN users u ON w.user_id = u.id
";

$params = [];
if( $isLoggedIn )
  $params[] = $auth->getCurrentUserId();

// Search by tag
if( ! empty( $tag ) )
{
  $baseQuery .= "
    JOIN widget_tags wt ON w.id = wt.widget_id
    JOIN tags t ON wt.tag_id = t.id
    WHERE t.name = ?
  ";
  $params[] = $tag;
}
// Search by query
elseif( ! empty( $query ) )
{
  $baseQuery .= "
    WHERE w.short_text LIKE ? 
    OR w.full_text LIKE ?
    OR u.username LIKE ?
  ";
  $searchTerm = "%{$query}%";
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $params[] = $searchTerm;
}

// Order by and limit
$baseQuery .= "
  ORDER BY w.created_at DESC
  LIMIT ?, ?
";
$params[] = $offset;
$params[] = $limit;

// Execute query
$stmt = $db->prepare( $baseQuery );
$stmt->execute( $params );
$results = $stmt->fetchAll();

// Function to format date
function formatDate( $date )
{
  $timestamp = strtotime( $date );
  $now = time();
  $diff = $now - $timestamp;
  
  if( $diff < 60 )
    return "just now";
  elseif( $diff < 3600 )
  {
    $minutes = floor( $diff / 60 );
    return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 86400 )
  {
    $hours = floor( $diff / 3600 );
    return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 604800 )
  {
    $days = floor( $diff / 86400 );
    return $days . " day" . ($days > 1 ? "s" : "") . " ago";
  }
  else
    return date( "M j, Y", $timestamp );
}

// Get tags for widgets
function getWidgetTags( $widgetId, $db )
{
  $tagQuery = $db->prepare(
    "SELECT t.id, t.name
     FROM tags t
     JOIN widget_tags wt ON t.id = wt.tag_id
     WHERE wt.widget_id = ?"
  );
  $tagQuery->execute( [$widgetId] );
  return $tagQuery->fetchAll();
}

// Start output buffer to capture HTML
ob_start();

foreach( $results as $widget ):
  $tags = getWidgetTags( $widget['id'], $db );
  $avatarUrl = $widget['avatar_id'] 
    ? "$baseUrl/uploads/{$widget['avatar_id']}" 
    : "$baseUrl/assets/img/default-avatar.png";
?>
  <div class="widget">
    <div class="widget-header">
      <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>">
        <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars( $widget['username'] ) ?>" class="widget-avatar">
      </a>
      <div>
        <a href="<?= $baseUrl ?>/?page=profile&id=<?= $widget['user_id'] ?>" class="widget-username text-decoration-none">
          <?= htmlspecialchars( $widget['username'] ) ?>
        </a>
        <div class="widget-time"><?= formatDate( $widget['created_at'] ) ?></div>
      </div>
    </div>
    
    <div class="widget-content">
      <div class="widget-text">
        <?= htmlspecialchars( $widget['short_text'] ) ?>
        
        <?php if( ! empty( $widget['full_text'] ) ): ?>
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
      
      <?php if( $widget['media_type'] !== 'none' && ! empty( $widget['media_content'] ) ): ?>
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
      
      <?php if( ! empty( $tags ) ): ?>
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
            $commentQuery->execute( [$widget['id']] );
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
<?php endforeach;

$html = ob_get_clean();

// Check if there are more results
$hasMore = count( $results ) >= $limit;

// Return JSON response
echo json_encode([
  'success' => true,
  'html' => $html,
  'hasMore' => $hasMore
]);
