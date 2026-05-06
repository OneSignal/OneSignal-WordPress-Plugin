(function () {
  let subscribe = wp.data.subscribe;
  let select = wp.data.select;
  let dispatch = wp.data.dispatch;

  let wasSaving = false;

  subscribe(function () {
    let editor = select("core/editor");
    if (!editor) return;

    let isSaving = editor.isSavingPost();
    let isAutosave = editor.isAutosavingPost();
    let isSavingMetaBoxes = editor.isSavingMetaBoxes ? editor.isSavingMetaBoxes() : false;
    let isSavingNonAutosave = (isSaving && !isAutosave) || isSavingMetaBoxes;

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
    let appId = onesignalNotice.appId;
    if (!appId || !notificationId) return "";
    return (
      "https://dashboard.onesignal.com/apps/" +
      encodeURIComponent(appId) +
      "/push/" +
      encodeURIComponent(notificationId)
    );
  }

  function fetchAndDisplayNotice() {
    let postId = select("core/editor").getCurrentPostId();
    let body = new URLSearchParams({
      action: "onesignal_get_send_notice",
      nonce: onesignalNotice.nonce,
      post_id: postId,
    });

    fetch(onesignalNotice.ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString(),
      credentials: "same-origin",
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (response) {
        if (!response.success || !response.data) return;

        let status = response.data.status;
        let detail = response.data.detail;
        let dashboardUrl = buildDashboardUrl(detail);
        let type, message, actions;

        if (status === "success") {
          type = "success";
          message = "OneSignal: Push notification sent successfully.";
          actions = dashboardUrl
            ? [
                {
                  label: "View in OneSignal Dashboard",
                  onClick: function () {
                    window.open(dashboardUrl, "_blank", "noopener,noreferrer");
                  },
                },
              ]
            : [];
        } else if (status === "scheduled") {
          type = "info";
          message =
            "OneSignal: Push notification scheduled. If you change the scheduled post time in WordPress, the existing notification will be cancelled and a new one created.";
          actions = dashboardUrl
            ? [
                {
                  label: "View scheduled notification in OneSignal Dashboard",
                  onClick: function () {
                    window.open(dashboardUrl, "_blank", "noopener,noreferrer");
                  },
                },
              ]
            : [];
        } else if (status === "warning") {
          type = "warning";
          message = "OneSignal: Push notification not sent: " + detail;
          actions = [];
        } else {
          type = "error";
          message =
            "OneSignal: Push notification failed to send: " + detail;
          actions = [];
        }

        dispatch("core/notices").createNotice(type, message, {
          id: "onesignal-send-notice",
          isDismissible: true,
          actions: actions,
        });
      })
      .catch(function () {
        // Silently ignore network errors
      });
  }
})();
