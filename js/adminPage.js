// Handles tab switching logic for adminPage.php
function showTab(tab) {
    // Remove 'active' class from all sidebar tabs and tab sections
    document.querySelectorAll('.sidebar-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-section').forEach(sec => sec.classList.remove('active'));

    // Add 'active' class to the selected tab and its corresponding section
    if (tab === 'newAdmin') {
        document.querySelector('.sidebar-tab:nth-child(2)').classList.add('active');
        document.getElementById('tab-newAdmin').classList.add('active');
    } else if (tab === 'users') {
        document.querySelector('.sidebar-tab:nth-child(3)').classList.add('active');
        document.getElementById('tab-users').classList.add('active');
    } else if (tab === 'reports') {
        document.querySelector('.sidebar-tab:nth-child(4)').classList.add('active');
        document.getElementById('tab-reports').classList.add('active');
    } else if (tab === 'banlog') {
        document.querySelector('.sidebar-tab:nth-child(5)').classList.add('active');
        document.getElementById('tab-banlog').classList.add('active');
    } else if (tab === 'postlog') {
        document.querySelector('.sidebar-tab:nth-child(6)').classList.add('active');
        document.getElementById('tab-postlog').classList.add('active');
    }
}

// Auto-select Post Log tab if requested via GET parameter
if (typeof window !== "undefined") {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'postlog') {
        showTab('postlog');
    }
}