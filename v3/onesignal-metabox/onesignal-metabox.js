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
});
