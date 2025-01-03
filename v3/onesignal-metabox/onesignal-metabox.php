<?php

defined('ABSPATH') or die('This page may not be accessed directly.');

// Create a meta box
add_action('add_meta_boxes', 'onesignal_add_metabox');

function onesignal_add_metabox()
{
  add_meta_box(
    'onesignal_metabox', // metabox ID
    'OneSignal Push Notifications', // title
    'onesignal_metabox', // callback function
  );
}

// Render the meta box
function onesignal_metabox($post)
{
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
       $os_update_checked = isset($os_meta['os_update'])
           ? $os_meta['os_update'] == 'on'
           : (get_option('OneSignalWPSetting')['notification_on_post'] ?? 0) == 1;

       echo $os_update_checked ? 'checked' : '';
       ?>>
Send notification when post is published or updated
</label>
  <div id="os_options">
    <label for="os_segment">Send to segment</label>
    <select name="os_segment" id="os_segment">
    <option value="All">All</option>
    <?php
    if ($json && is_array($json->segments)) {
        foreach ($json->segments as $segment) {
            if (isset($segment->name)) {
                $selected = isset($post->os_meta['os_segment']) && $post->os_meta['os_segment'] === $segment->name ? 'selected' : '';
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
      <input type="checkbox" name="os_customise" id="os_customise" <?php echo isset($post->os_meta['os_customise']) && $post->os_meta['os_customise'] == 'on' ? 'checked' : '' ?>>Customize notification content</label>
    <div id="os_customisations" style="<?php echo isset($post->os_meta['os_customise']) && $post->os_meta['os_customise'] == 'on' ? 'display:block;' : 'display:none;'; ?>">
      <label for="os_title">Notification title</label>
      <input type="text" name="os_title" id="os_title" value="<?php echo esc_attr(isset($post->os_meta['os_title']) ? $post->os_meta['os_title'] : ''); ?>" disabled>
      <label for="os_content">Notification content</label>
      <input type="text" name="os_content" id="os_content" value="<?php echo esc_attr(isset($post->os_meta['os_content']) ? $post->os_meta['os_content'] : ''); ?>" disabled>
    </div>
    <?php if (get_option('OneSignalWPSetting')['send_to_mobile_platforms'] == 1) : ?>
      <hr>
      <label for="os_mobile_url">Mobile URL</label>
      <input type="text" name="os_mobile_url" id="os_mobile_url" value="<?php echo esc_attr(isset($post->os_meta['os_mobile_url']) ? $post->os_meta['os_mobile_url'] : ''); ?>">
    <?php endif; ?>
  </div>

<?php
}

// Load metabox JS
add_action('admin_print_styles-post.php', 'onesignal_meta_files');
add_action('admin_print_styles-post-new.php', 'onesignal_meta_files');

function onesignal_meta_files()
{
  wp_enqueue_script('onesignal_metabox_js', plugins_url('onesignal-metabox.js', __FILE__));
  wp_enqueue_style('onesignal_metabox_css', plugins_url('onesignal-metabox.css', __FILE__), array(), time());
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
