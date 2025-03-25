/**
 * AI Lifestyle - Main JavaScript
 */

// Error handling for AJAX requests
const handleAjaxError = (error) => {
  console.error('AJAX Error:', error);
  
  // Create error message element
  const errorContainer = document.createElement('div');
  errorContainer.className = 'error-container';
  errorContainer.innerHTML = `
    <div class="error-icon">⚠️</div>
    <h1 class="h4 mb-3">Communication Error</h1>
    <p class="text-muted">There was a problem communicating with the server. Please try again later.</p>
    <button class="btn btn-primary mt-3" id="error-back-btn">Return to Home</button>
  `;
  
  // Replace body content with error
  document.body.innerHTML = '';
  document.body.appendChild(errorContainer);
  
  // Add event listener to back button
  document.getElementById('error-back-btn').addEventListener('click', () => {
    window.location.href = '/';
  });
};

// Helper function for AJAX requests
const fetchApi = async (url, options = {}) => {
  try {
    const defaultOptions = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    };
    
    const response = await fetch(url, { ...defaultOptions, ...options });
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'An error occurred');
    }
    
    return data;
  } catch (error) {
    // Handle network errors
    if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
      handleAjaxError(error);
    }
    throw error;
  }
};

// Like/unlike widget functionality
const setupWidgetLikes = () => {
  document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', async (e) => {
      e.preventDefault();
      
      const widgetId = button.dataset.widgetId;
      const isLiked = button.classList.contains('liked');
      
      try {
        const result = await fetchApi(`/ajax.php?action=widget&handler=like`, {
          method: 'POST',
          body: JSON.stringify({
            widget_id: widgetId,
            unlike: isLiked
          })
        });
        
        if (result.success) {
          // Toggle like status
          button.classList.toggle('liked');
          
          // Update like count
          const countElement = button.querySelector('.like-count');
          if (countElement) {
            let count = parseInt(countElement.textContent);
            count = isLiked ? count - 1 : count + 1;
            countElement.textContent = count;
          }
        }
      } catch (error) {
        console.error('Error liking widget:', error);
        alert('Update like status failed. Please try again.');
      }
    });
  });
};

// Load more content functionality
const setupLoadMore = () => {
  const loadMoreButtons = document.querySelectorAll('.load-more-btn');
  
  loadMoreButtons.forEach(button => {
    button.addEventListener('click', async () => {
      const contentType = button.dataset.contentType;
      const container = document.getElementById(`${contentType}-container`);
      const page = parseInt(button.dataset.page) || 1;
      const userId = button.dataset.userId || '';
      
      try {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        const result = await fetchApi(`/ajax.php?action=${contentType}&handler=load-more`, {
          method: 'POST',
          body: JSON.stringify({
            page: page + 1,
            user_id: userId
          })
        });
        
        if (result.success && result.data) {
          // Append new content
          container.insertAdjacentHTML('beforeend', result.data.html);
          
          // Update page number
          button.dataset.page = page + 1;
          
          // Hide button if no more content
          if (!result.data.has_more) {
            button.style.display = 'none';
          }
          
          // Reinitialize event listeners for new content
          setupWidgetLikes();
          setupCommentForms();
        }
      } catch (error) {
        console.error('Error loading more content:', error);
        alert('Load more content failed. Please try again.');
      } finally {
        button.disabled = false;
        button.textContent = 'Load More';
      }
    });
  });
};

// Comment form handling
const setupCommentForms = () => {
  document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const widgetId = form.dataset.widgetId;
      const commentInput = form.querySelector('.comment-input');
      const comment = commentInput.value.trim();
      
      if (!comment) return;
      
      try {
        const result = await fetchApi(`/ajax.php?action=widget&handler=comment`, {
          method: 'POST',
          body: JSON.stringify({
            widget_id: widgetId,
            comment: comment
          })
        });
        
        if (result.success && result.data) {
          // Add new comment to the list
          const commentsContainer = document.querySelector(`#comments-container-${widgetId}`);
          commentsContainer.insertAdjacentHTML('beforeend', result.data.html);
          
          // Clear input
          commentInput.value = '';
          
          // Update comment count
          const countElement = document.querySelector(`#comment-count-${widgetId}`);
          if (countElement) {
            let count = parseInt(countElement.textContent);
            countElement.textContent = count + 1;
          }
        }
      } catch (error) {
        console.error('Error posting comment:', error);
        alert('Post comment failed. Please try again.');
      }
    });
  });
};

// Widget full text toggle
const setupFullTextToggles = () => {
  document.querySelectorAll('.full-text-toggle').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      
      const widgetId = toggle.dataset.widgetId;
      const fullTextElement = document.querySelector(`#full-text-${widgetId}`);
      
      if (fullTextElement) {
        fullTextElement.classList.toggle('d-none');
        toggle.textContent = fullTextElement.classList.contains('d-none') ? 'Show More' : 'Show Less';
      }
    });
  });
};

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  setupWidgetLikes();
  setupLoadMore();
  setupCommentForms();
  setupFullTextToggles();
  
  // BS tooltips initialization
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

// Global error handling for JavaScript errors
window.addEventListener('error', (event) => {
  console.error('JavaScript Error:', event.error);
  
  // Only show error page for serious errors
  if (event.error && event.error.message && 
      !event.error.message.includes('Script error') && 
      !event.filename.includes('extension')) {
    handleAjaxError(event.error);
  }
});
