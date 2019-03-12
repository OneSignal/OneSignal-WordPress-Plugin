jQuery(document).ready(function() {
	if( !wp.data ) {
		console.warn("OneSignal Push: couldn't load wp.data. https://bit.ly/2F4G0bt");
		return;
	}

	const editor = wp.data.select('core/editor');
	const get_wp_attr = ( attr ) => { return editor.getEditedPostAttribute( attr ) };
	var post_id = ajax_object.post_id;
	var started = 0;
	var interval;
	var interval_count = 0;

	/*
	 * Subscribes function to state-change listener
	 *  - checks change in post modified date
	 *  - triggers interval that checks if recipient data available in backend
	 */
	var first_modified;
	wp.data.subscribe( () => {
	// runs with each change in state
		let post = wp.data.select('core/editor').getCurrentPost();
	
		// runs until post data loads
		if ( !first_modified && post !== {}) {
			first_modified = post.modified;
		}
		
		// latest modified date  
		var { modified } = post;

		// is checked
		let send_os_notif = jQuery('[name=send_onesignal_notification]').attr('checked');

		// if hasn't started and change is detected
		if ( !started && modified !== first_modified && send_os_notif ) {
			interval = setInterval(get_metadata,3000);
			started = 1;
		}
	});

	/*
	 * Checks if post has meta for "recipients"
	 *  - means request to OS has finished
	 */
	const get_metadata = () => {
		var data = {
			action 	: 'has_metadata',
			post_id : post_id
		};
		
		jQuery.get(ajax_object.ajax_url, data, function(response) {
			response = JSON.parse( response );
			let { recipients, status_code, error_message } = response;
		
			console.log(response);
			if( status_code >= 400 || !status_code ) {
				clearInterval( interval );
				if ( !status_code ) {
					error_notice( "HTTP request failed");
				}

				if ( !error_message ) {
					error_notice("OneSignal Push: there was a "+status_code+" error sending your notification");				
				} else {
					error_notice("OneSignal Push: "+error_message);
				}
				interval_count = 0;
				started = 0;
				first_modified = null;
				return;
			}
			
			if( recipients == 0 ) {
				clearInterval (interval)
				error_notice( "OneSignal Push: there were no recipients. You either 1) have no subscribers yet or 2) you hit the rate-limit. Please try again in an hour" );
				interval_count = 0;
				started = 0;
				first_modified = null;
			} else if ( recipients ) {
				clearInterval( interval );
				show_notice( recipients );
				interval_count = 0;
				started = 0;
				first_modified = null;
			}

			// try for 1 minute
			if ( interval_count > 20 ) {
				clearInterval(interval);
				error_notice("OneSignal Push: Bad network connection");
				interval_count = 0;
				started = 0;
				first_modified = null;
			}
		});
		interval_count += 1;
	}

	/*
	 * Gets recipient count and shows notice
	 */
	const show_notice = (recipients) => {
		let plural = recipients == 1 ? '' : 's';
		wp.data.dispatch('core/notices').createNotice('info', 'OneSignal Push: Successfully sent a notification to '+recipients+' recipient'+plural, {
			isDismissible: true
		});
	}
	
	const error_notice = ( error ) => {
		wp.data.dispatch('core/notices').createNotice('error', error, {
			isDismissible: true
		});
	}
});
