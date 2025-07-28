/**
 * Page View Counter JavaScript
 */

// Reset view count function
function pvcResetViews(postId) {
    if (!confirm('Are you sure you want to reset the view count for this post? This action cannot be undone.')) {
        return;
    }
    
    // Get nonce value
    const nonce = document.getElementById('pvc_reset_views_nonce').value;
    
    // Create form data
    const formData = new FormData();
    formData.append('action', 'pvc_reset_views');
    formData.append('post_id', postId);
    formData.append('nonce', nonce);
    
    // Send AJAX request
    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated count
            location.reload();
        } else {
            alert('Error resetting views: ' + (data.data || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error resetting views. Please try again.');
    });
}// 
Admin page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('cb-select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="post_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    }
});

// Bulk action validation
function pvcBulkAction() {
    const bulkActionSelector = document.getElementById('bulk-action-selector-top');
    if (!bulkActionSelector) return true;
    
    const bulkAction = bulkActionSelector.value;
    const checkedBoxes = document.querySelectorAll('input[name="post_ids[]"]:checked');
    
    if (!bulkAction) {
        alert('Please select a bulk action.');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one post.');
        return false;
    }
    
    if (bulkAction === 'reset') {
        return confirm('Are you sure you want to reset views for ' + checkedBoxes.length + ' selected post(s)? This action cannot be undone.');
    }
    
    return true;
}

// Reset single view function for admin page
function pvcResetSingleView(postId) {
    if (!confirm('Are you sure you want to reset the view count for this post?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'pvc_reset_single_view');
    formData.append('post_id', postId);
    formData.append('nonce', typeof pvcAdminData !== 'undefined' ? pvcAdminData.resetSingleNonce : '');

    const ajaxUrl = typeof pvcAdminData !== 'undefined' ? pvcAdminData.ajaxurl : ajaxurl;

    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.data || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error resetting views. Please try again.');
    });
}