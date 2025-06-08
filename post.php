<!-- Post Container -->
<div id="post">

    <!-- User Profile Image -->
    <div>
        <?php
            // Determine which image to use for the user: profile image or gender-based fallback
            $image = "";
            if (isset($rowUser['profileImage']) && file_exists($rowUser['profileImage'])) {
                $image = $rowUser['profileImage'];
            } else {
                $image = ($rowUser['gender'] == "Male") ? "images/user_male.jpg" : "images/user_female.jpg";
            }
        ?>
        <img 
            src="<?php echo htmlspecialchars($image); ?>" 
            style="width: 75px; height: 75px; object-fit: cover; border-radius: 50%; margin-right:4px;"
        >
    </div>

    <!-- Post Content -->
    <div>
        <!-- User Name and Post Date -->
        <div style="font-weight: bold; color: #405d9b; display: flex; align-items: center; gap: 10px;">
            <?php echo htmlspecialchars($rowUser['firstName'] . " " . $rowUser['lastName']); ?>
            <span style="color:#b8c1ec; font-size:0.92em; font-weight: normal;">
                <?php echo htmlspecialchars($row['date']); ?>
            </span>
        </div>

        <!-- Post Text -->
        <?php echo htmlspecialchars($row['post']); ?>
        <br><br>

        <!-- Post Image (if any) -->
        <?php
            $Image = new Image();
            if (!empty($row['image'])) {
                $postImage = $Image->getThumbPost($row['image']);
                if (file_exists($postImage)) {
                    echo "<img src='" . htmlspecialchars($postImage) . "' style='width:100%' />";
                }
            }
        ?>

        <!-- Post Location Map (if any) -->
        <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
            <div class="post-map" style="width:100%;height:200px;margin:10px 0;">
                <iframe
                    width="100%"
                    height="200"
                    frameborder="0"
                    style="border:0"
                    src="https://www.google.com/maps?q=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>&hl=es;z=14&output=embed"
                    allowfullscreen>
                </iframe>
            </div>
        <?php endif; ?>

        <br/><br/>

        <!-- Post Actions: Like and Comment -->
        <div class="post-actions">
            <!-- Like Button -->
            <a href="like.php?type=post&id=<?php echo $row['postID']; ?>" class="action-btn" style="text-decoration: none;">
                <span class="icon">&#x1F44D;</span>
                <?php if ($row['likes'] > 0): ?>
                    <span class="like-count"> (<?php echo $row['likes']; ?>)</span>
                <?php endif; ?>
            </a>
            <!-- Comment Form -->
            <form method="POST" action="comment.php" class="comment-form" data-postid="<?php echo $row['postID']; ?>">
                <input type="hidden" name="postID" value="<?php echo $row['postID']; ?>">
                <input type="text" name="comment_text" placeholder="Write a comment..." required>
                <button type="submit" class="action-btn">💬</button>
            </form>
        </div>
    </div>
</div>

<!-- Comments Section -->
<div class="comments-section" id="comments-section-<?php echo $row['postID']; ?>">
    <div class="comments-list"></div>
</div>

<?php
// Handle comment submission (fallback for non-AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_postid'], $_POST['comment_text'])) {
    $commentText = trim($_POST['comment_text']);
    $commentPostID = intval($_POST['comment_postid']);
    $commentUserID = $_SESSION['SocialBook_userID'];
    $postOwnerID = $row['userID'];
    if ($commentText !== "") {
        $DB->write("INSERT INTO Comments (userID, postOwnerID, postID, comment, date) VALUES ('$commentUserID', '$postOwnerID', '$commentPostID', '" . addslashes($commentText) . "', NOW())");
        // Refresh to show the new comment
        echo "<script>location.reload();</script>";
        exit;
    }
}
?>

<!-- Styles for Post and Comments -->
<style>
    .post-actions {
        display: flex;
        gap: 10px;
        margin-top: 8px;
        align-items: stretch;
    }
    .post-actions form {
        flex: 1;
        display: flex;
        align-items: stretch;
        gap: 6px;
        margin: 0;
    }
    .post-actions input[type="text"] {
        width: 100%;
        min-width: 220px;
        max-width: 100%;
        border-radius: 6px;
        border: 1px solid #b8c1ec;
        padding: 2px 6px;
        font-size: 0.95em;
        box-sizing: border-box;
        height: 100%;
    }
    .post-actions button.action-btn {
        padding: 2px 10px;
        font-size: 0.95em;
        height: 100%;
        display: flex;
        align-items: center;
    }
    .post-actions > .action-btn {
        height: 100%;
        display: flex;
        align-items: center;
    }
    .comments-section {
        margin-top: 12px;
        padding-left: 8px;
        max-width: 100%;
    }
    .comment {
        background: #232946;
        color: #eaeaea;
        border-radius: 8px;
        padding: 6px 10px;
        margin-bottom: 6px;
        font-size: 0.97em;
    }
    .modal {
        position: fixed;
        z-index: 9999;
        left: 0; 
        top: 0; 
        width: 100vw; 
        height: 100vh;
        background: rgba(0,0,0,0.4);
        display: none;
        align-items: center; 
        justify-content: center;
    }
    .modal.show {
        display: flex;
    }
    .modal-content {
        background: #232946;
        padding: 24px;
        border-radius: 12px;
        min-width: 320px;
        box-shadow: 0 2px 16px rgba(20,20,30,0.22);
        position: relative;
        margin: auto;
        max-width: 90vw;
    }
    .close {
        position: absolute; top: 8px; right: 14px; font-size: 1.5em; color: #eebbc3; cursor: pointer;
    }
    .post-actions .action-btn .like-count {
        color: #fff;
    }
</style>

<!-- Additional JS (if any) -->
<script>
window.currentPostID = <?php echo json_encode($row['postID']); ?>;
</script>
<script src="js/post.js"></script>
