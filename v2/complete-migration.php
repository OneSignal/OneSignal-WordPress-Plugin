<?php

function onesignal_complete_migration()
{
    if (isset($_POST['plugin_action']) && $_POST['plugin_action'] === 'complete_migration') {

        // Security check
        if (!check_admin_referer('onesignal_migration_nonce')) {
            wp_die(__('Security check failed', 'onesignal-push'));
        }

        // Mark the plugin as migrated
        update_option('onesignal_plugin_migrated', true);

        // Provide feedback to the user
        wp_redirect(admin_url('admin.php?page=onesignal-admin-page.php'));
        exit;
    }
}

add_action('admin_init', 'onesignal_complete_migration');
