jQuery(document).ready(notice);

var state = {
    post_id : ajax_object.post_id,  // post id sent from php backend
    first_modified : undefined,     // when the post was first modified
    started : false,                // post notification requests started
    interval: undefined,            // global interval for reattempting requests
    interval_count : 0,             // how many times has the request been attempted
    status : undefined              // whether the post is scheduled or published
  }
    
function notice() {
  if (!isWpCoreEditorDefined()) {
    return;
  }

  const editor = wp.data.select("core/editor");
  const get_wp_attr = attr => {
    return editor.getEditedPostAttribute(attr);
  };

  /*
   * Subscribes function to WP's state-change listener
   *  - checks change in post modified date
   *  - triggers interval that checks if recipient meta data available in backend
   */
  wp.data.subscribe(() => {
    // runs with each change in wp state
    const post = editor.getCurrentPost();

    // runs until post data loads
    if(!post || post === {}){
      return;
    }

    // post is defined now 
    if (!state.first_modified) {
      // captures last modified date of loaded post
      state.first_modified = post.modified;	
    }

    // latest modified date, status of the post
    const { modified, status } = post;
    state.status = status;

    // is checked
    const send_os_notif = jQuery("[name=send_onesignal_notification]").attr(
      "checked"
    );

    // if last modified differs from first modified times, post_modified = true
    const post_modified = modified !== state.first_modified;

    const is_published = status === "publish";

    // if hasn't started, change detected, box checked, and the status is 'publish'
    if (!state.started && post_modified && send_os_notif && is_published ) {
      state.interval = setInterval(get_metadata, 3000); // starts requests
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
      const { recipients, status_code, response_body } = response;

      if(window.DEBUG_MODE){
        console.log(response);
      }

      const is_status_empty = status_code.length == 0;
      const is_recipients_empty = recipients.length == 0;

      if(!is_status_empty && !is_recipients_empty){
        // status 0: HTTP request failed
        if (status_code === "0") {
          error_notice("OneSignal Push: request failed with status code 0. "+response_body);
          reset_state();
          return;
        }

        // 400 & 500 level errors
        if (status_code >= 400) {
          if (!response_body) {
            error_notice(
              "OneSignal Push: there was a " +
                status_code +
                " error sending your notification"
            );
          } else {
            error_notice("OneSignal Push: there was a " + status_code + " error sending your notification: " + response_body);
          }

          reset_state();
          return;
        }

        if (recipients === "0") {
          error_notice(
            "OneSignal Push: there were no recipients."
          );
          reset_state();

        } else if (recipients) {
          show_notice(recipients);
          reset_state();
        }
      }
    });

    // try for 1 minute (each interval = 3s)
    if (state.interval_count > 20) {
      error_notice(
        "OneSignal Push: Did not receive a response status from last notification sent"
      );
      reset_state();
    }
    
    state.interval_count += 1;
  };

  /*
   * Gets recipient count and shows notice
   */
  const show_notice = recipients => {
    const plural = recipients == 1 ? "" : "s";
    var delivery_link_text = "";

    if (state.status === "publish") {
      var notice_text = "OneSignal Push: Successfully sent a notification to ";
      delivery_link_text = ". Go to your app's \"Delivery\" tab to check sent messages: https://app.onesignal.com/apps";
    } else if (state.status === "future"){
      var notice_text = "OneSignal Push: Successfully scheduled a notification for ";
    }

    wp.data
      .dispatch("core/notices")
      .createNotice(
        "info",
        notice_text + recipients + " recipient" + plural + delivery_link_text,
        {
            id:'onesignal-notice',
            isDismissible: true
        }
      );
  };

  const error_notice = error => {
    wp.data.dispatch("core/notices").createNotice("error", error, {
        isDismissible: true,
        id:'onesignal-error'
    });
  };

  const reset_state = () => {
    clearInterval(state.interval);
    state.interval = undefined;
    state.interval_count = 0;
    state.started = false;
    state.first_modified = undefined;
  }
};

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

/**
 * - use the debug method in the console to show data about the request
 * - works in Gutenberg editor
 *
 * returns an object in the format
 *  { status : "200",
 *    recipients : "1374",
 *    response_body : []
 *  }
 */
window.OneSignal = {
    debug : () => {
        window.DEBUG_MODE = window.DEBUG_MODE ? !window.DEBUG_MODE : true;
        notice();
    }
};
