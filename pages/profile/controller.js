document.addEventListener('DOMContentLoaded', function() {
  // Media type selector handling
  const mediaTypeSelect = document.getElementById('media_type');
  const mediaContentContainer = document.getElementById('media-content-container');
  
  if (mediaTypeSelect) {
    mediaTypeSelect.addEventListener('change', function() {
      const mediaType = this.value;
      mediaContentContainer.innerHTML = '';
      mediaContentContainer.classList.add('d-none');
      
      if (mediaType !== 'none') {
        mediaContentContainer.classList.remove('d-none');
        
        if (mediaType === 'image') {
          mediaContentContainer.innerHTML = `
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
          `;
        } else if (mediaType === 'video') {
          mediaContentContainer.innerHTML = `
            <label for="media_content" class="form-label">YouTube Video URL</label>
            <input type="url" class="form-control" id="media_content" name="media_content" 
                   placeholder="https://www.youtube.com/watch?v=...">
            <div class="form-text">Enter a YouTube video URL (youtube.com or youtu.be)</div>
          `;
        } else if (mediaType === 'weblink') {
          mediaContentContainer.innerHTML = `
            <label for="media_content" class="form-label">Web Link URL</label>
            <input type="url" class="form-control" id="media_content" name="media_content" 
                   placeholder="https://...">
          `;
        } else if (mediaType === 'map') {
          mediaContentContainer.innerHTML = `
            <label for="media_content" class="form-label">Google Maps Embed URL</label>
            <input type="url" class="form-control" id="media_content" name="media_content" 
                   placeholder="https://www.google.com/maps/embed?...">
            <div class="form-text">Enter a Google Maps embed URL</div>
          `;
        }
      }
    });
  }
  
  // Follow/Unfollow button
  const followButton = document.getElementById('follow-button');
  
  if (followButton) {
    followButton.addEventListener('click', function() {
      const userId = this.getAttribute('data-user-id');
      const isFollowing = this.getAttribute('data-following') === '1';
      
      fetch('/ajax.php?action=profile&handler=follow', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.action === 'follow') {
            this.classList.remove('btn-primary');
            this.classList.add('btn-secondary');
            this.innerHTML = '<i class="bi bi-person-dash"></i> Unfollow';
            this.setAttribute('data-following', '1');
          } else {
            this.classList.remove('btn-secondary');
            this.classList.add('btn-primary');
            this.innerHTML = '<i class="bi bi-person-plus"></i> Follow';
            this.setAttribute('data-following', '0');
          }
          
          // Reload the page to update follower counts
          window.location.reload();
        } else {
          alert(data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    });
  }
  
  // Like/Unlike buttons
  document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (!isLoggedIn) {
        window.location.href = `${baseUrl}/?page=login`;
        return;
      }
      
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
      
      if (!isLoggedIn) {
        window.location.href = `${baseUrl}/?page=login`;
        return;
      }
      
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
  
  // Delete widget button
  document.querySelectorAll('.delete-widget-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      const widgetId = this.getAttribute('data-widget-id');
      document.getElementById('delete-widget-id').value = widgetId;
      
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteWidgetModal'));
      deleteModal.show();
    });
  });
  
  // Load more widgets
  const loadMoreWidgetsBtn = document.querySelector('.load-more-btn[data-content-type="widgets"]');
  
  if (loadMoreWidgetsBtn) {
    loadMoreWidgetsBtn.addEventListener('click', function() {
      const page = parseInt(this.getAttribute('data-page'));
      const userId = this.getAttribute('data-user-id');
      
      fetch(`/ajax.php?action=profile&handler=load_more_widgets&user_id=${userId}&page=${page}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Append new widgets to container
            const widgetsContainer = document.getElementById('widgets-container');
            widgetsContainer.insertAdjacentHTML('beforeend', data.html);
            
            // Update page number
            this.setAttribute('data-page', page + 1);
            
            // Hide button if no more widgets
            if (!data.hasMore) {
              this.parentElement.style.display = 'none';
            }
            
            // Re-initialize event listeners for new elements
            initializeEventListeners();
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
    });
  }
  
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
  
  // Function to initialize event listeners for dynamically added elements
  function initializeEventListeners() {
    // Re-initialize like buttons
    document.querySelectorAll('.like-button:not(.initialized)').forEach(button => {
      button.classList.add('initialized');
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!isLoggedIn) {
          window.location.href = `${baseUrl}/?page=login`;
          return;
        }
        
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
    
    // Re-initialize full text toggles
    document.querySelectorAll('.full-text-toggle:not(.initialized)').forEach(toggle => {
      toggle.classList.add('initialized');
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
  }
});
