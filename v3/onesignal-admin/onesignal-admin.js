window.addEventListener("DOMContentLoaded", () => {
  const sendToMobileHelpIcon = document.querySelector(".mobile-app .help");
  const sendToMobileInfoDiv = document.querySelector(".mobile-app .information");
  const utmParamsHelpIcon = document.querySelector(".utm-params .help");
  const utmParamsInfoDiv = document.querySelector(".utm-params .information");
  const customPostTypesHelpIcon = document.querySelector(".custom-post-types .help");
  const customPostTypesInfoDiv = document.querySelector(".custom-post-types .information");
  const notificationOnPostFromPluginHelpIcon = document.querySelector(".notification-on-post-from-plugin .help");
  const notificationOnPostFromPluginInfoDiv = document.querySelector(".notification-on-post-from-plugin .information");

  const setupToggleAction = (helpIcon, infoDiv) => {
    if (helpIcon && infoDiv) {
      helpIcon.addEventListener("click", () => {
        infoDiv.style.display =
          infoDiv.style.display === "none" ? "inherit" : "none";
      });
    }
  };

  setupToggleAction(sendToMobileHelpIcon, sendToMobileInfoDiv);
  setupToggleAction(utmParamsHelpIcon, utmParamsInfoDiv);
  setupToggleAction(customPostTypesHelpIcon, customPostTypesInfoDiv);
  setupToggleAction(notificationOnPostFromPluginHelpIcon, notificationOnPostFromPluginInfoDiv);
});

window.addEventListener("DOMContentLoaded", () => {
  const appIdInput = document.querySelector("#appid");
  const apiKeyInput = document.querySelector("#apikey");
  const utmInput = document.querySelector("#utm-params");
  const autoSendCheckbox = document.querySelector("#auto-send");
  const sendToMobileCheckbox = document.querySelector("#send-to-mobile");
  const saveButton = document.querySelector("#save-settings-button");
  const customPostTypesInput = document.querySelector("#custom-post-types");
  const notificationOnPostFromPluginCheckbox = document.querySelector("#notification-on-post-from-plugin");
  const notificationOnPageCheckbox = document.querySelector("#auto-send-pages");

  const haveAllAdminInputsLoaded = appIdInput &&
    apiKeyInput &&
    autoSendCheckbox &&
    sendToMobileCheckbox &&
    utmInput &&
    saveButton &&
    customPostTypesInput &&
    notificationOnPostFromPluginCheckbox &&
    notificationOnPageCheckbox;

  if (haveAllAdminInputsLoaded) {
    const initialAppId = appIdInput.value;
    const initialApiKey = apiKeyInput.value;
    const initialUtmInput = utmInput.value;
    const initialAutoSend = autoSendCheckbox.checked;
    const initialSendToMobile = sendToMobileCheckbox.checked;
    const initialCustomPostTypes = customPostTypesInput.value;
    const initialNotificationOnPostFromPlugin = notificationOnPostFromPluginCheckbox.checked;
    const initialNotificationOnPage = notificationOnPageCheckbox.checked;

    function isValidUUID(uuid) {
      const uuidRegex =
        /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
      return uuid.length > 0 && uuidRegex.test(uuid); // Ensure it's not empty and matches regex
    }

    function isValidApiKey(apiKey) {
      const base64Regex =
        /^(?:[A-Za-z0-9+/]{4}){12,}(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/; // At least 48 characters in Base64
      const opaqueTokenRegex = /^os_v[2-9]_app_[2-7a-z]{56,}$/;
      return (
        base64Regex.test(apiKey) || opaqueTokenRegex.test(apiKey)
      ); // Ensure it's not empty and matches regex
    }

    function updateValidationIcon(input, isValid) {
      const icon = input.parentElement.querySelector(".validation-icon");
      if (icon) {
        icon.textContent = isValid ? "✅" : "❌";
      }
    }

    function hasFormChanged() {
      const appIdChanged = appIdInput.value !== initialAppId;
      const apiKeyChanged = apiKeyInput.value !== initialApiKey;
      const utmChanged = utmInput.value !== initialUtmInput;
      const autoSendChanged = autoSendCheckbox.checked !== initialAutoSend;
      const sendToMobileChanged = sendToMobileCheckbox.checked !== initialSendToMobile;
      const customPostTypesChanged = customPostTypesInput.value !== initialCustomPostTypes;
      const notificationOnPostFromPluginChanged = notificationOnPostFromPluginCheckbox.checked !== initialNotificationOnPostFromPlugin;
      const notificationOnPageChanged = notificationOnPageCheckbox.checked !== initialNotificationOnPage;

      return appIdChanged ||
        apiKeyChanged ||
        autoSendChanged ||
        sendToMobileChanged ||
        utmChanged ||
        customPostTypesChanged ||
        notificationOnPostFromPluginChanged ||
        notificationOnPageChanged;
    }

    function toggleSaveButton() {
      const appIdValid = isValidUUID(appIdInput.value);
      const apiKeyValid = apiKeyInput.value.length == 0 || isValidApiKey(apiKeyInput.value);
      const formChanged = hasFormChanged();

      // Enable button if either text inputs are valid or toggles have changed
      const enabled = formChanged && appIdValid && apiKeyValid;
      saveButton.disabled = !enabled;
    }

    appIdInput.addEventListener("input", () => {
      const isValid = isValidUUID(appIdInput.value);
      updateValidationIcon(appIdInput, isValid);
      toggleSaveButton();
    });

    apiKeyInput.addEventListener("input", () => {
      const isValid = isValidApiKey(apiKeyInput.value);
      updateValidationIcon(apiKeyInput, isValid);
      toggleSaveButton();
    });

    utmInput.addEventListener("input", toggleSaveButton);
    autoSendCheckbox.addEventListener("change", toggleSaveButton);
    sendToMobileCheckbox.addEventListener("change", toggleSaveButton);
    customPostTypesInput.addEventListener("input", toggleSaveButton);
    notificationOnPostFromPluginCheckbox.addEventListener("change", toggleSaveButton);
    notificationOnPageCheckbox.addEventListener("change", toggleSaveButton);

    // Initial state on page load
    toggleSaveButton();
  }
});
