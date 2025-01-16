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

  wp.data.subscribe(() => {
    const currentStatus = editorStore.getCurrentPostAttribute("status");

    // Check if the post status changed to "publish"
    if (previousStatus !== currentStatus && currentStatus === "publish") {
      previousStatus = currentStatus;

      // Uncheck the checkbox and update the UI
      if (sendPost.checked) {
        sendPost.checked = false;
        updateUI(); // Ensure the UI reflects the checkbox state
      }
    } else {
      previousStatus = currentStatus;
    }
  });
});
