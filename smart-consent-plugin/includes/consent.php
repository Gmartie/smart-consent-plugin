<?php

add_action('wp_footer', function() {
    include plugin_dir_path(__FILE__) . '../templates/banner.php';
});
