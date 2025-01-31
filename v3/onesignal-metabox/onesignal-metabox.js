window.addEventListener("DOMContentLoaded", () => {
  const sendPost = document.getElementById("os_update");
  const optionsWrap = document.getElementById("os_options");
  const customisePost = document.getElementById("os_customise");
  const customiseWrap = document.getElementById("os_customisations");

  // Guard against missing elements
  if (!sendPost || !optionsWrap || !customisePost || !customiseWrap) {
    console.error("OneSignal: required elements are missing in the DOM.");
    return;
  }

  const customiseWrapChild = customiseWrap.querySelectorAll("input");

  function setDisplay(elem, checked) {
    elem.style.display = checked ? "inherit" : "none";
  }

  function setDisabled(children, disabled) {
    children.forEach((child) => (child.disabled = disabled));
  }

  function updateUI() {
    setDisplay(optionsWrap, sendPost.checked);
    setDisplay(customiseWrap, customisePost.checked);
    setDisabled(customiseWrapChild, !customisePost.checked);
  }

  // init UI state
  updateUI();

  sendPost.addEventListener("change", updateUI);
  customisePost.addEventListener("change", updateUI);

  // Make sure WordPress editor and API are available
  if (typeof wp === "undefined" || !wp.data || !wp.data.select) {
    console.warn("wp.data is not available.");
    return;
  }

  const editorStore = wp.data.select("core/editor");

  // Track previous post status to detect changes
  let previousStatus = editorStore.getCurrentPostAttribute("status");
  let checkingNotification = false;

  // Subscribe to post status changes
  wp.data.subscribe(() => {
    const currentStatus = editorStore.getCurrentPostAttribute("status");

    // Check if the post status changed to "publish"
    if (previousStatus !== currentStatus && currentStatus === "publish") {
      // Instead of unchecking immediately, let's wait for the save to complete
      if (sendPost.checked && !checkingNotification) {
        // Prevent the checkbox from being unchecked until save is complete
        checkingNotification = true;

        // Wait for the next tick to ensure form data is sent with the save
        setTimeout(() => {
          // Start checking for the notification status
          pollNotificationStatus();
        }, 0);
      }
    }

    previousStatus = currentStatus;
  });

  /**
   * Checks if a OneSignal notification has been sent for the current post
   * Uses WordPress admin-ajax.php endpoint for compatibility across all permalink structures
   *
   * @returns {Promise<boolean>} True if notification was sent successfully, false otherwise
   */
  async function checkNotificationStatus() {
    // Get the current post ID from WordPress editor store
    const postId = editorStore.getCurrentPostId();

    // Create form data for the AJAX request
    const formData = new FormData();
    formData.append('action', 'check_onesignal_notification'); // 'action' tells WordPress which AJAX handler to use (wp_ajax_check_onesignal_notification)
    formData.append('post_id', postId); // Pass the post ID to check notification status for this specific post
    formData.append('_ajax_nonce', ajax_object.nonce); // Add security nonce to prevent CSRF attacks

    try {
      // Use the AJAX URL localized in PHP via wp_localize_script
      const response = await fetch(ajax_object.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      });

      const data = await response.json();
      // Return the success status (true/false)
      return data.success;
    } catch (error) {
      // If anything fails, assume notification wasn't sent
      return false;
    }
  }

  /**
   * Polls the notification status until it is confirmed to be sent
   * Uses checkNotificationStatus to verify the status
   */
  async function pollNotificationStatus() {
    let attempts = 0;
    const maxAttempts = 10;

    while (attempts < maxAttempts) {
      const sent = await checkNotificationStatus();
      if (sent) {
        sendPost.checked = false;
        updateUI();
        break;
      }
      attempts++;

      // Wait for 1 second before checking again
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
    checkingNotification = false;
  }
});
