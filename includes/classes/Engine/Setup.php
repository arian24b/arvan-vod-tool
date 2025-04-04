<?php
namespace WP_Arvan\Engine;

// ...existing code...

class Setup {
    // ...existing code...

    /**
     * Render video library page in admin
     *
     * @return void
     */
    public function video_library_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Render the video library page template
        require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'admin/partials/video-library.php';
    }

    // ...existing code...
}
