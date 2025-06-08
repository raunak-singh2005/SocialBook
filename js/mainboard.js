// =======================
// Dropdown Functionality
// =======================

/**
 * Toggle the "More" dropdown for user actions.
 * Hides any other open dropdowns before toggling the selected one.
 * @param {HTMLElement} btn - The button that triggers the dropdown.
 */
function toggleDropdown(btn) {
    // Hide all other open dropdowns
    document.querySelectorAll('.user-card-actions-dropdown').forEach(function(drop) {
        if (drop !== btn.parentElement.nextElementSibling) drop.style.display = 'none';
    });
    // Toggle the selected dropdown
    var dropdown = btn.parentElement.nextElementSibling;
    dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}

/**
 * Hide dropdowns when clicking outside of a "More" button.
 */
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('more-btn')) {
        document.querySelectorAll('.user-card-actions-dropdown').forEach(function(drop) {
            drop.style.display = 'none';
        });
    }
});

// =======================
// AJAX Follow/Unfollow
// =======================

/**
 * Handles AJAX follow/unfollow button logic.
 * Updates button state and text based on server response.
 */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-card form[action="follow.php"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('.follow-btn');
            btn.disabled = true;
            var formData = new FormData(form);

            fetch('follow.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                if (data && data.success) {
                    if (data.following) {
                        btn.textContent = 'Unfollow';
                        btn.classList.add('remove-btn');
                    } else {
                        btn.textContent = 'Follow';
                        btn.classList.remove('remove-btn');
                    }
                }
            })
            .catch(function() {
                btn.disabled = false;
            });
        });
    });
});

// =======================
// Dynamic User Search
// =======================

/**
 * Filters user cards in real-time as the user types in the search input.
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const userList = document.getElementById('userList');
    searchInput.addEventListener('input', function() {
        const filter = searchInput.value.trim().toLowerCase();
        const userCards = userList.querySelectorAll('.user-card-container');
        userCards.forEach(function(card) {
            const name = card.getAttribute('data-name');
            if (name.includes(filter)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});