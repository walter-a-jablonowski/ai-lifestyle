document.addEventListener('DOMContentLoaded', function() {
  // Like/Unlike button
  const likeButton = document.querySelector('.like-button');
  if (likeButton) {
    likeButton.addEventListener('click', function(e) {
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
  }
  
  // Follow/Unfollow button
  const followButton = document.querySelector('.follow-button');
  if (followButton) {
    followButton.addEventListener('click', function() {
      const userId = this.getAttribute('data-user-id');
      
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
            this.classList.add('btn-outline-primary');
            this.innerHTML = '<i class="bi bi-person-check-fill"></i> Following';
          } else {
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            this.innerHTML = '<i class="bi bi-person-plus"></i> Follow';
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
  }
  
  // Comment form submission
  const commentForm = document.querySelector('.comment-form');
  if (commentForm) {
    commentForm.addEventListener('submit', function(e) {
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
          const commentCountElement = document.getElementById('comment-count');
          commentCountElement.textContent = data.commentCount;
          
          // Add new comment to the list
          const commentsContainer = document.getElementById('comments-container');
          
          // Remove empty state if it exists
          const emptyState = commentsContainer.querySelector('.text-center.py-4');
          if (emptyState) {
            emptyState.remove();
          }
          
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
  }
  
  // Copy share link
  const copyLinkBtn = document.querySelector('.copy-link-btn');
  if (copyLinkBtn) {
    copyLinkBtn.addEventListener('click', function() {
      const shareUrl = document.getElementById('share-url');
      shareUrl.select();
      document.execCommand('copy');
      
      // Change button text temporarily
      const originalText = this.textContent;
      this.textContent = 'Copied!';
      
      setTimeout(() => {
        this.textContent = originalText;
      }, 2000);
    });
  }
});
