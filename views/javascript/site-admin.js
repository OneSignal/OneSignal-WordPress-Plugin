jQuery(function() {
  jQuery('.site .menu .item').tab();
  jQuery('form[role="configuration"] [type=checkbox]').checkbox();
  jQuery('form[role="configuration"] [role=popup]').popup({
    hoverable: true,
    position: 'right center'
  });
  jQuery('.ui.sidebar')
    .sidebar('toggle')
  ;
});

function activateSetupTab(tab) {
  jQuery('.menu .item').tab('change tab', tab);
  jQuery('body').scrollTop(0);
}

function showSupportMessage(type) {
  var message = "";
  if (type === "not_sure_protocol") {
    message = "Hello, I'm trying to set up the WordPress plugin. I need to enter a Site URL into my platform config, but I'm not sure whether I should use HTTP or HTTPS?";
  } else if (type == 'chrome-push-settings') {
    message = "Hello, I'm having some trouble configuring Chrome push settings from the WordPress plugin guide.";
  } else if (type == 'safari-push-settings') {
    message = "Hello, I'm having some trouble configuring Safari push settings from the WordPress plugin guide.";
  }
  Intercom('showNewMessage', message);
}


function showHttpPopup() {
  subdomain = jQuery('[name=subdomain]').val();
  message_localization_opts = {
    actionMessage: jQuery('[name=prompt_action_message]').val(),
    exampleNotificationTitleDesktop: jQuery('[name=prompt_example_notification_title_desktop]').val(),
    exampleNotificationMessageDesktop: jQuery('[name=prompt_example_notification_message_desktop]').val(),
    exampleNotificationTitleMobile: jQuery('[name=prompt_example_notification_title_mobile]').val(),
    exampleNotificationMessageMobile: jQuery('[name=prompt_example_notification_message_mobile]').val(),
    exampleNotificationCaption: jQuery('[name=prompt_example_notification_caption]').val(),
    acceptButtonText: jQuery('[name=prompt_accept_button_text]').val(),
    cancelButtonText: jQuery('[name=prompt_cancel_button_text]').val()
  };
  var message_localization_opts_str = '';
  if (message_localization_opts) {
    var message_localization_params = ['actionMessage',
      'exampleNotificationTitleDesktop',
      'exampleNotificationMessageDesktop',
      'exampleNotificationTitleMobile',
      'exampleNotificationMessageMobile',
      'exampleNotificationCaption',
      'acceptButtonText',
      'cancelButtonText'];
    for (var i = 0; i < message_localization_params.length; i++) {
      var key = message_localization_params[i];
      var value = message_localization_opts[key];
      var encoded_value = encodeURIComponent(value);
      if (value) {
        message_localization_opts_str += '&' + key + '=' + encoded_value;
      }
    }
  }
  var domainPrefix = 'https://' + subdomain + '.onesignal.com/sdks/initOneSignalHttp';
  if (window.popupPreviewWindow) {
    window.popupPreviewWindow.close();
  }
  window.popupPreviewWindow = window.open(domainPrefix + "?" + message_localization_opts_str, "_blank", 'scrollbars=yes, width=' + 550 + ', height=' + 480 + ', top=' + 0 + ', left=' + 0);

  if (popupPreviewWindow)
    popupPreviewWindow.focus();
}