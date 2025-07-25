<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Create a meta box
add_action('add_meta_boxes', 'onesignal_add_metabox');

function onesignal_add_metabox()
{
  $current_post_type = get_post_type();

  if (onesignal_is_post_type_allowed($current_post_type)) {
    add_meta_box(
      'onesignal_metabox', // metabox ID
      'OneSignal Push Notifications', // title
      'onesignal_metabox', // callback function
    );
  }
}

// Render the meta box
function onesignal_metabox($post)
{
  $os_meta = get_post_meta($post->ID, 'os_meta', true);
  if (!is_array($os_meta)) {
    $os_meta = array();
  }

  // View segments API to populate Segments list.
  $args = array(
    'headers' => array(
      'Authorization' => 'Bearer ' . get_option('OneSignalWPSetting')['app_rest_api_key'],
      'accept' => 'application/json',
      'content-type' => 'application/json',
    )
  );

  // Make the API request, log errors, get segment names.
  $response = wp_remote_get('https://onesignal.com/api/v1/apps/' . get_option('OneSignalWPSetting')['app_id'] . '/segments', $args);

  if (is_wp_error($response)) {
      error_log('API request failed: ' . $response->get_error_message());
      $json = null; // Handle error case
  } else {
      $body = wp_remote_retrieve_body($response);
      $json = json_decode($body);

      // Check if segments exist and are an array
      if (!isset($json->segments) || !is_array($json->segments)) {
          error_log('Unexpected API response: Missing or invalid key');
          $json = null;
      }
  }

  // Meta box content -> js file hides sections depending on whats checked.
?>
  <label for="os_update">
  <input type="checkbox" name="os_update" id="os_update"
       <?php
       // Determine if this is a new post (never published)
       $is_new_post = ($post->post_status !== 'publish' || empty($post->post_date_gmt));
       $post_type = $post->post_type;
       if ($is_new_post) {
           if ($post_type === 'page') {
               $os_update_checked = (get_option('OneSignalWPSetting')['notification_on_page'] ?? 0) == 1;
           } else {
               $os_update_checked = (get_option('OneSignalWPSetting')['notification_on_post'] ?? 0) == 1;
           }
       } else {
           // Already published: always unchecked
           $os_update_checked = false;
       }
       echo $os_update_checked ? 'checked' : '';
       ?>>
Send notification when <?php echo $post_type === 'page' ? 'page' : 'post'; ?> is published
</label>
  <div id="os_options">
    <label for="os_segment">Send to segment</label>
    <select name="os_segment" id="os_segment">
    <option value="All">All</option>
    <?php
    if ($json && is_array($json->segments)) {
        foreach ($json->segments as $segment) {
            if (isset($segment->name)) {
                $selected = isset($os_meta['os_segment']) && $os_meta['os_segment'] === $segment->name ? 'selected' : '';
                echo '<option value="' . esc_attr($segment->name) . '"' . $selected . '>' . esc_html($segment->name) . '</option>';
            }
        }
    } else {
        echo '<option disabled>No segments available</option>';
    }
    ?>
</select>
    <hr>
    <label for="os_customise">
      <input type="checkbox" name="os_customise" id="os_customise" <?php echo isset($os_meta['os_customise']) && $os_meta['os_customise'] === 'on' ? 'checked' : '' ?>>Customize notification content</label>
    <div id="os_customisations" style="<?php echo isset($os_meta['os_customise']) && $os_meta['os_customise'] === 'on' ? 'display:block;' : 'display:none;'; ?>">
      <label for="os_title">Notification title</label>
      <input type="text" name="os_title" id="os_title" value="<?php echo esc_attr(isset($os_meta['os_title']) ? $os_meta['os_title'] : ''); ?>" disabled>
      <label for="os_content">Notification content</label>
      <input type="text" name="os_content" id="os_content" value="<?php echo esc_attr(isset($os_meta['os_content']) ? $os_meta['os_content'] : ''); ?>" disabled>
    </div>
    <?php if (get_option('OneSignalWPSetting')['send_to_mobile_platforms'] == 1) : ?>
      <hr>
      <label for="os_mobile_url">Mobile URL</label>
      <input type="text" name="os_mobile_url" id="os_mobile_url" value="<?php echo esc_attr(isset($os_meta['os_mobile_url']) ? $os_meta['os_mobile_url'] : ''); ?>">
    <?php endif; ?>
  </div>

<?php
}

// Load metabox JS
add_action('admin_print_styles-post.php', 'onesignal_meta_files');
add_action('admin_print_styles-post-new.php', 'onesignal_meta_files');

function onesignal_meta_files()
{
  $cache_buster = ceil(time() / 3600); // updates every hour
  wp_enqueue_script(
    'onesignal_metabox_js',
    plugins_url('onesignal-metabox.js', __FILE__),
    array(),
    $cache_buster,
    true // load in the footer for performance
  );
  wp_enqueue_style(
    'onesignal_metabox_css',
    plugins_url('onesignal-metabox.css', __FILE__),
    array(),
    $cache_buster
  );
}

// Store meta data
add_action('save_post', 'onesignal_save_meta', 10);

function onesignal_save_meta($post_id)
{
  $fields = [
    'os_update',
    'os_segment',
    'os_customise',
    'os_title',
    'os_content',
    'os_mobile_url'
  ];

  $meta_values = array();

  foreach ($fields as $field) {
    if (array_key_exists($field, $_POST)) {
      $meta_values[$field] = sanitize_text_field($_POST[$field]);
    } else {
      unset($meta_values[$field]);
    }
  }

  // Update the post meta with the os_meta key and values
  update_post_meta($post_id, 'os_meta', $meta_values);
}
