<div class="container py-4">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Msgs</h1>
        
        <div class="btn-group">
          <?php if ($unreadCount > 0): ?>
            <a href="<?= $baseUrl ?>/?page=msgs&mark_as_read=all" class="btn btn-outline-primary">
              <i class="bi bi-check-all me-1"></i> Mark All as Read
            </a>
          <?php endif; ?>
          
          <?php if ($sumMsgs > 0): ?>
            <a href="<?= $baseUrl ?>/?page=msgs&delete=all" class="btn btn-outline-danger" 
               onclick="return confirm('Are you sure you want to delete all msgs?')">
              <i class="bi bi-trash me-1"></i> Clear All
            </a>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if (empty($msgs)): ?>
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3">No Msgs</h4>
            <p class="text-muted">You don't have any msgs at the moment.</p>
          </div>
        </div>
      <?php else: ?>
        <div class="card">
          <div class="list-group list-group-flush">
            <?php foreach ($msgs as $msg): 
              // Get avatar URL
              $avatarUrl = $msg['avatar_id'] 
                ? "$baseUrl/uploads/{$msg['avatar_id']}" 
                : "$baseUrl/assets/img/default-avatar.png";
              
              // Format date
              $timestamp = strtotime($msg['created_at']);
              $now = time();
              $diff = $now - $timestamp;
              
              if ($diff < 60) {
                $timeAgo = "just now";
              } elseif ($diff < 3600) {
                $minutes = floor($diff / 60);
                $timeAgo = $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
              } elseif ($diff < 86400) {
                $hours = floor($diff / 3600);
                $timeAgo = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
              } elseif ($diff < 604800) {
                $days = floor($diff / 86400);
                $timeAgo = $days . " day" . ($days > 1 ? "s" : "") . " ago";
              } else {
                $timeAgo = date("M j, Y", $timestamp);
              }
              
              // Determine msg icon and link
              $icon = 'bi-bell';
              $link = '#';
              
              switch ($msg['type']) {
                case 'like':
                  $icon = 'bi-heart-fill text-danger';
                  $link = "$baseUrl/?page=widget&id={$msg['entity_id']}";
                  break;
                case 'comment':
                  $icon = 'bi-chat-fill text-primary';
                  $link = "$baseUrl/?page=widget&id={$msg['entity_id']}";
                  break;
                case 'follow':
                  $icon = 'bi-person-plus-fill text-success';
                  $link = "$baseUrl/?page=profile&id={$msg['actor_id']}";
                  break;
                case 'mention':
                  $icon = 'bi-at text-info';
                  $link = "$baseUrl/?page=widget&id={$msg['entity_id']}";
                  break;
                case 'system':
                  $icon = 'bi-gear-fill text-secondary';
                  break;
              }
            ?>
              <div class="list-group-item msg-item <?= $msg['is_read'] ? '' : 'unread' ?>">
                <div class="d-flex">
                  <div class="flex-shrink-0">
                    <?php if ($msg['actor_id']): ?>
                      <a href="<?= $baseUrl ?>/?page=profile&id=<?= $msg['actor_id'] ?>">
                        <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($msg['username']) ?>" 
                             class="rounded-circle msg-avatar">
                      </a>
                    <?php else: ?>
                      <div class="msg-icon-wrapper">
                        <i class="bi <?= $icon ?>"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                  
                  <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-start">
                      <a href="<?= $link ?>" class="msg-link">
                        <?= $msg['content'] ?>
                      </a>
                      <small class="text-muted msg-time"><?= $timeAgo ?></small>
                    </div>
                    
                    <div class="msg-actions mt-2">
                      <?php if (!$msg['is_read']): ?>
                        <a href="<?= $baseUrl ?>/?page=msgs&mark_as_read=<?= $msg['id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-check"></i> Mark as Read
                        </a>
                      <?php endif; ?>
                      
                      <a href="<?= $baseUrl ?>/?page=msgs&delete=<?= $msg['id'] ?>" 
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Are you sure you want to delete this msg?')">
                        <i class="bi bi-trash"></i> Delete
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        
        <?php if ($pagesSum > 1): ?>
          <nav aria-label="Msgs pagination" class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl ?>/?page=msgs&page_num=<?= $page - 1 ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
              
              <?php for ($i = 1; $i <= $pagesSum; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                  <a class="page-link" href="<?= $baseUrl ?>/?page=msgs&page_num=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              
              <li class="page-item <?= $page >= $pagesSum ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl ?>/?page=msgs&page_num=<?= $page + 1 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
