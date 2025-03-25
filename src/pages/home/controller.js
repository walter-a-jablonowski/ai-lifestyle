/**
 * Home page controller
 * Handles client-side interactions for the home page
 */

document.addEventListener('DOMContentLoaded', function() {
  // Toggle full text display
  const fullTextToggles = document.querySelectorAll('.full-text-toggle');
  fullTextToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      const widgetId = this.dataset.widgetId;
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
  
  // Handle like button clicks
  const likeButtons = document.querySelectorAll('.like-button');
  likeButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Only proceed if user is logged in
      if (!isUserLoggedIn()) {
        window.location.href = `${baseUrl}/?page=login`;
        return;
      }
      
      const widgetId = this.dataset.widgetId;
      const likeCountElement = this.querySelector('.like-count');
      const iconElement = this.querySelector('i');
      const isLiked = this.classList.contains('liked');
      
      // Send AJAX request to like/unlike
      fetch(`${baseUrl}/ajax.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'toggle_like',
          widget_id: widgetId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update UI
          if (data.is_liked) {
            this.classList.add('liked');
            iconElement.classList.remove('bi-heart');
            iconElement.classList.add('bi-heart-fill');
          } else {
            this.classList.remove('liked');
            iconElement.classList.remove('bi-heart-fill');
            iconElement.classList.add('bi-heart');
          }
          
          // Update like count
          likeCountElement.textContent = data.like_count;
        }
      })
      .catch(error => {
        console.error('Error toggling like:', error);
      });
    });
  });
  
  // Handle comment form submission
  const commentForms = document.querySelectorAll('.comment-form');
  commentForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const widgetId = this.dataset.widgetId;
      const commentInput = this.querySelector('.comment-input');
      const commentContent = commentInput.value.trim();
      
      if (!commentContent) return;
      
      // Send AJAX request to add comment
      fetch(`${baseUrl}/ajax.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'add_comment',
          widget_id: widgetId,
          content: commentContent
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear input
          commentInput.value = '';
          
          // Add new comment to the list
          const commentsContainer = document.getElementById(`comments-container-${widgetId}`);
          commentsContainer.innerHTML += createCommentHTML(data.comment);
          
          // Update comment count
          const commentCount = document.getElementById(`comment-count-${widgetId}`);
          commentCount.textContent = parseInt(commentCount.textContent) + 1;
        }
      })
      .catch(error => {
        console.error('Error adding comment:', error);
      });
    });
  });
  
  // Handle load more widgets
  const loadMoreWidgetsBtn = document.querySelector('.load-more-btn[data-content-type="widgets"]');
  if (loadMoreWidgetsBtn) {
    loadMoreWidgetsBtn.addEventListener('click', function() {
      const page = parseInt(this.dataset.page) + 1;
      
      // Show loading state
      this.textContent = 'Loading...';
      this.disabled = true;
      
      // Send AJAX request to load more widgets
      fetch(`${baseUrl}/ajax.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'load_more_widgets',
          page: page
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Append new widgets
          const widgetsContainer = document.getElementById('widgets-container');
          data.widgets.forEach(widget => {
            widgetsContainer.innerHTML += widget.html;
          });
          
          // Update button state
          this.textContent = 'Load More';
          this.disabled = false;
          this.dataset.page = page;
          
          // Hide button if no more widgets
          if (!data.has_more) {
            this.parentElement.remove();
          }
          
          // Reinitialize event listeners for new elements
          initializeEventListeners();
        }
      })
      .catch(error => {
        console.error('Error loading more widgets:', error);
        this.textContent = 'Load More';
        this.disabled = false;
      });
    });
  }
  
  // Handle load more comments
  const loadMoreCommentsButtons = document.querySelectorAll('.load-more-btn[data-content-type="comments"]');
  loadMoreCommentsButtons.forEach(button => {
    button.addEventListener('click', function() {
      const widgetId = this.dataset.widgetId;
      const commentsContainer = document.getElementById(`comments-container-${widgetId}`);
      const commentCount = commentsContainer.querySelectorAll('.comment').length;
      
      // Show loading state
      this.textContent = 'Loading...';
      this.disabled = true;
      
      // Send AJAX request to load more comments
      fetch(`${baseUrl}/ajax.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'load_more_comments',
          widget_id: widgetId,
          offset: commentCount
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Append new comments
          data.comments.forEach(comment => {
            commentsContainer.innerHTML += createCommentHTML(comment);
          });
          
          // Update button state
          this.textContent = 'Load More Comments';
          this.disabled = false;
          
          // Hide button if no more comments
          if (!data.has_more) {
            this.parentElement.remove();
          }
        }
      })
      .catch(error => {
        console.error('Error loading more comments:', error);
        this.textContent = 'Load More Comments';
        this.disabled = false;
      });
    });
  });
  
  // Helper function to create comment HTML
  function createCommentHTML(comment) {
    const avatarUrl = comment.avatar_id 
      ? `${baseUrl}/uploads/${comment.avatar_id}` 
      : `${baseUrl}/assets/img/default-avatar.png`;
    
    return `
      <div class="comment">
        <a href="${baseUrl}/?page=profile&id=${comment.user_id}">
          <img src="${avatarUrl}" alt="${comment.username}" class="comment-avatar">
        </a>
        <div class="comment-content">
          <div class="comment-header">
            <a href="${baseUrl}/?page=profile&id=${comment.user_id}" class="comment-username text-decoration-none">
              ${comment.username}
            </a>
            <span class="comment-time">${comment.formatted_date}</span>
          </div>
          <p class="comment-text">${comment.content.replace(/\n/g, '<br>')}</p>
        </div>
      </div>
    `;
  }
  
  // Helper function to check if user is logged in
  function isUserLoggedIn() {
    return typeof isLoggedIn !== 'undefined' && isLoggedIn === true;
  }
  
  // Function to initialize event listeners for dynamically added elements
  function initializeEventListeners() {
    // Re-initialize full text toggles
    document.querySelectorAll('.full-text-toggle:not([data-initialized])').forEach(toggle => {
      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        const widgetId = this.dataset.widgetId;
        const fullTextElement = document.getElementById(`full-text-${widgetId}`);
        
        if (fullTextElement.classList.contains('d-none')) {
          fullTextElement.classList.remove('d-none');
          this.textContent = 'Show Less';
        } else {
          fullTextElement.classList.add('d-none');
          this.textContent = 'Show More';
        }
      });
      toggle.setAttribute('data-initialized', 'true');
    });
    
    // Re-initialize like buttons
    document.querySelectorAll('.like-button:not([data-initialized])').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!isUserLoggedIn()) {
          window.location.href = `${baseUrl}/?page=login`;
          return;
        }
        
        const widgetId = this.dataset.widgetId;
        const likeCountElement = this.querySelector('.like-count');
        const iconElement = this.querySelector('i');
        const isLiked = this.classList.contains('liked');
        
        fetch(`${baseUrl}/ajax.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'toggle_like',
            widget_id: widgetId
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (data.is_liked) {
              this.classList.add('liked');
              iconElement.classList.remove('bi-heart');
              iconElement.classList.add('bi-heart-fill');
            } else {
              this.classList.remove('liked');
              iconElement.classList.remove('bi-heart-fill');
              iconElement.classList.add('bi-heart');
            }
            
            likeCountElement.textContent = data.like_count;
          }
        })
        .catch(error => {
          console.error('Error toggling like:', error);
        });
      });
      button.setAttribute('data-initialized', 'true');
    });
  }
});
