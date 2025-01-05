import { TTBackboneView } from '@/views/TTBackboneView';
import TTEventBus from '@/services/TTEventBus';

class ApplicationRouter extends Backbone.Router {
	constructor( options = {} ) {
		_.defaults( options, {
			controller: null,

			routes: {
				'': 'onViewChange',
				'!:viewName': 'onViewChange',
				'*notFound': 'notFound'
			}
		} );

		super( options );
		this.event_bus = new TTEventBus({ view_id: 'index_controller' });
	}

	reloadView( view_id ) {
		//error: Uncaught ReferenceError: XXXXViewController is not defined ininterface/html5/#!m=TimeSheet line 3
		// Happens when quickly click on context menu and network is slow.
		if ( typeof window[view_id + 'ViewController'] === 'function' &&
			LocalCacheData.current_open_primary_controller &&
			LocalCacheData.current_open_primary_controller.viewId === view_id ) {
			LocalCacheData.current_open_primary_controller.setSelectLayout();
			LocalCacheData.current_open_primary_controller.search();
		}
	}

	notFound( url ) {

		var new_url = Global.getBaseURL();

		Global.setURLToBrowser( new_url + '#!m=Login' );
	}

	/* jshint ignore:start */
	onViewChange( viewName ) {
		var $this = this;
		var args = {};
		var view_id;
		var edit_id;
		var action;
		var auto_login_timer;

		if ( Global.needReloadBrowser ) {
			Global.needReloadBrowser = false;
			window.location.reload();
			return;
		}

		if ( viewName ) {
			args = Global.buildArgDic( viewName.split( '&' ) );
		}
		if ( viewName && viewName.indexOf( 'm=' ) >= 0 ) {
			view_id = Global.sanitizeViewId( args.m );
		} else {
			view_id = 'Login';
		}

		// #VueContextMenu# Trigger Vue Router changes
		// Add interface functions for this once POC works.
		// console.log('aaaa onViewChange', view_id); // comparing triggers against BaseVC.
		// VueRouter.push('/view/'+view_id);
		// End Vue router

		LocalCacheData.fullUrlParameterStr = viewName;
		LocalCacheData.setAllURLArgs( args );

		if ( view_id == 'Install' ) {
			if ( LocalCacheData.loadViewRequiredJSReady ) {
				IndexViewController.openWizard( 'InstallWizard', null, function() {
					// need to link to the login interface.
				} );
			} else {
				auto_login_timer = setInterval( function() {
					if ( timeout_count == 100 ) {
						clearInterval( auto_login_timer );
					}
					timeout_count = timeout_count + 1;
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						IndexViewController.openWizard( 'InstallWizard', null, function() {
							// need to link to the login interface.
						} );
						clearInterval( auto_login_timer );
					}
				}, 600 );
			}
			return;
		}

		if ( LocalCacheData.getAllURLArgs().sm === 'ResetPassword' && LocalCacheData.getAllURLArgs().key ) {
			IndexViewController.openWizard( 'ResetForgotPasswordWizard', null, function() {
				LocalCacheData.setAllURLArgs( _.omit( LocalCacheData.getAllURLArgs(), 'sm' ) );
				LocalCacheData.setAllURLArgs( _.omit( LocalCacheData.getAllURLArgs(), 'key' ) );
				TAlertManager.showAlert( $.i18n._( 'Password has been changed successfully, you may now login.' ) );
				var new_url = Global.getBaseURL();
				Global.setURLToBrowser( new_url + '#!m=Login' );
				return;
			} );
			return;
		}

		edit_id = args.id; //Accrual id is combined. x_x -- Also need to support UUID.
		action = args.a;

		if ( LocalCacheData.current_open_view_id === view_id ) {

			if ( LocalCacheData.current_open_primary_controller ) {

				if ( action ) {
					switch ( action ) {
						case 'edit':
							//Error: Unable to get property 'id' of undefined or null reference in /interface/html5/IndexController.js?v=8.0.0-20141230-125406 line 87
							if ( typeof LocalCacheData.current_open_primary_controller != 'undefined' && ( !LocalCacheData.current_open_primary_controller.edit_view || ( LocalCacheData.current_open_primary_controller.current_edit_record && LocalCacheData.current_open_primary_controller.current_edit_record.id && LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id ) ) ) {
								//Makes ure when doing copy_as_new, don't open this
								if ( LocalCacheData.current_doing_context_action === 'edit' ) {
									openEditView( edit_id );
								}

							}
							break;
						case 'new':
							if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
								openEditView();
							}

							break;
						case 'view':
							switch ( view_id ) {
								case 'MessageControl':
									if ( args.t === 'message' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											( !checkIds() ) ) {
											openEditView( edit_id, true );
										}
									} else if ( args.t === 'request' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											( LocalCacheData.current_open_primary_controller.current_select_message_control_data.id != edit_id ) ) {
											openEditView( edit_id, true );
										}
									}
									break;
								default:
									// Error: Unable to get property 'id' of undefined or null reference
									if ( typeof LocalCacheData.current_open_primary_controller != 'undefined' && ( !LocalCacheData.current_open_primary_controller.edit_view || ( LocalCacheData.current_open_primary_controller.current_edit_record && LocalCacheData.current_open_primary_controller.current_edit_record.id && LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id ) ) ) {
										openEditView( edit_id, true );
									}
									break;
							}

					}

					return;
				} else {
					if ( view_id === 'Login' ) {
						$this.event_bus.emit( 'tt_login', 'step_changed' ); //Notify login view to change step when browser back button is clicked.
					}

					if ( LocalCacheData.current_open_primary_controller.edit_view &&
						LocalCacheData.current_open_primary_controller.current_edit_record ) {

						if ( LocalCacheData.current_open_primary_controller.is_mass_editing ) {
							return;
						}

						LocalCacheData.current_open_primary_controller.buildContextMenu( true );
						LocalCacheData.current_open_primary_controller.removeEditView();
						this.cleanAnySubViewUI();

					}
				}

			}
			return;

		} else {
			LocalCacheData.edit_id_for_next_open_view = edit_id;

			if ( action ) {
				LocalCacheData.current_doing_context_action = action;
			}
		}

		Global.setDeepLink();
		var timeout_count;
		if ( view_id !== 'Login' && !LocalCacheData.getLoginUser() ) {
			Global.setURLToBrowser( Global.getBaseURL() + '#!m=Login' );
			return;
		} else {
			timeout_count = 0;
			if ( view_id !== 'Login' && Global.isSet( view_id ) ) {
				if ( LocalCacheData.loadViewRequiredJSReady ) {
					initRibbonMenuAndCopyRight();
				} else {
					auto_login_timer = setInterval( function() {
						if ( timeout_count == 100 ) {
							clearInterval( auto_login_timer );
						}
						timeout_count = timeout_count + 1;
						if ( LocalCacheData.loadViewRequiredJSReady ) {
							initRibbonMenuAndCopyRight();
							clearInterval( auto_login_timer );
						}
					}, 600 );
				}
			} else {
				showRibbonMenuAndLoadView();
			}

		}

		function showRibbonMenuAndLoadView() {

			if ( view_id && view_id !== 'Login' ) {
				$this.event_bus.emit( 'tt_topbar', 'refresh_login_data' ); // Update the topbar company and logged in user.
				$this.setContentDivHeight();
			}

			 if ( view_id && view_id !== 'Login' ) {
				$( 'body' ).removeClass( 'login-bg' );
				$( 'body' ).addClass( 'application-bg' );
			}

			switch ( view_id ) {
				case 'JobApplication':
					// require( ['autolinker/Autolinker.min', 'pdfjs-dist/build/pdf', 'pdfjs/compatibility', 'pdfjs/ui_utils', 'pdfjs/text_layer_builder'], function( autolinker ) {
					// 	window.Autolinker = autolinker;
						Global.loadViewSource( view_id, view_id + 'ViewController.js', function() {
							var permission_id = view_id;
							if ( PermissionManager.checkTopLevelPermission( permission_id ) ) {
								BaseViewController.loadView( view_id );
							} else {
								TAlertManager.showAlert( 'Permission denied', 'ERROR', function() {
									if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
										MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
									} else {
										MenuManager.goToView( 'Home' );
									}
								} );
								Debug.Text( 'Navigation permission denied. Permission: ' + permission_id, 'IndexController.js', 'IndexController', 'showRibbonMenuAndLoadView', 10 );
							}
						} );
					// } );
					break;
				default:
					Global.loadViewSource( view_id, view_id + 'ViewController.js', function() {
						var permission_id = view_id;

						switch ( view_id ) {
							case 'ClientGroup':
								permission_id = 'Client';
								break;
							case 'ProductGroup':
								permission_id = 'Product';
								break;
							case 'PayStubTransactionSummaryReport':
							case 'PayStubTransaction':
								permission_id = 'PayStub';
								break;

						}

						if ( view_id === 'Login' || view_id === 'Home' || PermissionManager.checkTopLevelPermission( permission_id ) ) {
							BaseViewController.loadView( view_id );
						} else {
							if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId && LocalCacheData.current_open_primary_controller.viewId == 'LoginView' ) {
								if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
									MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
								} else {
									MenuManager.goToView( 'Home' );
								}
							} else {
								TAlertManager.showAlert( 'Permission denied', 'ERROR', function() {
									if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
										MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
									} else {
										MenuManager.goToView( 'Home' );
									}
								} );
							}
							Debug.Text( 'Navigation permission denied. Permission: ' + permission_id, 'IndexController.js', 'IndexController', 'showRibbonMenuAndLoadView', 10 );
						}

					} );
					break;
			}

		}

		function initRibbonMenuAndCopyRight() {

			// Trigger Vue TopMenu to load, now that all pre-requisitives like login info have been run.
			$this.event_bus.emit( 'tt_top_container', 'ready_to_load_top_bar' );

			//Add copy right
			Global.bottomContainer().css( 'display', 'block' );

			//Start check signal
			Global.setSignalStrength();

			//Add feedback event
			Global.bottomFeedbackLinkContainer().css( 'display', 'block' );
			$( '#feedback-link' ).off( 'click.feedback' ).on( 'click.feedback', function() {
				$().TFeedback( {
					source: 'ManualTrigger',
					manual_trigger: true,
					prompt_for_feedback: true // regardless of server feedback state input, click should always allow feedback
				} );
			} );

			$( '#copy_right_info_1' ).css( 'display', 'inline' );
			$( '#copy_right_logo_link' ).attr( 'href', 'https://' + LocalCacheData.getLoginData().organization_url );
			if ( !$( '#copy_right_logo' ).attr( 'src' ) ) {
				$( '#copy_right_logo' ).attr( 'src', ServiceCaller.getURLByObjectType( 'copyright' ) ); // Use the same logo as the one on the login screen. (Changed from smcopyright)
			}
			showRibbonMenuAndLoadView();
		}

		function checkIds() {

			if ( Global.isArray( LocalCacheData.current_open_primary_controller.current_edit_record ) ) {
				for ( var i = 0; i < LocalCacheData.current_open_primary_controller.current_edit_record.length; i++ ) {
					var item = LocalCacheData.current_open_primary_controller.current_edit_record[i];

					if ( item.id && item.id === edit_id ) {
						return true;
					}
				}
			} else {
				item = LocalCacheData.current_open_primary_controller.current_edit_record;
				if ( item.id && item.id === edit_id ) {
					return true;
				}
			}

			return false;
		}

		function openEditView( edit_id, view_mode ) {
			var type;
			switch ( view_id ) {
				case 'MessageControl':
					type = args.t;
					var item = {};
					if ( type === 'message' ) {
						item.id = edit_id;
					} else {
						item.object_id = edit_id;
						item.object_type_id = 50;
					}
					LocalCacheData.current_open_primary_controller.onViewClick( item );
					break;

				case 'TimeSheet':
					type = args.t;

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id, type );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id, type );
						}
					}

					break;
				default:

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id );
						}
					}

					break;
			}
		}

	}

	/* jshint ignore:end */

	cleanAnySubViewUI() {
		var children = Global.contentContainer().children();

		if ( children.length > 1 ) {
			for ( var i = 1; i < children.length; i++ ) {
				// Object doesn't support property or method 'remove', Not sure why, add try catch to ingore this error since this should no harm
				try {

					if ( $( children[i] ).attr( 'id' ) === LocalCacheData.current_open_primary_controller.ui_id ) {
						continue;
					} else {
						children[i].remove();
					}

				} catch ( e ) {
					//Do nothing
				}

			}
		}
	}

	setContentDivHeight() {
		// Now done by CSS calculation
		// Global.contentContainer().css( 'height', ( Global.bodyHeight() - Global.topContainer().height() - $('.topbar').height() ) ); // TODO: do away with these hardcoded calculated heights in future refactor.
		// $( window ).resize( function() {
		// 	Global.contentContainer().css( 'height', ( Global.bodyHeight() - Global.topContainer().height() - $('.topbar').height()) ); // TODO: do away with these hardcoded calculated heights in future refactor.
		// } );

		Global.contentContainer().removeClass( 'content-container' );
		Global.contentContainer().addClass( 'content-container-after-login' );
	}

	removeCurrentView( callBack ) {

		if ( LocalCacheData.current_open_edit_only_controller ) {
			clean( LocalCacheData.current_open_edit_only_controller );
			LocalCacheData.current_open_edit_only_controller = null;
		}

		if ( LocalCacheData.current_open_primary_controller ) {
			if ( LocalCacheData.current_open_primary_controller.edit_view ) {
				clean( LocalCacheData.current_open_primary_controller );
			}
			LocalCacheData.current_open_primary_controller.unmountContextMenu();
			Global.contentContainer().children().detach().remove(); // This is about 3x faster than Global.contentContainer().empty()
			LocalCacheData.current_open_primary_controller.cleanWhenUnloadView( callBack );
		} else {

			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}

		function clean( viewController ) {
			viewController.clearErrorTips();
			// Cannot read property 'remove' of null in interface/html5/IndexController.js?v=9.0.0-20151016-153057 line 439
			if ( viewController.edit_view ) {
				// #question: Not sure when this would be trigged, but this should surely run through viewController.removeEditView()?
				viewController.unmountContextMenu(); // Not sure when this clean would be trigged, but lets have this here just in case.
				viewController.edit_view.remove();
			}
			viewController.sub_log_view_controller = null;
			viewController.edit_view_ui_dic = {};
			viewController.edit_view_ui_validation_field_dic = {};
			viewController.edit_view_form_item_dic = {};
			viewController.edit_view_error_ui_dic = {};
			LocalCacheData.current_doing_context_action = '';
		}
	}

}

export class IndexViewController extends TTBackboneView {

	constructor( options = {} ) {
		_.defaults( options, {
			el: 'body', //So we can add event listener for all elements
			router: null,
		} );

		super( options );
	}

	initialize( options ) {

		super.initialize( options );
		this.router = new ApplicationRouter();

		//Set title in index.php instead.
		//$( 'title' ).html( '' );

		this.router.controller = this;
		//Error: Backbone.history has already been started in interface/html5/framework/backbone/backbone-min.js?v=9.0.1-20151022-162110 line 28
		if ( !Backbone.History.started ) {
			Backbone.history.start();
		}

		IndexViewController.instance = this;

	}

	static goToView( view_name, filter ) {
		Global.closeEditViews( function() {
			LocalCacheData.default_filter_for_next_open_view = filter;
			MenuManager.goToView( view_name, true );
		} );

	}

	static goToViewByViewLabel( view_label ) {
		var view_name;
		switch ( view_label ) {
			case 'Exceptions':
				view_name = 'Exception';
				break;
			case 'Messages':
				view_name = 'MessageControl';
				break;
			case 'Requests':
				view_name = 'Request';
				break;
			case 'Contact Information':
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'LoginUserContact' );
				return;
			case 'InOut':
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'InOut' );
				return;
			default:
				var reg = /\s/g;
				view_name = view_label.replace( reg, '' );
				break;
		}

		MenuManager.goToView( view_name, true );

	}

	//DEPRECATED: all new wizards should go through openWizardController()
	static openWizard( wizardName, defaultData, callBack ) {
		Global.setUINotready();

		BaseWizardController.default_data = defaultData;
		BaseWizardController.call_back = callBack;

		switch ( wizardName ) {
			default:
				// track edit view only view
				Global.trackView( wizardName );
				Global.loadViewSource( wizardName, wizardName + 'Controller.js', function() {
					BaseWizardController.openWizard( wizardName, wizardName + '.html' );
				} );
				break;
		}

	}

	//ATTN: New wizards should go through this
	static openWizardController( wizard_id, filter_data, source_view ) {
		Global.setUINotready();
		// BaseWizardController.default_data = defaultData;
		// BaseWizardController.call_back = callBack;
		switch ( wizard_id ) {
			default:
				// track edit view only view
				Global.trackView( wizard_id );
				Global.loadViewSource( wizard_id, wizard_id + '.js', function() {

					if ( LocalCacheData.current_open_wizard_controllers.some( wizard => wizard.wizard_id === wizard_id ) ) {
						switch ( wizard_id ) {
							case 'ReportViewWizard':
								break;
							case 'PayrollRemittanceAgencyEventWizardController':
								//if the current wizard is a PayrollRemittanceAgencyEventWizard, we need to remember the cards that were clicked because it's just minimized.
								var wizard = LocalCacheData.current_open_wizard_controllers.find( wizard => wizard.wizard_id === wizard_id );
								wizard.remove(); // #2768 Removes the existing wizard DOM element if it exists, to avoid duplicate wizards. Causes issue when user double clicks on Wizard icon (without overlay) or Tax Wizard opened when already open. TODO: Add overlay to prevent background clicks.
								wizard.getStepObject().initialize( wizard );
								break;
							default:
								var wizard = LocalCacheData.current_open_wizard_controllers.find( wizard => wizard.wizard_id === wizard_id );
								wizard.onCloseClick();
								break;
						}
					}

					Global.loadViewSource( wizard_id, wizard_id + '.html', function( result ) {
						var args = {};
						var template = _.template( result );
						$( 'body' ).append( template( args ) );

						//#2422 - pass the data on to the process transactions wizard
						// This must be here because we don't instantiate the WizardController ( the host view ) in the html file like we do with other views so that we can pass it constructor arguments
						switch ( wizard_id ) {
							case 'ProcessTransactionsWizardController':
								new ProcessTransactionsWizardController( filter_data );
								break;
						}

						Global.setUIInitComplete();
					} );
				} );
				break;
		}

	}

	static openReport( parent_view_controller, view_name, id, tab_name ) {
		// Note: This behaviour/router call is also in BaseViewController.loadView()
		// VueRouter.push({
		// 	name: 'report',
		// 	params: {
		// 		viewId: view_name
		// 	}
		// }).then(function() {
		// 	doNextAfterReportRouteChange();
		// });
		doNextAfterReportRouteChange(); // Now using one global context menu, so no need for router calls. Just using the one single LegacyView for now.

		function doNextAfterReportRouteChange() {
			Global.closeEditViews( function() {
				if ( LocalCacheData.current_open_report_controller ) {
					LocalCacheData.current_open_report_controller.removeEditView();
				}

				ProgressBar.showOverlay();

				switch ( view_name ) {
					default:
						var path = Global.getViewPathByViewId( view_name );
						if ( path ) {
							Global.loadScript( path + view_name + 'ViewController', function() {
								Debug.Text( 'R-LOADING: ' + view_name, 'IndexViewController.js', 'IndexViewController', 'openReport', 10 );
								/* jshint ignore:start */
								TTPromise.add( 'Reports', 'openReport' );
								//var $view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
								var $view_controller = new window[view_name + 'ViewController']({ edit_only_mode: true });
								/* jshint ignore:end */

								TTPromise.wait( 'Reports', 'openReport', function() {
									doNext( view_name, tab_name );
								} );

								function doNext( view_name, tab_name ) {
									$view_controller.parent_view_controller = parent_view_controller;
									$view_controller.openEditView();

									var current_url = window.location.href;
									if ( current_url.indexOf( '&sm' ) > 0 ) {
										current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
									}
									current_url = current_url + '&sm=' + view_name;

									if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
										current_url = current_url + '&sid=' + LocalCacheData.default_edit_id_for_next_open_edit_view;
									}

									if ( typeof tab_name != 'undefined' ) {
										LocalCacheData.current_open_report_controller.selected_tab = tab_name;
										current_url += '&tab=' + tab_name;
									} else if ( window.location.href.indexOf( '&tab=' ) > -1 ) {
										var tab_name = window.location.href;
										tab_name = tab_name.substr( ( window.location.href.indexOf( '&tab=' ) + 5 ) ); //get the selected tab name
										tab_name = tab_name.substr( 0, window.location.href.indexOf( '&' ) ); // incase there are subsequent arguments after the tab argument
										current_url += '&tab=' + tab_name;
									}

									Global.setURLToBrowser( current_url );

								}

							} );
							break;
						} else {
							console.debug( 'Report View does not exist! View Name: ' + view_name );
							if ( ServiceCaller.root_url && APIGlobal.pre_login_data.base_url ) {
								Global.setURLToBrowser( ServiceCaller.root_url + APIGlobal.pre_login_data.base_url );
							}
						}
				}
			} );
		}
	}

	//Open edit view
	static openEditView( parent_view_controller, view_name, id, action_function ) {
		if ( LocalCacheData.current_open_report_controller ) { //don't allow editviews over report views.
			LocalCacheData.current_open_report_controller.onCancelClick( null, null, function() {
				Global.closeEditViews( function() {
					IndexViewController.openEditView( parent_view_controller, view_name, id, action_function );
				} );
			} );
			return;
		} else if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId && LocalCacheData.current_open_edit_only_controller.viewId == view_name ) { //Stop edit only views from overlaying themselves with the same view and disconnecting others from the menu
			LocalCacheData.current_open_edit_only_controller.setEditMenu(); //display the right edit menu
			$( '#ribbon_view_container .context-menu:visible a' ).click();
		} else {
			doNext();
		}

		function doNext() {
			var view_controller = null;

			if ( !PermissionManager.checkTopLevelPermission( view_name ) && view_name !== 'Map' ) {
				if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId && LocalCacheData.current_open_primary_controller.viewId == 'LoginView' ) {
					if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
						MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
					} else {
						MenuManager.goToView( 'Home' );
					}
				} else {
					TAlertManager.showAlert( 'Permission denied', 'ERROR', function() {
						if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
							MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
						} else {
							MenuManager.goToView( 'Home' );
						}
					} );
				}
				Debug.Text( 'Navigation permission denied. View: ' + view_name, 'IndexController.js', 'IndexController', 'openEditView', 10 );
				return;
			}

			if ( view_name == 'Request' ) {
				action_function = 'openAddView';
			}

			if ( !action_function ) {
				action_function = 'openEditView';
			}

			// Added originally in 83a1df72 for issue #1805 but caused a bug mentioned in issue #2091 the steps to reproduce the bug are as follows:
			// 1. Go to invoice > client contacts and highlite a Client Contact 2. Click the "Edit Client" button on the ribbon menu
			// 3. Click the Invoices tab 4. Edit an invoice 5. Click Payment on the ribbon menu 6. Click Cancel to bring you back to the invoice edit scren, then Cancel again to go back to the Invoices tab on the client screen.
			// 7. You won't be back there. The Client screen will be missing.
			//if ( LocalCacheData.current_open_edit_only_controller ) {
			//LocalCacheData.current_open_edit_only_controller.onCancelClick();
			//}

			// track edit view only view
			Global.trackView( view_name );

			Global.loadViewSource( view_name, view_name + 'ViewController.js', function() {
				/* jshint ignore:start */
				//view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
				var view_controller = new window[view_name + 'ViewController']({ edit_only_mode: true });
				/* jshint ignore:end */

				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					view_controller.parent_view_controller = parent_view_controller;

					view_controller[action_function]( id );
					if ( TTUUID.isUUID( id ) ) {
						var current_url = window.location.href;
						if ( current_url.indexOf( '&sm' ) > 0 ) {
							current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
						}
						if ( id && _.isString( id ) ) {
							current_url = current_url + '&sm=' + view_name + '&sid=' + id;
						} else {
							current_url = current_url + '&sm=' + view_name;
						}

						Global.setURLToBrowser( current_url );
					}

					LocalCacheData.current_open_edit_only_controller = view_controller;
				} );
			} );
		}
	}

	static initializeNotifications( target ) {
		NotificationConsumer.setupUser( true, false );
		NotificationConsumer.getSystemNotifications( target );
	}

	static testInternetConnection() {
		if ( !navigator.onLine ) {
			window.internet_connection_available = false;
			window.is_testing_internet_connection = false;
		}

		var img = new Image();
		window.is_testing_internet_connection = true;
		img.onload = function() {
			window.internet_connection_available = true;
			window.is_testing_internet_connection = false;
		};
		img.onerror = function( e ) {
			window.internet_connection_available = false;
			window.is_testing_internet_connection = false;
		};
		img.src = 'https://coreapi.timetrex.com/images/ping.gif';
	}
}
IndexViewController.instance = null;

