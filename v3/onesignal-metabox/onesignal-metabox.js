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

  let wasSaving = false;

  /*
  * Uncheck the "Send notification when post is published or updated" input whenever the post is published
  * This is to prevent users from accidentally sending notifications on subsequent updates
  * Instead, the user needs to opt-in again to send a notification when a post is updated
  */
  if (wp?.data?.select) {
    // Gutenberg editor
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
  } else {
    // Classic editor
    jQuery(document).ready(function($) {
      /*
      * The classic editor doesn't have the same hooks as the Gutenberg editor
      * It also refreshes the page after saving, so we need to persist the "just published" state across page reloads
      */
      if (sessionStorage.getItem('onesignal_just_published')) {
        if (sendPost && sendPost.checked) {
          sendPost.checked = false;
          updateUI();
        }
        sessionStorage.removeItem('onesignal_just_published');
      }

      $('#publish').click(function() {
        if (sendPost && sendPost.checked) {
          // Set flag before page refresh
          sessionStorage.setItem('onesignal_just_published', 'true');
        }
      });
    });
  }
});
