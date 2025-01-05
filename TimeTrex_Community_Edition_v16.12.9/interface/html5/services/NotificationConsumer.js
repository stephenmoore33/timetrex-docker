import firebase from 'firebase/compat/app';
import 'firebase/compat/messaging';
import { TTAPI } from '@/services/TimeTrexClientAPI';
import 'bootstrap';
import TTEventBus from '@/services/TTEventBus';

class NotificationConsumer {

	constructor() {

		const firebaseConfig = {
			apiKey: "AIzaSyB9tM0QYb1D3JF07RqpeG-14ADGhezGRws",
			authDomain: "timetrex-app.firebaseapp.com",
			databaseURL: "https://timetrex-app.firebaseio.com",
			projectId: "timetrex-app",
			storageBucket: "timetrex-app.appspot.com",
			messagingSenderId: "462133047262",
			appId: "1:462133047262:web:1705b6bfca364bcd99b74f"
		};

		// Initialize Firebase
		firebase.initializeApp( firebaseConfig );
		this.browser_supported = firebase.messaging.isSupported();
		this.messaging = firebase.messaging.isSupported() ? firebase.messaging() : null;
		this.user_notification_device_token_api = TTAPI.APINotificationDeviceToken;
		this.notification_api = TTAPI.APINotification;
		this.user_preference_api = TTAPI.APIUserPreference;
		this.notification_holder = document.querySelector( '#notification-holder' );
		this.notification_duration = 120000;
		this.token = '';
		this.notification_total = 0;
		this.active_notification_stack = []; //Stack of notifications on screen.
		this.create_event_listeners = true;
		this.notification_sound = null;
		this.sound_timer = null;
		this.title_timer = null;
		this.previous_page_title = document.title;
		this.pending_events = [];
		this.event_bus = new TTEventBus( { view_id: 'notification_consumer' } );
	}

	setupUser( request_permission, refresh_token ) {
		if ( refresh_token === true ) {
			this.deleteNotificationDeniedCookie();
		}

		if ( this.isBrowserSupported( refresh_token ) === false ) {
			if ( refresh_token === true ) {
				TAlertManager.showAlert( $.i18n._( 'Sorry, this is browser does not support push notifications.' ), $.i18n._( 'Push Notifications' ) );
			}
			return false;
		}

		//If impersonating other users do not show notification permission request pop ups or notifications.
		var alternate_session_data = getCookie( 'AlternateSessionData' );
		if ( alternate_session_data && !refresh_token ) {
			return false;
		}

		//User has either repeatedly denied permissions or has disabled notifications for this browser in user preferences.
		if ( this.getNotificationDeniedCookie().asked_count >= 2 && refresh_token === false ) {
			return false;
		}

		if ( LocalCacheData.getLoginUser() && APIGlobal.pre_login_data.production === true && APIGlobal.pre_login_data.demo_mode !== true && APIGlobal.pre_login_data.sandbox !== true ) {
			this.notification_duration = parseInt( LocalCacheData.getLoginUserPreference().notification_duration ) * 1000;

			if ( Notification.permission === 'granted' && LocalCacheData.getLoginUserPreference().notification_status_id !== 2 ) {
				this.registerWorkerAndGetToken( true );
				this.createNotificationListeners();
				if ( refresh_token === true ) {
					// Provide feedback to user when they click refresh push notifications button.
					TAlertManager.showAlert( $.i18n._( 'Push Notifications Enabled' ), $.i18n._( 'Push Notifications' ) );
				}
			} else if ( request_permission && Notification.permission === 'default' ) {
				//Do not show mobile browsers notification permission alert unless they manually refresh notifications in My Account -> Preferences.
				if ( APIGlobal.pre_login_data.user_agent_data.is_mobile === true && refresh_token === false ) {
					return false;
				}
				this.showNotificationPermissionAlert();
			} else if ( refresh_token === true ) {
				this.showNotificationPermissionAlert();
			}
		} else {
			// Else user has declined permissions.
			if ( refresh_token === true ) {
				TAlertManager.showAlert( $.i18n._( 'Sorry, push notifications are disabled on this server.' ), $.i18n._( 'Push Notifications' ) );
			}
		}
	}

	// Register our service worker and vapidKey.
	// Safari and Firefox require this to trigger on a user action and not just randomly ask.
	registerWorkerAndGetToken( send_token ) {
		try {
			navigator.serviceWorker.register( './dist/v' + APIGlobal.pre_login_data.application_version + '/firebase-messaging-sw.js' )
				.then( ( registration ) => {
					this.messaging.getToken( {
						serviceWorkerRegistration: registration,
						vapidKey: 'BAIFamGLNE689DChvdL8bWrvgiPFUMGzPwBrxuDiKQNTzpbQu-VZ3urH3SdIOSQ4DUYAOmeTrhmGTNQaNdtW-2I'
					} ).then( ( currentToken ) => {
						if ( currentToken ) {
							this.token = currentToken;
							if ( send_token ) {
								this.sendDeviceToken( this.token );
							}

							//If permission alert exists, remove it.
							if ( $( '.modal-alert' ).length ) {
								$( '.modal-alert' ).remove();
								Global.setUIReady();
								this.sendAnalytics( 'allow-confirm' ); //Only trigger this when the employee is actually asked to allow permissions. Not everytime the service worker is registered.
							}
						} else {
							//request permission window happens
						}
					} ).catch( ( err ) => {
						// unexpected error
					} );
				} );
		} catch ( err ) {
			Debug.Text( 'Error attempting to register firebase service workers and push notification service: ' + err.message, 'NotificationConsumer.js', 'NotificationConsumer', 'registerWorkerAndGetToken', 9 );
		}
	}

	isBrowserSupported( show_message ) {
		if ( window.location.protocol !== 'https:' ) {
			Debug.Text( 'Not on a HTTPS connection. Push Notifications disabled.', 'NotificationConsumer.js', 'NotificationConsumer', 'checkBrowserSupported', 9 );
			if ( show_message === true ) {
				// Provide feedback to user why push notifications are not working if they clicked to refresh push notifications in My Account -> Preferences.
				TAlertManager.showAlert( $.i18n._( 'Push Notification are only available on HTTPS connections.' ), $.i18n._( 'Push Notifications' ) );
			}
			return false;
		} else if ( this.browser_supported === false ) {
			Debug.Text( 'User on an unsupported browser.', 'NotificationConsumer.js', 'NotificationConsumer', 'checkBrowserSupported', 9 );
			if ( show_message === true ) {
				// Provide feedback to user why push notifications are not working if they clicked to refresh push notifications in My Account -> Preferences.
				TAlertManager.showAlert( $.i18n._( 'Push Notification are not supported on this browser.' ), $.i18n._( 'Push Notifications' ) );
			}
			return false;
		}

		return true;
	}

	sendDeviceToken( device_token ) {
		this.user_notification_device_token_api.checkAndSetNotificationDeviceToken( device_token, 100, {
			onResult: function( res ) {
				let result = res.getResult();
			}
		} );
	}

	deleteToken() {
		var $this = this;
		if ( this.token ) {
			this.messaging.deleteToken().then( ( result ) => {
				let data = {};
				data.device_token = this.token;
				this.user_notification_device_token_api.deleteNotificationDeviceToken( [data], {
					onResult: function( res ) {
						$this.token = '';
						var result = res.getResult();
						if ( result ) {
							Debug.Text( 'Successfully deleted device token.', 'NotificationConsumer.js', 'NotificationConsumer', 'deleteToken', 9 );
						} else {
							Debug.Text( 'Failed to delete device token.', 'NotificationConsumer.js', 'NotificationConsumer', 'deleteToken', 9 );
						}
					}
				} );
			} ).catch( ( err ) => {
				Debug.Text( 'ERROR: While attempting to delete notification device token.', 'NotificationConsumer.js', 'NotificationConsumer', 'deleteToken', 9 );
			} );
		}
	}

	deleteAllTokens() {
		//This deletion path does necessarily mean the current session has a device token, but will attempt to delete all device tokens for the current user.
		var $this = this;
		this.user_notification_device_token_api.deleteAllNotificationDeviceTokens( {
			onResult: function( res ) {
				$this.token = '';
				var result = res.getResult();
				if ( result ) {
					Debug.Text( 'Successfully deleted all device tokens.', 'NotificationConsumer.js', 'NotificationConsumer', 'DeleteAllDeviceTokens', 9 );
				} else {
					Debug.Text( 'Failed to delete all device tokens, none may exist.', 'NotificationConsumer.js', 'NotificationConsumer', 'DeleteAllDeviceTokens', 9 );
				}
			}
		} );
	}

	createNotificationListeners() {
		if ( this.create_event_listeners === false ) {
			return;
		}

		var $this = this;
		this.create_event_listeners = false;

		// Handles foreground, background and background notification-clicked events.
		navigator.serviceWorker.addEventListener( 'message', payload => {
			this.handlePushNotificationEvent( payload.data.messageType === 'push-received', payload.data );
		} );

		this.notification_holder.addEventListener( 'click', event => {
			let element = event.target;

			if (element?.id === 'notification-holder') {
				//Issue #3466 - User did not click on a specific notification, clicked on holder for all notifications and we cannot clicked notification from that.
				//Returning early to prevent exceptions.
				return;
			}

			let notification_id = element.closest( '.toast' )?.querySelector( '.notification-link' )?.id ?? null;

			if ( !notification_id ) {
				Debug.Text( 'Notification ID not found.', 'NotificationConsumer.js', 'NotificationConsumer', 'createNotificationListeners', 9 );

				return;
			}

			if ( element.classList.contains( 'notification-close' ) ) {
				// Ignored notifications are not marked as read.
				this.removeNotification( notification_id );
			} else if ( element.classList.contains( 'notification-link' ) ) {
				// Mark notification as read when user clicks "view details" and is sent to the notificwtion link.
				this.setNotificationAsRead( element.id );
				this.removeNotification( element.id );

				//If notification has an open_view event attached, trigger that event and then delete it.
				for ( var i = this.pending_events.length - 1; i >= 0; i-- ) {
					if ( this.pending_events[i].id === element.id && this.pending_events[i].event === 'open_view' ) {
						this.openViewLinkedToNotification( this.pending_events[i].event_data );
						event.preventDefault(); //Stop default href from being followed.
						this.pending_events.splice( i, 1 ); //Delete event from pending events.
						this.startTimers();
						break;
					}
				}
			}
		} );

		window.addEventListener( 'focus', function( event ) {
			$this.cancelTimers();
		}, false );
	}

	handlePushNotificationEvent( foreground, payload ) {
		let timetrex_data = JSON.parse( payload.data.timetrex );

		Debug.Arr( payload, 'Push notification received.', 'NotificationConsumer.js', 'NotificationConsumer', 'handlePushNotificationEvent', 9 );

		// If on foreground displays the notification.
		if ( payload.messageType !== 'notification-clicked' && timetrex_data.user_id !== undefined && LocalCacheData.getLoginUser() && LocalCacheData.getLoginUser().id === timetrex_data.user_id && payload.notification && payload.notification.title ) {
			this.event_bus.emit( 'tt_topbar', 'profile_pending_counts', { //When push notification received update all "My Profile" badges.
				object_types: []
			} );

			// Increment total on bell everytime a new notification comes in.
			this.updateBell( true, this.notification_total + 1 );
			this.showNotification( payload.notification.title, payload.notification.body, payload.notification.click_action, timetrex_data.id, timetrex_data.priority, payload.data.link_target ? payload.data.link_target : '' );
			Debug.Text( 'Showing Notification on UI as user_id matches and notification was not a system click.', 'NotificationConsumer.js', 'NotificationConsumer', 'handlePushNotificationEvent', 9 );
		}

		if ( payload.messageType === 'notification-clicked' ) {
			Debug.Text( 'Notification was clicked on from desktop.', 'NotificationConsumer.js', 'NotificationConsumer', 'handlePushNotificationEvent', 9 );

			// Set notification as read as we about to redirect the user to the notification.
			this.setNotificationAsRead( timetrex_data.id );
			// If the notification toast is still on screen remove it.
			this.removeNotification( timetrex_data.id );

			// User clicked desktop notification, redirect them directly to the notification if a click_action was given.
			if ( payload.notification.click_action !== undefined && payload.notification.click_action !== '' ) {
				window.location = payload.notification.click_action;
			} else {
				window.location = Global.getBaseURL() + '#!m=Notification';
			}
		} else {
			//Handle background events if any are in the payload.
			if ( timetrex_data.event !== undefined && timetrex_data.event.length > 0 ) {
				this.handleBackgroundEvent( timetrex_data );
			}
		}
	}

	handleBackgroundEvent( timetrex_data ) {
		// Handles timetrex specific data of notification payload.
		Debug.Text( 'Background action was supplied in the push notification.', 'NotificationConsumer.js', 'NotificationConsumer', 'handlePushNotificationEvent', 9 );
		for ( let i = 0; i < timetrex_data.event.length; i++ ) {
			switch ( timetrex_data.event[i].type ) {
				case 'clean_cache':
					LocalCacheData.cleanNecessaryCache();
					break;
				case 'open_view':
					//Only triggered if user clicks the notification.
					this.pending_events.push( {
						id: timetrex_data.id,
						event: timetrex_data.event[i].type,
						event_data: timetrex_data.event[i]
					} );
					break;
				case 'open_view_immediate':
					this.openViewLinkedToNotification( timetrex_data.event[i] );
					this.startTimers();
					break;
				case 'redirect':
					if ( timetrex_data.event[i].ask === 1 ) {
						TAlertManager.showConfirmAlert( $.i18n._( timetrex_data.event[i].text ), $.i18n._( 'Redirect Confirmation' ), ( flag ) => {
							if ( flag === true ) {
								if ( timetrex_data.event[i].target && timetrex_data.event[i].target === '_blank' ) {
									window.open(
										timetrex_data.event[i].link,
										'_blank'
									);
								} else {
									window.location = timetrex_data.event[i].link;
								}

							}
						} );
					} else {
						if ( timetrex_data.event[i].target && timetrex_data.event[i].target === '_blank' ) {
							window.open(
								timetrex_data.event[i].link,
								'_blank'
							);
						} else {
							window.location = timetrex_data.event[i].link;
						}
					}
					break;
				case 'refresh_job_queue':
					this.event_bus.emit( 'tt_topbar', 'toggle_job_queue_spinner', {
						//Boolean events for job queue spinner.
						show: timetrex_data.event[i].show, //Show the job queue spinner
						get_job_data: timetrex_data.event[i].get_job_data, //Update job queue panel data
						check_completed: timetrex_data.event[i].check_completed //Check if job queue is completed and hide the job queue spinner if no pending tasks.
					} );

					//Update TimeSheet is user is on it.
					if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
					break;
			}
		}
	}

	openViewLinkedToNotification( event_data ) {
		//Open a view with the option of pre-filling fields.
		LocalCacheData.setAutoFillData( event_data.data );
		// This is taking them to listview in some cases so a on a onAdd/onEdit click can be clicked afterwards.
		IndexViewController.goToViewByViewLabel( event_data.view_name );

		// Ignore edit only views that don't have list views.
		if ( event_data.view_name !== 'InOut' && event_data.view_name !== 'Contact Information' ) {
			// Need to add the promise before onTabShow is called where it's originally intended to be added otherwise the below wait is not triggered.
			TTPromise.add( 'BaseViewController', 'onTabShow' );
			TTPromise.wait( 'BaseViewController', 'onTabShow', function() {
				if ( event_data.action === 'add' ) {
					LocalCacheData.current_open_primary_controller.onAddClick();
				} else if ( event_data.action === 'edit' ) {
					LocalCacheData.current_open_primary_controller.onEditClick( event_data.view_id );
				} else if ( event_data.action === 'view' ) {
					LocalCacheData.current_open_primary_controller.onViewClick( event_data.view_id );
				}
			} );
		}
	}

	setNotificationDeniedCookie( cookie_value ) {
		cookie_value.asked_count++;
		cookie_value.last_asked = new Date().getTime();

		setCookie( 'disable_push_notification_ask', JSON.stringify( cookie_value ), 10000, APIGlobal.pre_login_data.cookie_base_url );
	}

	deleteNotificationDeniedCookie() {
		deleteCookie( 'disable_push_notification_ask' );
	}

	getNotificationDeniedCookie() {
		if ( getCookie( 'disable_push_notification_ask' ) ) {
			return JSON.parse( getCookie( 'disable_push_notification_ask' ) );
		}

		var cookie_value = {};
		cookie_value.asked_count = 0;
		cookie_value.last_asked = 0;

		return cookie_value;
	}

	showNotificationPermissionAlert() {
		//Only ever ask twice to enable notification permissions for this browser.
		//If we have only asked once before and it has been 180 days since then, ask again.
		var notification_denied_cookie = this.getNotificationDeniedCookie();
		if ( notification_denied_cookie.asked_count > 1 || notification_denied_cookie.last_asked + ( 180 * 24 * 60 * 60 * 1000 ) > new Date().getTime() ) {
			return false;
		}

		TAlertManager.showModalAlert( 'push_notification', 'ask', ( flag ) => {
			if ( flag === true ) {
				this.showPermissionHelp();
				this.setUserPreferencePushNotification( 1 );
				this.sendAnalytics( 'allow' );
			} else {
				this.setUserPreferencePushNotification( 0 );
				this.sendAnalytics( 'deny' );
				this.setNotificationDeniedCookie( notification_denied_cookie );
			}
		} );
	}

	showPermissionHelp() {
		this.registerWorkerAndGetToken( true );
		this.createNotificationListeners();

		TAlertManager.showModalAlert( 'push_notification', 'wait_for_permission', ( flag ) => {
			if ( flag === true ) {
				TAlertManager.showModalAlert( 'push_notification', 'help_text', '' );
				this.showArrowToEnablePushNotifications();
				this.sendAnalytics( 'unsure' );
			}
		} );
	}

	showArrowToEnablePushNotifications() {
		const arrow = $( '<div class="permission-arrow">' +
			'<img style="display: block; margin-left: auto; margin-right: auto;" src="' + Global.getRealImagePath( 'images/notification-arrow.svg' ) + '" width="150" height="150!">' +
			'</div>' );

		$( '.modal-alert' ).append( arrow );
	}

	setUserPreferencePushNotification( status ) {
		if ( LocalCacheData.getLoginUser() && LocalCacheData.getLoginUserPreference() ) {
			var data = {};

			data.user_id = LocalCacheData.getLoginUser().id;
			data.id = LocalCacheData.getLoginUserPreference().id;
			data.browser_permission_ask_date = Math.round( new Date().getTime() / 1000 );
			//If user agrees set notifications to enabled. Else only set last browser_permission_ask_date.
			if ( status === 1 ) {
				data.notification_status_id = status;
			}

			this.user_preference_api.setUserPreference( data, {
				onResult: function( res ) {
					let result = res.getResult();
				}
			} );
		}
	}

	sendAnalytics( choice ) {
		Global.sendAnalyticsEvent( 'push_notifications', 'click', 'click:push_notifications:' + choice );
	}

	getUnreadNotifications() {
		this.notification_api.getUnreadNotifications( {
			onResult: ( result ) => {
				this.notification_total = parseInt( result.getResult() );
				this.updateBell( false, this.notification_total );
			}
		} );
	}

	getSystemNotifications( target ) {
		this.notification_api.getSystemNotification( target, {
			onResult: ( result ) => {
				var new_system_notifications = parseInt( result.getResult() );

				if ( new_system_notifications > 0 ) {
					this.updateBell( true, this.notification_total + new_system_notifications );
				}
			}
		} );
	}

	setNotificationAsRead( id ) {
		this.notification_api.setNotificationStatus( [id], 20, {
			onResult: ( result ) => {
				this.updateBell( true, this.notification_total - 1 );
			}
		} );
	}

	updateBell( manual, amount ) {
		if ( manual ) {
			this.notification_total = amount;
		}

		this.event_bus.emit( 'tt_topbar', 'notification_bell', {
			notification_count: this.notification_total
		} );
	}

	playSound() {
		// Only load notification sound when we first need it. Then reuse from then on.
		if ( this.notification_sound === null ) {
			this.notification_sound = new Audio();
			this.notification_sound.src = Global.getBaseURL( '../' ) + 'sounds/notification.mp3';
			this.notification_sound.load();
		}

		const playPromise = this.notification_sound.play();
		if ( playPromise !== undefined ) { //Older browsers play() does not return anything.
			playPromise.then( () => {
				//Notification audio is playing.
			} )
				.catch( error => {
					console.log( error );
				} );
		}
	}

	startTimers() {
		this.playSound();

		var $this = this;
		if ( document.hasFocus() === false && this.sound_timer === null ) {
			//Change page title and repeat notififation sound if page does not have focus and no timer is already set.
			this.sound_timer = setInterval( function() {
				if ( document.hasFocus() ) {
					//If tab is in focus cancel the timer.
					$this.cancelTimers();
				} else {
					$this.playSound();
				}
			}, 5000 );

			this.title_timer = setInterval( function() {
				if ( document.hasFocus() ) {
					//If tab is in focus cancel the timer.
					$this.cancelTimers();
				} else {
					if ( document.title === '!!!!!!!!!!!!!' ) {
						document.title = $.i18n._( 'NOTICE!' );
					} else {
						document.title = '!!!!!!!!!!!!!';
					}
				}
			}, 2000 );
		}
	}

	cancelTimers() {
		if ( this.sound_timer !== null ) {
			clearInterval( this.sound_timer );
			this.sound_timer = null;
		}

		if ( this.title_timer !== null ) {
			document.title = this.previous_page_title;
			clearInterval( this.title_timer );
			this.title_timer = null;
		}
	}

	showNotification( title, body, url, notification_id, priority, target ) {
		if ( priority == 10 ) {
			//User is not notified nor receives toast for low priority notifications.
			return false;
		}

		//All notifications other than low play notification sound.
		this.playSound();

		// To stop infinite stacking notifications on screen we only show up to 5 at a time. When a new one comes in we remove the oldest.
		// Allow high and critical priority notifications with priority 1 or 2 through.
		if ( this.active_notification_stack.length >= 5 && priority > 2 ) {
			this.removeNotification( this.active_notification_stack[0].id );
		}

		const notification = document.createElement( 'div' );
		notification.className = 'toast show toast-spacing';
		notification.style = 'width: 22rem; background-color: hsla(0, 0%, 100%, 1) !important; margin-bottom: 0.4rem !important;';

		const notification_header = document.createElement( 'div' );
		notification_header.className = 'toast-header';

		const notification_title = document.createElement( 'strong' );
		notification_title.className = 'me-auto';
		notification_title.textContent = title;

		const notification_close_button = document.createElement( 'button' );
		notification_close_button.className = 'btn-close notification-close';
		notification_close_button.setAttribute( 'aria-label', 'close' );
		notification_close_button.style.fontSize = '12px';

		const notification_body = document.createElement( 'div' );
		notification_body.className = 'toast-body';

		const notification_body_text = document.createElement( 'p' );
		notification_body_text.textContent = body;

		const notification_body_link = document.createElement( 'a' );
		notification_body_link.textContent = 'View Details';
		notification_body_link.id = notification_id;
		notification_body_link.className = 'notification-link';
		if ( url !== undefined && url !== '' ) {
			notification_body_link.href = url;
		} else {
			notification_body_link.href = Global.getBaseURL() + '#!m=Notification';
		}

		if ( target === '_blank' ) {
			notification_body_link.target = '_blank';
		}

		notification_header.appendChild( notification_title );
		notification_header.appendChild( notification_close_button );
		notification.appendChild( notification_header );

		notification_body.appendChild( notification_body_text );
		notification_body.appendChild( notification_body_link );
		notification.appendChild( notification_body );

		this.notification_holder.appendChild( notification );

		this.active_notification_stack.push( { id: notification_id, notification: notification } );

		if ( priority == 2 ) {
			//High priority notifications flash border twice around notification.
			notification.className += ' notification-outline-repeat';
		} else if ( priority == 1 ) {
			//Critical priority notification repeat the notification sound, change document title and continuously flash border around toast.
			this.startTimers( notification );
			notification.className += ' notification-outline-infinite';
		}

		if ( this.notification_duration !== 0 && priority != 1 ) {
			// User notification preferences with 0 delay or critical notifications with priority 1 are never automatically removed.
			setTimeout( () => {
				this.removeNotification( notification_id );
			}, this.notification_duration );
		}
	}

	removeNotification( id ) {
		for ( let i = 0; i < this.active_notification_stack.length; i++ ) {
			if ( this.active_notification_stack[i].id === id ) {
				this.active_notification_stack[i].notification.remove();
				this.active_notification_stack.splice( i, 1 );
				break;
			}
		}
	}

	removeAllNotifications() {
		this.active_notification_stack.forEach( ( active_notification ) => {
			active_notification.notification.remove();
		} );

		this.active_notification_stack = [];
		this.cancelTimers();
	}

	detectBrowserNeedsExtraPermission() {
		// Some browsers by default block push notification permission so we need to detect them to show user a different prompt
		if ( Global.getBrowserVendor() === 'Edge' ) {
			return true;
		} else {
			return false;
		}
	}

}

export const NotificationConsumerObj = new NotificationConsumer();