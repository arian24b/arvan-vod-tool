<?php
/**
 * Video Library Page Template
 *
 * @package WP_Arvan
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="arvan-video-library-container">
        <!-- Video library content will go here -->
        <div class="arvan-video-library-list">
            <?php
            // Add your video listing code here
            ?>
        </div>
    </div>
</div>
