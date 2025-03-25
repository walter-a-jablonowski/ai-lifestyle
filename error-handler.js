/**
 * AI Lifestyle Error Handler
 * 
 * Provides comprehensive error handling for JavaScript and AJAX errors
 */

class ErrorHandler {
  /**
   * Initialize the error handler
   */
  static init() {
    // Set up global error handling
    window.addEventListener('error', this.handleGlobalError.bind(this));
    window.addEventListener('unhandledrejection', this.handlePromiseError.bind(this));
    
    // Set up AJAX error handling
    this.setupAjaxErrorHandling();
    
    console.log('Error handler initialized');
  }
  
  /**
   * Handle global JavaScript errors
   */
  static handleGlobalError(event) {
    const { message, filename, lineno, colno, error } = event;
    
    console.error('JavaScript Error:', {
      message,
      filename,
      lineno,
      colno,
      stack: error ? error.stack : null
    });
    
    // Show user-friendly error message
    this.showErrorToast('An error occurred while processing your request. Please try again.');
    
    // Prevent default browser error handling
    event.preventDefault();
  }
  
  /**
   * Handle unhandled Promise rejections
   */
  static handlePromiseError(event) {
    console.error('Unhandled Promise Rejection:', event.reason);
    
    // Show user-friendly error message
    this.showErrorToast('An error occurred while processing your request. Please try again.');
    
    // Prevent default browser error handling
    event.preventDefault();
  }
  
  /**
   * Set up AJAX error handling
   */
  static setupAjaxErrorHandling() {
    // Override the fetch API to handle errors
    const originalFetch = window.fetch;
    
    window.fetch = async function(...args) {
      try {
        const response = await originalFetch.apply(this, args);
        
        if (!response.ok) {
          // Handle HTTP error status
          const errorData = await response.json().catch(() => {
            return { message: `HTTP error ${response.status}: ${response.statusText}` };
          });
          
          console.error('AJAX Error:', {
            status: response.status,
            statusText: response.statusText,
            url: response.url,
            data: errorData
          });
          
          ErrorHandler.showErrorToast(errorData.message || `Error ${response.status}: ${response.statusText}`);
        }
        
        return response;
      } catch (error) {
        // Handle network errors
        console.error('Network Error:', error);
        ErrorHandler.showErrorToast('Network error. Please check your connection and try again.');
        throw error;
      }
    };
  }
  
  /**
   * Show an error toast to the user
   */
  static showErrorToast(message) {
    // Check if toast container exists, create if no
    let toastContainer = document.getElementById('error-toast-container');
    
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'error-toast-container';
      toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'error-toast-' + Date.now();
    const toastHtml = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <strong class="me-auto">Error</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
      toastElement.remove();
    });
  }
  
  /**
   * Log an error to the server for tracking
   */
  static logErrorToServer(errorData) {
    // This could be implemented to send errors to a server-side logging endpoint
    fetch('/ajax.php?action=error&handler=log', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(errorData)
    }).catch(err => {
      // Silently fail if error logging fails
      console.error('Failed to log error to server:', err);
    });
  }
}

// Initialize error handler when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  ErrorHandler.init();
});
