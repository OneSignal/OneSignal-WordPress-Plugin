(function () {
    var subscribe = wp.data.subscribe;
    var select = wp.data.select;
    var dispatch = wp.data.dispatch;

    var wasSaving = false;

    var unsubscribe = subscribe(function () {
        var editor = select('core/editor');
        if (!editor) return;

        var isSaving = editor.isSavingPost();
        var isAutosave = editor.isAutosavingPost();
        var isSavingNonAutosave = isSaving && !isAutosave;

        // Detect the moment a non-autosave save transitions from in-progress to complete
        if (wasSaving && !isSavingNonAutosave) {
            wasSaving = false;
            fetchAndDisplayNotice();
        }

        if (isSavingNonAutosave) {
            wasSaving = true;
        }
    });

    function buildDashboardUrl(notificationId) {
        var appId = onesignalNotice.appId;
        if (!appId || !notificationId) return '';
        return 'https://dashboard.onesignal.com/apps/' + encodeURIComponent(appId) + '/push/' + encodeURIComponent(notificationId);
    }

    function fetchAndDisplayNotice() {
        var body = new URLSearchParams({
            action: 'onesignal_get_send_notice',
            nonce: onesignalNotice.nonce,
        });

        fetch(onesignalNotice.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (response) {
                if (!response.success || !response.data) return;

                var status = response.data.status;
                var notificationId = response.data.detail;
                var dashboardUrl = buildDashboardUrl(notificationId);
                var type, message, actions;

                if (status === 'success') {
                    type = 'success';
                    message = 'OneSignal: Push notification sent successfully.';
                    actions = dashboardUrl ? [{ label: 'View in OneSignal Dashboard', url: dashboardUrl }] : [];
                } else if (status === 'scheduled') {
                    type = 'info';
                    message = 'OneSignal: Push notification scheduled. If you change the scheduled post time in WordPress, the existing notification will be cancelled and a new one created.';
                    actions = dashboardUrl ? [{ label: 'View scheduled notification in OneSignal Dashboard', url: dashboardUrl }] : [];
                } else {
                    type = 'error';
                    message = 'OneSignal: Push notification failed to send: ' + notificationId;
                    actions = [];
                }

                dispatch('core/notices').createNotice(type, message, {
                    id: 'onesignal-send-notice',
                    isDismissible: true,
                    actions: actions,
                });
            })
            .catch(function () {
                // Silently ignore network errors
            });
    }
})();
