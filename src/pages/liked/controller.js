document.addEventListener('DOMContentLoaded', function() {
  // Show/hide full text
  document.querySelectorAll('.full-text-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      
      const widgetId = this.getAttribute('data-widget-id');
      const fullTextElement = document.getElementById(`full-text-${widgetId}`);
      
      if (fullTextElement.classList.contains('d-none')) {
        fullTextElement.classList.remove('d-none');
        this.textContent = 'Show Less';
      } else {
        fullTextElement.classList.add('d-none');
        this.textContent = 'Show More';
      }
    });
  });
  
  // Like/Unlike buttons
  document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      const widgetId = this.getAttribute('data-widget-id');
      const likeIcon = this.querySelector('i');
      const likeCount = this.querySelector('.like-count');
      
      fetch('/ajax.php?action=profile&handler=like', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `widget_id=${widgetId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.action === 'like') {
            this.classList.add('liked');
            likeIcon.classList.remove('bi-heart');
            likeIcon.classList.add('bi-heart-fill');
          } else {
            this.classList.remove('liked');
            likeIcon.classList.remove('bi-heart-fill');
            likeIcon.classList.add('bi-heart');
            
            // If we're on the liked page, remove the widget after unliking
            if (window.location.href.includes('page=liked')) {
              const widget = this.closest('.widget');
              if (widget) {
                widget.style.opacity = '0';
                setTimeout(() => {
                  widget.remove();
                  
                  // Check if there are no more widgets
                  const widgetsContainer = document.getElementById('liked-widgets-container');
                  if (widgetsContainer && widgetsContainer.children.length === 0) {
                    // Show empty state
                    widgetsContainer.innerHTML = `
                      <div class="card">
                        <div class="card-body text-center py-5">
                          <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
                          <h4 class="mt-3">No Liked Content</h4>
                          <p class="text-muted">You haven't liked any content yet. Explore and find content that inspires you!</p>
                          <a href="${baseUrl}/?page=home" class="btn btn-primary mt-2">Explore Content</a>
                        </div>
                      </div>
                    `;
                  }
                }, 300);
              }
            }
          }
          
          likeCount.textContent = data.likeCount;
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });
  });
  
  // Comment form submission
  document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const widgetId = this.getAttribute('data-widget-id');
      const commentInput = this.querySelector('.comment-input');
      const commentContent = commentInput.value.trim();
      
      if (commentContent === '') {
        return;
      }
      
      fetch('/ajax.php?action=profile&handler=comment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `widget_id=${widgetId}&content=${encodeURIComponent(commentContent)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear input
          commentInput.value = '';
          
          // Update comment count
          const commentCountElement = document.getElementById(`comment-count-${widgetId}`);
          commentCountElement.textContent = data.commentCount;
          
          // Add new comment to the list
          const commentsContainer = document.getElementById(`comments-container-${widgetId}`);
          const newComment = document.createElement('div');
          newComment.className = 'comment';
          newComment.innerHTML = `
            <a href="${baseUrl}/?page=profile&id=${data.comment.user_id}">
              <img src="${data.comment.avatar_url}" alt="${data.comment.username}" class="comment-avatar">
            </a>
            <div class="comment-content">
              <div class="comment-header">
                <a href="${baseUrl}/?page=profile&id=${data.comment.user_id}" class="comment-username text-decoration-none">
                  ${data.comment.username}
                </a>
                <span class="comment-time">${data.comment.created_at}</span>
              </div>
              <p class="comment-text">${data.comment.content.replace(/\n/g, '<br>')}</p>
            </div>
          `;
          
          commentsContainer.appendChild(newComment);
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });
  });
  
  // Load more comments
  document.querySelectorAll('.load-more-btn[data-content-type="comments"]').forEach(button => {
    button.addEventListener('click', function() {
      const widgetId = this.getAttribute('data-widget-id');
      const commentsContainer = document.getElementById(`comments-container-${widgetId}`);
      const offset = commentsContainer.querySelectorAll('.comment').length;
      
      fetch(`/ajax.php?action=profile&handler=load_more_comments&widget_id=${widgetId}&offset=${offset}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Append new comments to container
            commentsContainer.insertAdjacentHTML('beforeend', data.html);
            
            // Hide button if no more comments
            if (!data.hasMore) {
              this.parentElement.style.display = 'none';
            }
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
    });
  });
});
