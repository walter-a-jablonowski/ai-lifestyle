document.addEventListener('DOMContentLoaded', function() {
  // Real-time updates
  if (isLoggedIn) {
    // Check for new every 60 seconds
    setInterval( checkNewMsgs, 60000);
  }
  
  // Confirm deletion
  document.querySelectorAll('.btn-outline-danger').forEach(button => {
    button.addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to delete this?')) {
        e.preventDefault();
      }
    });
  });
  
  // Mark as read when clicked
  document.querySelectorAll('.msg-link').forEach(link => {
    link.addEventListener('click', function() {
      const msgItem = this.closest('.msg-item');
      if (msgItem && msgItem.classList.contains('unread')) {
        // Get ID from the mark as read button
        const markAsReadBtn = msgItem.querySelector('a[href*="mark_as_read="]');
        if (markAsReadBtn) {
          const url = markAsReadBtn.getAttribute('href');
          const msgId = url.split('mark_as_read=')[1];
          
          // Send AJAX request to mark as read
          fetch(`${baseUrl}/ajax.php?action=msgs&handler=mark_as_read&id=${msgId}`, {
            method: 'POST'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              msgItem.classList.remove('unread');
              markAsReadBtn.style.display = 'none';
              
              // Update unread count in navbar
              updateUnreadCount(-1);
            }
          })
          .catch(error => {
            console.error('Error highlight msg as read:', error);
          });
        }
      }
    });
  });
  
  // Function to check for new
  function checkNewMsgs() {
    fetch(`${baseUrl}/ajax.php?action=msgs&handler=check_new`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.hasNew) {
          // Show toast
          showMsgToast(data.newCount);
          
          // Update unread count in navbar
          updateUnreadCount(data.newCount);
          
          // Refresh page if user is on page
          if (window.location.href.includes('page=msgs')) {
            window.location.reload();
          }
        }
      })
      .catch(error => {
        console.error('Error checking for new:', error);
      });
  }
  
  // Function to show toast
  function showMsgToast(count) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toast-container';
      toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
      document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'msg-toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set toast content
    toast.innerHTML = `
      <div class="toast-header">
        <i class="bi bi-bell-fill me-2 text-primary"></i>
        <strong class="me-auto">New</strong>
        <small>Just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        You have ${count} new msgs.
        <div class="mt-2 pt-2 border-top">
          <a href="${baseUrl}/?page=msgs" class="btn btn-primary btn-sm">View</a>
        </div>
      </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
      this.remove();
    });
  }
  
  // Function to update unread count in navbar
  function updateUnreadCount(change) {
    const unreadBadge = document.querySelector('.msg-badge');
    if (unreadBadge) {
      let currentCount = parseInt(unreadBadge.textContent);
      if (isNaN(currentCount)) {
        currentCount = 0;
      }
      
      const newCount = currentCount + change;
      
      if (newCount > 0) {
        unreadBadge.textContent = newCount;
        unreadBadge.style.display = 'inline-block';
      } else {
        unreadBadge.style.display = 'none';
      }
    }
  }
});
