jQuery(document).ready(function() {
  if (!isWpCoreEditorDefined()) {
    return;
  }

  const editor = wp.data.select("core/editor");
  const get_wp_attr = attr => {
    return editor.getEditedPostAttribute(attr);
  };
 
  var state = {
    post_id : ajax_object.post_id,
    first_modified : undefined,
    started : false,
    interval: undefined,
    interval_count : 0
  }

  /*
   * Subscribes function to WP's state-change listener
   *  - checks change in post modified date
   *  - triggers interval that checks if recipient meta data available in backend
   */
  wp.data.subscribe(() => {
    // runs with each change in wp state
    const post = wp.data.select("core/editor").getCurrentPost();

    if(!post || post === {}){
      return;
    }

    // runs until post data loads
    if (!state.first_modified) {
      // captures last modified date of loaded post
      state.first_modified = post.modified;	
    }

    // latest modified date
    const { modified, status } = post;

    // is checked
    const send_os_notif = jQuery("[name=send_onesignal_notification]").attr(
      "checked"
    );

    const post_modified = modified !== state.first_modified;
    
    // if hasn't started and change is detected
    if (!state.started && post_modified && send_os_notif && (status === "publish")) {
      state.interval = setInterval(get_metadata, 3000);
      state.started = true;
    }
  });

  /*
   * Checks if post has meta for "recipients" on server
   *  - means request to OS has finished
   */
  const get_metadata = () => {
    const data = {
      action: "has_metadata",
      post_id: state.post_id
    };

    jQuery.get(ajax_object.ajax_url, data, function(response) {
      response = JSON.parse(response);
      const { recipients, status_code, error_message } = response;
  
      // status 0: HTTP request failed
      if (status_code == 0) {
        error_notice("OneSignal Push: HTTP request failed");
        reset_state();
	return;
      }

      // 400 & 500 level errors
      if (status_code >= 400) {
        if (!error_message) {
          error_notice(
            "OneSignal Push: there was a " +
              status_code +
              " error sending your notification"
          );
        } else {
          error_notice("OneSignal Push: " + error_message);
        }

        reset_state();
        return;
      }

      if (recipients == 0) {
        error_notice(
          "OneSignal Push: there were no recipients. You either 1) have no subscribers yet or 2) you hit the rate-limit. Please try again in an hour. Learn more: https://bit.ly/2UDplAS"
        );
        reset_state();

      } else if (recipients) {
        show_notice(recipients);
        reset_state();
      }

      // try for 1 minute
      if (state.interval_count > 20) {
        error_notice(
          "OneSignal Push: Did not receive a response status from last notification sent"
        );
        reset_state();
      }
    });
    state.interval_count += 1;
  };

  /*
   * Gets recipient count and shows notice
   */
  const show_notice = recipients => {
    const plural = recipients == 1 ? "" : "s";
    wp.data
      .dispatch("core/notices")
      .createNotice(
        "info",
        "OneSignal Push: Successfully sent a notification to " +
          recipients +
          " recipient" +
          plural,
        {
          isDismissible: true
        }
      );
  };

  const error_notice = error => {
    wp.data.dispatch("core/notices").createNotice("error", error, {
      isDismissible: true
    });
  };

  const reset_state = () => {
    clearInterval(state.interval);
    state.interval = undefined;
    state.interval_count = 0;
    state.started = false;
    state.first_modified = undefined;
  }

});
const isWpCoreEditorDefined = () => {
  var unloadable = ""; // variable name that couldn't be loaded
  if (!wp || !wp.data || !wp.data.select("core/editor")) {
    if (!wp) {
      unloadable = "wp";
    } else if (!wp.data) {
      unloadable = "wp.data";
    } else if (!wp.data.select("core/editor")) {
      unloadable = 'wp.data.select("core/editor")';
    }

    console.warn(
      `OneSignal Push: could not load ${unloadable}. https:\/\/bit.ly/2F4G0bt`
    );
    return false;
  } else {
    return true;
  }
};
