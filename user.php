<div id="friends">

    <?php
        // Set default profile image based on gender
        if ($friendRow['gender'] == "Male") {
            $image = "images/user_male.jpg";
        } else {
            $image = "images/user_female.jpg";
        }
    ?>

    <!-- Friend profile link and display -->
    <a href="profile.php?id=<?php echo $friendRow['userid']; ?>">
        <img src="<?php echo $image; ?>" id="friendIMG">
        <br>
        <?php 
            // Display friend's full name
            echo $friendRow['firstName'] . " " . $friendRow['lastName'] . " "; 
        ?>
    </a>

</div>