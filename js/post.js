// =====================
// Modal Handling
// =====================

// Open comment modal when comment button is clicked
document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const postid = this.getAttribute('data-postid');
        const modal = document.getElementById('commentModal-' + postid);
        modal.style.display = 'flex';
    });
});

// Close modal when close button is clicked
document.querySelectorAll('.close').forEach(closeBtn => {
    closeBtn.onclick = function() {
        this.closest('.modal').style.display = 'none';
    };
});

// Close modal when clicking outside the modal content
window.onclick = function(event) {
    document.querySelectorAll('.modal').forEach(function(modal) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};

// =====================
// AJAX Comment Handling for a Single Post
// =====================
(function() {
    // Assumes window.currentPostID is set in the template
    const postID = window.currentPostID;
    const commentsList = document.querySelector('#comments-section-' + postID + ' .comments-list');
    const commentForm = document.querySelector('.comment-form[data-postid="' + postID + '"]');
    const commentInput = commentForm.querySelector('input[name="comment_text"]');

    // Render comments in the DOM
    function renderComments(comments) {
        commentsList.innerHTML = '';
        if (comments.length === 0) {
            commentsList.innerHTML = "<div style='color:#b8c1ec; text-align:center;'>No comments yet.</div>";
            return;
        }
        comments.forEach(comment => {
            const div = document.createElement('div');
            div.className = 'comment';
            div.innerHTML = `<b>${comment.name}:</b> ${comment.comment}`;
            commentsList.appendChild(div);
        });
    }

    // Fetch comments from the server
    function fetchComments() {
        fetch('fetchComments.php?postID=' + postID)
            .then(res => res.json())
            .then(data => renderComments(data));
    }

    // Handle comment form submission via AJAX
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(commentForm);
        fetch('comment.php', {
            method: 'POST',
            body: formData
        }).then(res => {
            if (res.ok) {
                commentInput.value = '';
                fetchComments();
            }
        });
    });

    // Initial fetch and polling for new comments every 2 seconds
    fetchComments();
    setInterval(fetchComments, 2000);
})();