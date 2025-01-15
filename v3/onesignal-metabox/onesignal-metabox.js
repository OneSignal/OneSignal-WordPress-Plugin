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

  setDisplay(optionsWrap, sendPost.checked);
  setDisplay(customiseWrap, customisePost.checked);
  setDisabled(customiseWrapChild, !customisePost.checked);

  sendPost.addEventListener("change", () => {
    setDisplay(optionsWrap, sendPost.checked);
  });

  customisePost.addEventListener("change", () => {
    setDisplay(customiseWrap, customisePost.checked);
    setDisabled(customiseWrapChild, !customisePost.checked);
  });

  // make sure WordPress editor and API are available
  if (typeof wp === 'undefined' || !wp.data || !wp.data.select) {
    console.warn('wp.data is not available.');
    return;
  }

  const editorStore = wp.data.select('core/editor');

  // track initial state of checkbox
  const osUpdateCheckbox = document.querySelector('#os_update');
  const wasCheckedInitially = osUpdateCheckbox ? osUpdateCheckbox.checked : false;

  // track previous post status to detect changes
  let previousStatus = editorStore.getCurrentPostAttribute('status');

  // subscribe to state changes
  wp.data.subscribe(() => {
    const currentStatus = editorStore.getCurrentPostAttribute('status');

    // check if the post status changed to "publish"
    if (previousStatus !== currentStatus && currentStatus === 'publish') {
        previousStatus = currentStatus;

        if (wasCheckedInitially) {
            // uncheck the os_update checkbox
            if (osUpdateCheckbox && osUpdateCheckbox.checked) {
                osUpdateCheckbox.checked = false;
            }
        }
    } else {
        previousStatus = currentStatus;
    }
  });
});
