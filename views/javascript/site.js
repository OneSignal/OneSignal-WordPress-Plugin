$(function() {
  $('.site .menu .item').tab();
  $('form[role="configuration"] [type=checkbox]').checkbox();
  $('form[role="configuration"] [role=popup]').popup({
    hoverable: true,
    position: 'right center'
  });
  $('.ui.sidebar')
    .sidebar('toggle')
  ;
});

function activateSetupTab(tab) {
  $('.menu .item').tab('change tab', tab);
  $('body').scrollTop(0);
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