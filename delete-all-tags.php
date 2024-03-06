<?php
/*
* Plugin Name: Delete All Tags
* Description: Deletes all tags from your WordPress site.
* Author: Zeljko Ascic
* Author URL: https://www.ascic.net/
* Version: 1.0
*/

// Add admin menu item
add_action('admin_menu', 'delete_all_tags_menu');

function delete_all_tags_menu() {
    add_menu_page(
        'Delete All Tags', // Page title
        'Delete All Tags', // Menu title
        'manage_options', // Capability required to access the page
        'delete-all-tags', // Menu slug
        'delete_all_tags_page', // Callback function to render the page
        'dashicons-trash' // Icon
    );
}

// Callback function to render the admin page
function delete_all_tags_page() {
    ?>
    <div class="wrap">
        <h1>Delete All Tags</h1>
        <p>Warning: This action is permanent and cannot be undone.</p>
        <p>Number of tags that will be deleted: <?php echo get_tag_count(); ?></p>
        <button id="delete-tags-btn" class="button button-primary">Delete Tags</button>
        <div id="progress-bar" style="margin-top: 20px; display: none;">
            <progress value="0" max="100"></progress>
            <span id="progress-label">0%</span>
        </div>
    </div>

    <script>
        // JavaScript to handle the button click
        document.getElementById('delete-tags-btn').addEventListener('click', function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?php echo admin_url("admin-ajax.php?action=delete_all_tags"); ?>', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Tags deleted successfully!');
                } else {
                    alert('Error: ' + xhr.statusText);
                }
            };
            xhr.onerror = function() {
                alert('Request failed.');
            };
            xhr.send();
        });
    </script>
    <?php
}

// AJAX handler to delete all tags
add_action('wp_ajax_delete_all_tags', 'delete_all_tags_ajax');

function delete_all_tags_ajax() {
    global $wpdb;
    
    // Delete tags
    $wpdb->query("DELETE FROM {$wpdb->terms} WHERE term_id IN (SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag')");
    $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag'");
    $wpdb->query("DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy})");

    echo 'Tags deleted successfully!';
    wp_die();
}

// Function to get the number of tags
function get_tag_count() {
    global $wpdb;
    return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'post_tag'");
}
