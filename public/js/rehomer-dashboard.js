// Rehomer Dashboard JavaScript

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    
    sidebar.classList.toggle('collapsed');
    body.classList.toggle('sidebar-collapsed');
    
    // Update toggle button icon
    const toggleBtn = document.querySelector('.sidebar-toggle-btn i');
    if (sidebar.classList.contains('collapsed')) {
        toggleBtn.className = 'fas fa-bars';
    } else {
        toggleBtn.className = 'fas fa-times';
    }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    // Make sure toggle button is visible
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    if (toggleBtn) {
        console.log('Toggle button found and initialized');
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.menu-toggle');
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle?.contains(event.target) && event.target !== toggleBtn) {
            sidebar.classList.remove('active');
        }
    }
});

// Mobile menu toggle
const mobileToggle = document.querySelector('.menu-toggle');
if (mobileToggle) {
    mobileToggle.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
}

// Handle request actions (approve/reject)
function handleRequest(adoptionId, action) {
    if (confirm(`Are you sure you want to ${action} this adoption request?`)) {
        // You can implement AJAX call here
        window.location.href = `handle-adoption.php?id=${adoptionId}&action=${action}`;
    }
}
