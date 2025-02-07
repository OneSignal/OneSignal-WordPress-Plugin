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

  let checkingNotification = false;

  // Subscribe to post status changes
  wp.data.subscribe(() => {
    const currentStatus = editorStore.getCurrentPostAttribute("status");
    const isAutosaving = editorStore.isAutosavingPost();
    const isSaving = editorStore.isSavingPost();

    // Only proceed if we're transitioning from "not saving" to "saving"
    // This prevents multiple triggers during the same save operation
    if (!isAutosaving && isSaving && currentStatus === "publish") {
      // Check if we should poll for notification status
      if (sendPost.checked && !checkingNotification) {
        checkingNotification = true;
        pollNotificationStatus();
      }
    }
  });

  /**
   * Checks if a OneSignal notification has been sent for the current post
   *
   * @returns {Promise<boolean>} True if notification was sent successfully, false otherwise
   */
  async function checkNotificationStatus() {
    const postId = editorStore.getCurrentPostId();

    const formData = new FormData();
    formData.append('action', 'check_onesignal_notification'); // 'action' tells WordPress which AJAX handler to use (wp_ajax_check_onesignal_notification)
    formData.append('post_id', postId); // Pass the post ID to check notification status for this specific post
    formData.append('_ajax_nonce', ajax_object.nonce); // Add security nonce to prevent CSRF attacks

    try {
      const response = await fetch(ajax_object.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      });

      const data = await response.json();
      // Return the success status (true/false)
      return data.success;
    } catch (error) {
      console.error('OneSignal status check error:', error);
      // If anything fails, assume the notification wasn't sent
      return false;
    }
  }

  /**
   * Reset the notification status
   *
   * @returns {Promise<void>}
   */ 
  async function resetNotificationStatus() {
    const resetFormData = new FormData();
    resetFormData.append('action', 'reset_onesignal_status'); // 'action' tells WordPress which AJAX handler to use (wp_ajax_reset_onesignal_status)
    resetFormData.append('post_id', editorStore.getCurrentPostId()); // Pass the post ID to reset notification status for this specific post
    resetFormData.append('_ajax_nonce', ajax_object.nonce); // Add security nonce to prevent CSRF attacks

    try {
      const resetResponse = await fetch(ajax_object.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: resetFormData
      });
      const resetData = await resetResponse.json();
    } catch (error) {
      console.error('OneSignal status reset error:', error);
    }
  }

  /**
   * Polls the notification status until it is confirmed to be sent
   * Uses checkNotificationStatus to verify the status
   * Uses resetNotificationStatus to reset the status after the notification is sent
   *
   * @returns {Promise<void>}
   */
  async function pollNotificationStatus() {
    let attempts = 0;
    const maxAttempts = 10;

    while (attempts < maxAttempts) {
      const sent = await checkNotificationStatus();

      if (sent) {
        await resetNotificationStatus();

        sendPost.checked = false;
        updateUI();
        break;
      }

      attempts++;
      await new Promise(resolve => setTimeout(resolve, 1500));
    }

    checkingNotification = false;
  }
});
