jQuery(document).ready(function ($) {
    $('#onesignal-update-notice').on('click', '.notice-dismiss', function () {
        $.post(onesignalData.ajax_url, {
            action: 'dismiss_onesignal_deprecation_notice',  // This action should match the PHP function's AJAX hook
            nonce: onesignalData.nonce,
        });
    });
});

