import jQueryBridget from 'jquery-bridget';
import Masonry from 'masonry-layout';
import { TTBackboneView } from '@/views/TTBackboneView';

export class HomeViewController extends TTBackboneView {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.home-view',

			// _required_files: ['jquery.masonry', 'jquery-bridget'],
			user_generic_data_api: null,
			context_menu_array: null,
			viewId: null,
			dashletControllerArray: null,
			initMasonryDone: false,
			dashboard_container: false,
			order_data: false,
			current_scroll_position: false,
			current_mouse_position: null

		} );

		super( options );
	}

	/**
	 * When changing this function, you need to look for all occurences of this function because it was needed in several bases
	 * BaseViewController, HomeViewController, BaseWizardController, QuickPunchBaseViewControler
	 *
	 * @returns {Array}
	 */
	// filterRequiredFiles() {
	// 	var retval = [];
	// 	var required_files = this._required_files;
	//
	// 	if ( required_files && required_files[0] ) {
	// 		retval = required_files;
	// 	} else {
	// 		for ( var edition_id in required_files ) {
	// 			if ( Global.getProductEdition() >= edition_id ) {
	// 				retval = retval.concat( required_files[edition_id] );
	// 			}
	// 		}
	// 	}
	//
	// 	Debug.Arr( retval, 'RETVAL', 'BaseViewController.js', 'BaseViewController', 'filterRequiredFiles', 10 );
	// 	return retval;
	// }

	initialize( options ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();
		var $this = this;

		super.initialize( options );

		// require( this.filterRequiredFiles(), function( Masonry, jQueryBridget ) {

		$this.viewId = 'Home';
		LocalCacheData.current_open_primary_controller = $this;
		$this.user_generic_data_api = TTAPI.APIUserGenericData;
		$this.api_dashboard = TTAPI.APIDashboard;

		jQueryBridget( 'masonry', Masonry, $ );
		$this.dashboard_container = $( '.dashboard-container' );
		$this.initMasonryDone = false;
		$this.initContextMenu();
		$this.initDashBoard();
		$this.autoOpenEditOnlyViewIfNecessary();

		TTPromise.resolve( 'BaseViewController', 'initialize' );
		// } );
	}

	autoOpenEditOnlyViewIfNecessary() {
		if ( LocalCacheData.getAllURLArgs() && LocalCacheData.getAllURLArgs().sm && !LocalCacheData.current_open_edit_only_controller ) {
			if ( LocalCacheData.getAllURLArgs().sm.indexOf( 'Report' ) < 0 ) {
				IndexViewController.openEditView( this, LocalCacheData.getAllURLArgs().sm, LocalCacheData.getAllURLArgs().sid );
			} else {
				IndexViewController.openReport( this, LocalCacheData.getAllURLArgs().sm );
				if ( LocalCacheData.getAllURLArgs().sid ) {
					LocalCacheData.default_edit_id_for_next_open_edit_view = LocalCacheData.getAllURLArgs().sid;
				}
			}
		}
	}

	initContextMenu() {
		var $this = this;
		this.buildContextMenu();
		this.setDefaultMenu();
		$( this.el ).unbind( 'click' ).bind( 'click', function() {
			$this.setDefaultMenu();
		} );
	}

	determineContextMenuMountAttributes() {
		return {
			id: ContextMenuManager.generateMenuId( 'main_context', 'home_view' ),
			parent_mount_point: $( this.el )
		};
	}

	unmountContextMenu() {
		// This should be able to handle various menu's as the determine menu id function will identify the right menu (view, edit etc)
		ContextMenuManager.unmountContextMenu( this.determineContextMenuMountAttributes().id );
	}

	buildContextMenu() {
		LocalCacheData.current_open_sub_controller = null;

		// Vue Context Menu initialization
		var menu_attributes = this.determineContextMenuMountAttributes();

		if( ContextMenuManager.getMenu( menu_attributes.id ) === undefined ) { // Prevents multiple context borders and menu builds .e.g. when a subview is closed and parent menu is rebuilt.
			ContextMenuManager.createAndMountMenu( menu_attributes.id, menu_attributes.parent_mount_point, this ); // #VueContextMenu# Initialize Vue ContextMenuManager here so that each view has their own unique one.

			// #VueContextMenu#context-border creation to put a border around a context menu and the contents it relates to. This will help users understand which context menu belongs to what if there is more than one menu on the page.
			var context_parent = menu_attributes.parent_mount_point; // $('.edit-view-tab-bar');
			var context_label = 'Dashboard'; // this.context_menu_name;

			context_parent.prepend('<span class="context-border-label">'+ context_label +'</span>');
			context_parent.wrapInner('<div class="context-border"></div>');
		}

		this.buildContextMenuModels();
	}

	onContextMenuClick( context_btn, menu_name ) {
		var $this = this;
		var id;

		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			if ( context_btn.disabled ) {
				return;
			}
		}

		switch ( id ) {
			case 'add':
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case 'refresh_all':
				ProgressBar.showOverlay();
				for ( var i = 0; i < $this.dashletControllerArray.length; i++ ) {
					$( $this.dashletControllerArray[i].el ).find( '.refresh-btn' ).trigger( 'click' );
				}
				break;
			case 'auto_arrange':
				TAlertManager.showConfirmAlert( Global.auto_arrange_dashlet_confirm_message, null, function( result ) {
					if ( result ) {
						ProgressBar.showOverlay();

						$this.initDashBoard( true );
					} else {
						ProgressBar.closeOverlay();
					}
				} );
				break;
			case 'reset_all':
				TAlertManager.showConfirmAlert( Global.rese_all_dashlet_confirm_message, null, function( result ) {
					if ( result ) {
						ProgressBar.showOverlay();
						var ids = [];
						for ( var i = 0; i < $this.dashlet_list.length; i++ ) {
							ids.push( $this.dashlet_list[i].id );
						}
						if ( ids.length > 0 ) {
							$this.user_generic_data_api.deleteUserGenericData( ids, {
								onResult: function( result ) {
									if ( result && result.isValid() ) {
										doResetAllNext();
									} else {
										TAlertManager.showErrorAlert( result );
									}
								}
							} );
						} else {
							doResetAllNext();
						}

						function doResetAllNext() {
							if ( $this.order_data ) {
								$this.user_generic_data_api.deleteUserGenericData( $this.order_data.id, {
									onResult: function() {
										$this.initDashBoard();
									}
								} );
							} else {
								$this.initDashBoard();
							}
						}

					} else {
						ProgressBar.closeOverlay();
					}
				} );
				break;
			case 'in_out':
			case 'timesheet':
			case 'schedule':
			case 'request':
			case 'pay_stub':
				this.onNavigationClick( id );
				break;
		}
		Global.triggerAnalyticsContextMenuClick( context_btn );
	}

	onNavigationClick( iconName ) {
		switch ( iconName ) {
			case 'in_out':
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'InOut' );
				break;
			case 'timesheet':
				IndexViewController.goToView( 'TimeSheet' );
				break;
			case 'schedule':
				IndexViewController.goToView( 'Schedule' );
				break;
			case 'request':
				IndexViewController.goToView( 'Request' );
				break;
			case 'pay_stub':
				IndexViewController.goToView( 'PayStub' );
				break;
		}
	}

	//Call this when select grid row
	//Call this when setLayout
	setDefaultMenu() {
		// Copied and modified from BaseViewController, to enable the icons again after ContextMenuManager.buildContextMenuModelFromBackbone disabled them all.
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;

			// In all view controllers other than HomeView, in order to reduce flashing of icons between menu build and permission settings, all icons in Vue context menu now set to disabled (in ContextMenuManager), waiting on the BaseViewController.setDefaultMenu to enable them if needed. However, HomeView does not have the same code, hence it has been added here now below.
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			this.setCustomDefaultMenuIcon( id, context_btn );
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'timesheet':
				this.setDefaultMenuTimeSheetIcon( context_btn, grid_selected_length );
				break;
			case 'schedule':
				this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length, 'schedule' );
				break;
			case 'request':
				this.setDefaultMenuRequestIcon( context_btn, grid_selected_length, 'request' );
				break;
			case 'pay_stub':
				this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length, 'pay_stub' );
				break;
		}
	}

	setDefaultMenuTimeSheetIcon( context_btn, grid_selected_length ) {
		if ( PermissionManager.checkTopLevelPermission( 'TimeSheet' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuScheduleIcon( context_btn, grid_selected_length ) {
		if ( PermissionManager.checkTopLevelPermission( 'Schedule' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuRequestIcon( context_btn, grid_selected_length ) {
		if ( PermissionManager.checkTopLevelPermission( 'Request' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuPayStubIcon( context_btn, grid_selected_length ) {
		if ( PermissionManager.checkTopLevelPermission( 'PayStub' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onAddClick() {
		var $this = this;
		IndexViewController.openWizard( 'DashletWizard', null, function() {
			$this.initDashBoard();
		} );
	}

	removeContentMenuByName( name ) {
		if ( !LocalCacheData.current_open_primary_controller ) {
			return;
		}
		var primary_view_id = LocalCacheData.current_open_primary_controller.viewId;

		if ( !Global.isSet( name ) ) {
			name = this.context_menu_name;
		}
		var tab = $( '#ribbon ul a' ).filter( function() {
			return $( this ).attr( 'ref' ) === name;
		} ).parent();

		var index = $( 'li', $( '#ribbon' ) ).index( tab );
		if ( index >= 0 ) {
			$( '#ribbon_view_container' ).tabs( 'refresh' );
		}
	}

	buildContextMenuModels() {

		var icons = [];

		icons.push( {
			label: $.i18n._( 'Add Dashlet' ),
			id: 'add',
			//group: editor_group,
			vue_icon: 'tticon tticon-add_black_24dp',
			sort_order: 1010,
			permission_result: true,
			permission: null
		} );

		icons.push( {
			label: $.i18n._( 'Jump To' ),
			id: 'jump_to_header',
			menu_align: 'right',
			action_group: 'jump_to',
			action_group_header: true,
			permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
		} );

		icons.push( {
			label: $.i18n._( 'TimeSheet' ),
			id: 'timesheet',
			menu_align: 'right',
			action_group: 'jump_to',
			//group: navigation_group,
			sort_order: 2020,
			permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheet' ),
			permission: null
		} );

		icons.push( {
			label: $.i18n._( 'Schedules' ),
			id: 'schedule',
			menu_align: 'right',
			action_group: 'jump_to',
			//group: navigation_group,
			sort_order: 2030,
			permission_result: PermissionManager.checkTopLevelPermission( 'Schedule' ),
			permission: null
		} );

		icons.push( {
			label: $.i18n._( 'Requests' ),
			id: 'request',
			menu_align: 'right',
			action_group: 'jump_to',
			//group: navigation_group,
			sort_order: 2040,
			permission_result: PermissionManager.checkTopLevelPermission( 'Request' ),
			permission: null
		} );

		icons.push( {
			label: $.i18n._( 'Pay Stubs' ),
			id: 'pay_stub',
			menu_align: 'right',
			action_group: 'jump_to',
			//group: navigation_group,
			sort_order: 2050,
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStub' ),
			permission: null
		} );

		icons.push (
			{
				label: '', //Empty label. vue_icon is displayed instead of text.
				id: 'other_header',
				menu_align: 'right',
				action_group: 'other',
				action_group_header: true,
				vue_icon: 'tticon tticon-more_vert_black_24dp',
			},
			{
				label: $.i18n._( 'Auto Arrange' ),
				id: 'auto_arrange',
				menu_align: 'right',
				action_group: 'other',
				permission_result: true,
				permission: null
			},
			{
				label: $.i18n._( 'Refresh All Dashlets' ),
				id: 'refresh_all',
				menu_align: 'right',
				action_group: 'other',
				permission_result: true,
				permission: null
			},
			{
				label: $.i18n._( 'Restore Default Dashlets' ),
				id: 'reset_all',
				menu_align: 'right',
				action_group: 'other',
				permission_result: true,
				permission: null
			}
		)

		 ContextMenuManager.buildContextMenuModelFromBackbone( this.determineContextMenuMountAttributes().id, { icons: icons }, this );
	}

	unLoadCurrentDashlets() {
		//Error: TypeError: this.dashletControllerArray is null in interface/html5/framework/jquery.min.js?v=9.0.2-20151106-092147 line 2 > eval line 368
		if ( this.dashletControllerArray ) {
			for ( var i = 0; i < this.dashletControllerArray.length; i++ ) {
				var dashletController = this.dashletControllerArray[i];
				dashletController.cleanWhenUnloadView();
			}
		}
		this.dashletControllerArray = [];
	}

	initDashBoard( auto_arrange ) {
		var $this = this;
		var i = 0;
		if ( !this.dashletControllerArray ) {
			this.dashletControllerArray = [];
		} else {
			this.unLoadCurrentDashlets();
		}

		if ( this.initMasonryDone ) {
			this.dashboard_container = $( '.dashboard-container' );
			this.dashboard_container.masonry(); //#2353 - fix js exception on auto arrange "masonry is not initialized"
			this.dashboard_container.masonry( 'destroy' );
			this.dashboard_container.sortable( 'destroy' );
			this.dashboard_container.empty();
			this.initMasonryDone = false;
		}
		$this.dashlet_controller_dic = {};
		this.user_generic_data_api.getUserGenericData( {
			filter_data: {
				script: 'global_dashboard',
				deleted: false
			}
		}, {
			onResult: function( result ) {
				var dashlet_list = result.getResult();
				if ( !Global.isArray( dashlet_list ) || dashlet_list.length < 1 ) {
					$this.api_dashboard.getDefaultDashlets( {
						onResult: function( result ) {
							dashlet_list = result.getResult();
							$this.is_getting_default_dashlet = true;
							doOrder( dashlet_list );
						}
					} );
				} else {
					doOrder( dashlet_list );
				}
			}
		} );

		function doOrder( dashlet_list ) {
			$this.removeNoResultCover();
			$this.user_generic_data_api.getUserGenericData( {
				filter_data: {
					script: 'global_dashboard_order',
					name: 'order_data',
					deleted: false
				}
			}, {
				onResult: function( order_result ) {
					order_result = order_result.getResult();
					if ( Global.isArray( order_result ) && order_result.length == 1 ) {
						$this.order_data = order_result[0];
						if ( $this.is_getting_default_dashlet ) {
							$this.order_data.data = [];
							$this.addMissedDashLetToOrder( dashlet_list );
							$this.is_getting_default_dashlet = false;
						} else {
							//Error: Uncaught TypeError: $this.order_data.data.push is not a function in interface/html5/#!m=Home line 550
							if ( !$this.order_data.data || !Global.isArray( $this.order_data.data ) ) {
								$this.order_data.data = [];
							}
							$this.addMissedDashLetToOrder( dashlet_list );
						}
						$this.dashlet_list = [];
						for ( var y = 0, yy = $this.order_data.data.length; y < yy; y++ ) {
							var order_id = $this.order_data.data[y];
							var found = false;
							for ( var j = 0, jj = dashlet_list.length; j < jj; j++ ) {
								var dashlet = dashlet_list[j];
								if ( dashlet.id.toString() === order_id.toString() ) {
									$this.dashlet_list.push( dashlet );
									found = true;
									break;
								}
							}
						}
					} else {
						$this.dashlet_list = dashlet_list;
					}
					if ( $this.dashlet_list.length > 0 ) {
						loadPage( $this.dashlet_list[i] );
					}
				}
			} );
		}

		function loadPage( dashlet_data ) {
			Global.loadScript( 'views/home/dashlet/DashletController.js', function() {
				var id = 'dashlet_' + dashlet_data.id;
				var dash_let = $( '<div class="dashlet-container" id="' + id + '">' +
					'<div class="dashlet">' +
					'<span class="title"></span>' +
					'<div class="button-bar">' +
					'<span class="tticon tticon-visibility_black_24dp view-btn" title="View"></span>' +
					'<span class="tticon tticon-edit_black_24dp modify-btn" title="Edit"></span>' +
					'<span class="tticon tticon-delete_black_24dp delete-btn" title="Delete"></span>' +
					'<span class="tticon tticon-refresh_black_24dp refresh-btn" title="Refresh"></span>' +
					'</div>' +
					'<div class="content">' +
					'<table id="grid"></table>' +
					'<iframe class="report-iframe" id="iframe"></iframe>' +
					'</div>' +
					'</div>' +
					'<div class="dashlet-left-cover" ></div>' +
					'<div class="dashlet-right-cover" ></div>' +
					'</div>' );
				if ( !dashlet_data.data.height || auto_arrange ) {
					dashlet_data.data.height = 200;
				}
				if ( !dashlet_data.data.width || auto_arrange ) {
					if ( dashlet_data.data.dashlet_type === 'custom_report' ) {
						dashlet_data.data.width = 99;
					} else {
						dashlet_data.data.width = 33;
					}
				}

				//Dashlet resizing changed in Vue to "snap to" certain values. Making sure users coming from legacy ui have a smooth
				//transition to the new "snap to" dashlet sizes. For example an old dashlet height of 257px will be set to 260px.
				//If we do not change old values then "snap to" functionality will not work due to mismatch.
				if ( dashlet_data.data.height % 10 !== 0 ) {
					dashlet_data.data.height = Math.round( dashlet_data.data.height / 10 ) * 10;
				}
				if ( dashlet_data.data.width % 1 !== 0 ) {
					dashlet_data.data.width = Math.round( dashlet_data.data.width );
				}

				dash_let.css( 'height', dashlet_data.data.height + 'px' );
				dash_let.css( 'width', dashlet_data.data.width + '%' );
				$this.dashboard_container.append( dash_let );
				dash_let.find( '.tticon' ).unbind( 'click' ).bind( 'click', function( e ) {
					var target = e.target;
					var container = $( target ).parent().parent().parent();
					// Error: Uncaught TypeError: Cannot read property 'split' of undefined in interface/html5/#!m=Home line 490
					if ( !container.attr( 'id' ) ) {
						return;
					}
					var dashlet_id = container.attr( 'id' ).split( '_' )[1];

					if ( $( target ).hasClass( 'delete-btn' ) ) {
						$this.deleteDashlet( dashlet_id, $( container ) );
					}

					if ( $( target ).hasClass( 'modify-btn' ) ) {
						$this.modifyDashlet( dashlet_id );
					}

				} );
				var dashlet_controller = new DashletController();
				$this.dashletControllerArray.push( dashlet_controller );
				dashlet_controller.el = '#' + id;
				dashlet_controller.data = dashlet_data;
				dashlet_controller.homeViewController = $this;
				dashlet_controller.initContent();
				// Update width and height to default one if doing auto arrange
				if ( auto_arrange ) {
					$this.user_generic_data_api.setUserGenericData( dashlet_data, {
						onResult: function( result ) {
						}
					} );
				}
				i = i + 1;
				if ( i < $this.dashlet_list.length ) {
					loadPage( $this.dashlet_list[i] );
				} else {
					$this.updateLayout();
				}
			} );
		}

		//BUG#2070 - Break sortable for mobile because it negatively impacts usability
		if ( Global.detectMobileBrowser() ) {
			this.dashboard_container.sortable( { disabled: true } );
		}
	}

	showNoResultCover() {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {
			related_view_controller: this,
			is_new: true,
			message: $.i18n._( 'No Dashlets Found' ),
			iconLabel: $.i18n._( 'Add' )
		} );
		this.no_result_box.attr( 'id', '#dashboard_' + this.viewId + '_no_result_box' );
		$( this.el ).find( '.container' ).append( this.no_result_box );
	}

	removeNoResultCover() {
		if ( this.no_result_box && this.no_result_box.length > 0 ) {
			this.no_result_box.remove();
		}
		this.no_result_box = null;
	}

	addMissedDashLetToOrder( dashlet_list ) {
		var $this = this;
		//Error: Uncaught TypeError: $this.order_data.data.push is not a function in interface/html5/#!m=Home line 546
		if ( !$this.order_data || !$this.order_data.data ) {
			return;
		}
		for ( var j = 0, jj = dashlet_list.length; j < jj; j++ ) {
			var dashlet = dashlet_list[j];
			var found = false;
			for ( var y = 0, yy = $this.order_data.data.length; y < yy; y++ ) {
				var order_id = $this.order_data.data[y];
				if ( dashlet.id.toString() === order_id.toString() ) {
					found = true;
					break;
				}
			}
			if ( !found ) {
				$this.order_data.data.push( dashlet.id.toString() );
			}
			if ( this.is_getting_default_dashlet ) {
				$this.order_data.data.sort();
			}

		}
	}

	updateLayout() {
		var $this = this;

		this.saveScrollPosition();
		if ( this.initMasonryDone ) {
			this.dashboard_container.masonry( 'destroy' );
			this.dashboard_container.sortable( 'destroy' );
		} else {
			this.initMasonryDone = true;
		}
		this.dashboard_container.masonry( {
			'columnWidth': 1,
			itemSelector: '.dashlet-container'
		} );

		this.dashboard_container.on( 'mouseup', function() {
			$( '.dashlet-cover--display-red' ).removeClass( 'dashlet-cover--display-red' );
			$( '.dashlet-cover--display-green' ).removeClass( 'dashlet-cover--display-green' );
		} );

		this.dashboard_container.on( 'mousemove', function( e ) {
			var x = e.pageX;
			var y = e.pageY;
			$this.current_mouse_position = { x: x, y: y };
		} );

		this.dashboard_container.sortable( {
			forceHelperSize: true,
			forcePlaceholderSize: true,
			grid: [3, 10],
			containment: '.container',
			change: function( e, ui ) {
				$( '.dashlet-cover--display-red' ).removeClass( 'dashlet-cover--display-red' );
				$( '.dashlet-cover--display-green' ).removeClass( 'dashlet-cover--display-green' );
				// //#2353 custom code to maintain the hover ui hint
				var placeholder_index = $( '.ui-sortable-placeholder' ).index();

				if ( placeholder_index != -1 ) {
					var dashlets_to_loop = $( '.dashlet-container' );
					for ( var x = 0; x < dashlets_to_loop.length; x++ ) {
						if ( $( dashlets_to_loop[x] ).attr( 'id' ) != ui.item.attr( 'id' ) ) {
							//ensure collision and on one side of placeholder or the other
							if ( ( $( dashlets_to_loop[x] ).index() == ( placeholder_index + 1 ) || $( dashlets_to_loop[x] ).index() == ( placeholder_index - 1 ) ) && checkCollision( $( dashlets_to_loop[x] ), $this.current_mouse_position ) ) {
								//mouseover the right half.
								var direction;
								if ( $this.current_mouse_position.x >= ( $( dashlets_to_loop[x] ).offset().left + ( $( dashlets_to_loop[x] ).width() / 2 ) ) && $this.current_mouse_position.x <= ( $( dashlets_to_loop[x] ).offset().left + ( $( dashlets_to_loop[x] ).width() ) ) ) {
									direction = 'RIGHT';
									$( dashlets_to_loop[x] ).find( '.dashlet-right-cover' ).addClass( 'dashlet-cover--display-green' );
								} else {
									direction = 'LEFT';
									$( dashlets_to_loop[x] ).find( '.dashlet-left-cover' ).addClass( 'dashlet-cover--display-green' );
								}

							}
						}
					}
				}
			},

			stop: function( e, ui ) {
				$this.saveNewOrder();
				$this.updateLayout();
				var draggingTargetId = ui.item.attr( 'id' ).split( '_' )[1];
				for ( var j = 0, jj = $this.dashletControllerArray.length; j < jj; j++ ) {
					var dashlet = $this.dashletControllerArray[j];
					if ( draggingTargetId == dashlet.data.id ) {
						dashlet.refreshIfNecessary();
					}
				}
				$this.dashboard_container.masonry( 'reloadItems' );
			}
		} );

		function checkCollision( el, mouse_coords ) {
			el = $( el );

			if ( el.offset().left <= mouse_coords.x && ( el.offset().left + el.width() ) >= mouse_coords.x
				&& el.offset().top <= mouse_coords.y && el.offset().top + el.height() >= mouse_coords.y
			) {

				return true;
			}
			return false;
		}

		this.recoverCurrentScrollPosition();

		TTPromise.resolve( 'init', 'init' );
	}

	saveNewOrder( callBack ) {
		var $this = this;
		var dashlets = $( this.el ).find( '.dashlet-container:not(.ui-sortable-placeholder)' );
		var new_order = [];
		for ( var i = 0, ii = dashlets.length; i < ii; i++ ) {
			var dashlet = $( dashlets[i] );
			var id = dashlet.attr( 'id' ).split( '_' )[1];
			new_order.push( id );
		}
		var arg = {};
		if ( this.order_data ) {
			this.order_data.data = new_order;
			arg = this.order_data;
		} else {
			arg.name = 'order_data';
			arg.script = 'global_dashboard_order';
			arg.is_default = true;
			arg.data = new_order;
		}

		this.user_generic_data_api.setUserGenericData( arg, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data != true && TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.order_data = { id: result_data };
					$this.order_data.data = new_order;
					if ( callBack ) {
						callBack();
					}
				} else if ( result_data === true ) {
					if ( callBack ) {
						callBack();
					}
				}
			}
		} );
	}

	cleanWhenUnloadView() {
		this.unLoadCurrentDashlets();
	}

	modifyDashlet( id ) {
		var $this = this;
		IndexViewController.openWizard( 'DashletWizard', { saved_dashlet_id: id }, function() {
			$this.initDashBoard();
		} );
	}

	deleteDashlet( id, target ) {
		var $this = this;
		TAlertManager.showConfirmAlert( Global.delete_dashlet_confirm_message, null, function( result ) {
			if ( result ) {
				ProgressBar.showOverlay();
				$this.user_generic_data_api.deleteUserGenericData( id, {
					onResult: function( result ) {
						target.remove();
						$this.removeDeletedDashletsData( id );
						if ( $( $this.el ).find( '.dashboard-container' ).children().length < 1 ) {
							$this.showNoResultCover();
						} else {
							$this.saveNewOrder( function() {
								$this.updateLayout();
							} );
						}
					}
				} );
			} else {
				ProgressBar.closeOverlay();
			}
		} );
	}

	removeDeletedDashletsData( id ) {
		for ( var i = 0, ii = this.dashlet_list.length; i < ii; i++ ) {
			if ( this.dashlet_list[i].id.toString() === id ) {
				this.dashlet_list.splice( i, 1 );
				break;
			}
		}
	}

	saveScrollPosition() {
		this.current_scroll_position = this.dashboard_container.parent().scrollTop();
	}

	recoverCurrentScrollPosition() {
		if ( this.current_scroll_position > 0 ) {
			this.dashboard_container.parent().scrollTop( this.current_scroll_position );
		}
	}

}

HomeViewController.html_template = `
	<div class="html2js view home-view" id="home_view_container">
		<div class="clear-both-div"></div>
		<div class="container">
			<div class="dashboard-container">
			</div>
		</div>
		<div class="grid-bottom-border" style="display: block;"></div>
	</div>
`;
