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
  
  // Load more search results
  const loadMoreBtn = document.querySelector('.load-more-btn[data-content-type="search"]');
  
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
      const page = parseInt(this.getAttribute('data-page'));
      const query = this.getAttribute('data-query');
      const tag = this.getAttribute('data-tag');
      
      let url = `/ajax.php?action=search&handler=load_more&page=${page}`;
      
      if (query) {
        url += `&q=${encodeURIComponent(query)}`;
      }
      
      if (tag) {
        url += `&tag=${encodeURIComponent(tag)}`;
      }
      
      fetch(url)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Append new results to container
            const resultsContainer = document.getElementById('search-results-container');
            resultsContainer.insertAdjacentHTML('beforeend', data.html);
            
            // Update page number
            this.setAttribute('data-page', page + 1);
            
            // Hide button if no more results
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
    
    // Re-initialize comment forms
    document.querySelectorAll('.comment-form:not(.initialized)').forEach(form => {
      form.classList.add('initialized');
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
  }
});
