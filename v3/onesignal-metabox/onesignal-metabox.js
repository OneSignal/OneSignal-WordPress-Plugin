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

  updateUI();

  sendPost.addEventListener("change", updateUI);
  customisePost.addEventListener("change", updateUI);

  // Make sure WordPress editor and API are available
  if (typeof wp === "undefined" || !wp.data || !wp.data.select) {
    console.warn("wp.data is not available.");
    return;
  }

  let wasSaving = false;

  /*
  * Uncheck the "Send notification when post is published or updated" input whenever the post is published
  * This is to prevent users from accidentally sending notifications on subsequent updates
  * Instead, the user needs to opt-in again to send a notification when a post is updated
  */
  wp.data.subscribe(() => {
    const isSaving = wp.data.select('core/editor').isSavingPost();
    const postStatus = wp.data.select('core/editor').getCurrentPost().status;

    // Check if the post has finished saving successfully and is published
    if (wasSaving && !isSaving && postStatus === 'publish') {
      if (sendPost && sendPost.checked) {
        sendPost.checked = false;
        updateUI();
      }
    }

    // reset state for the next check
    wasSaving = isSaving;
  });
});
