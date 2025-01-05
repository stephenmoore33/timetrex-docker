export class NotificationViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#notification_view_container',

			is_viewing: null,
			status_id_array: null,
			type_id_array: null

		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'NotificationEditView.html';
		this.permission_id = 'notification';
		this.viewId = 'Notification';
		this.script_name = 'NotificationView';
		this.table_name_key = 'Notification';
		this.navigate_link = '';
		this.selected_payload = {};
		this.context_menu_name = $.i18n._( 'Notifications' );
		this.navigation_label = $.i18n._( 'Notification' );
		this.api = TTAPI.APINotification;

		this.is_viewing = false;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		this.initDropDownOption( 'type', 'type_id', this.api );
		this.initDropDownOption( 'status', 'status_id', this.api );
	}

	onGridDblClickRow( e ) {
		// shorten it's path as its only ever a view click
		ProgressBar.showOverlay();
		this.onViewClick();
		this.setDefaultMenu( true );
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};

		column_filter.id = true;
		column_filter.object_id = true;
		column_filter.payload_data = true;
		column_filter.status_id = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	}

	setGridCellBackGround() {
		//Error: Unable to get property 'getGridParam' of undefined or null reference
		if ( !this.grid ) {
			return;
		}

		var data = this.grid.getGridParam( 'data' );

		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.status_id == 10 ) {
				$( 'tr[id=\'' + item.id + '\'] td' ).css( 'font-weight', 'bold' );
			}
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				other: {
					label: $.i18n._( 'Other' ),
					id: this.script_name + 'other',
					sort_order: 9000
				},
				mark: {
					label: $.i18n._( 'Mark' ),
					id: this.viewId + 'mark',
					sort_order: 8000
				}
			},
			exclude: [
				'save_and_continue',
				'save_and_next',
				'save_and_new',
				'save_and_copy',
				'save',
				'copy',
				'copy_as_new',
				'edit',
				'new_add',
				'add',
				'mass_edit',
				'export_excel',
				'delete_and_next'

			],
			include: [
				'view',
				{
					label: $.i18n._( 'Jump To' ),
					id: 'navigate',
					vue_icon: 'tticon tticon-north_east_black_24dp',
					menu_align: 'right',
					permission_result: true,
					permission: 8200,
					sort_order: 8100
				},
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Mark: Read' ),
					id: 'read',
					menu_align: 'left',
					action_group: 'mark',
					permission_result: true,
					permission: null,
					sort_order: 8000
				},
				{
					label: $.i18n._( 'Mark: UnRead' ),
					id: 'unread',
					menu_align: 'left',
					action_group: 'mark',
					permission_result: true,
					permission: null,
					sort_order: 8100
				},
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id, context_btn ) {
		switch ( id ) {
			case 'close_misc':
			case 'cancel':
				this.onCancelClick();
				break;
			case 'read':
				this.onReadClick();
				break;
			case 'unread':
				this.onUnReadClick();
				break;
			case 'navigate':
				this.onNavigateClick();
				break;
		}
	}

	oncancelClick() {
		this.removeEditView();
	}

	onReadClick() {
		var notification_ids = [];
		if ( this.is_viewing && this.current_edit_record ) {
			notification_ids.push( this.current_edit_record.id );
		} else {
			notification_ids = this.getGridSelectIdArray();
		}

		if ( notification_ids.length > 0 ) {
			var $this = this;

			this.api['setNotificationStatus']( notification_ids, 20, {
				onResult: function( res ) {
					if ( $this.is_viewing ) {
						$this.removeEditView();
					}
					$this.search( false );
				}
			} );
		}
	}

	onUnReadClick() {
		var notification_ids = [];
		if ( this.is_viewing && this.current_edit_record ) {
			notification_ids.push( this.current_edit_record.id );
		} else {
			notification_ids = this.getGridSelectIdArray();
		}

		if ( notification_ids.length > 0 ) {
			var $this = this;

			this.api['setNotificationStatus']( notification_ids, 10, {
				onResult: function( res ) {
					if ( $this.is_viewing ) {
						$this.removeEditView();
					}
					$this.search( false );
				}
			} );
		}
	}

	onNavigateClick() {
		if ( this.navigate_link !== '' ) {
			// If viewing a notification the view needs to be closed for window.location to work correctly for links that open another onViewClick view.
			if ( this.is_viewing == true ) {
				this.onCancelClick();
			}

			if ( this.navigate_link === 'open_view' ) {
				for ( let i = 0; i < this.selected_payload.timetrex.event.length; i++ ) {
					if ( this.selected_payload.timetrex.event[i].type === 'open_view' || this.selected_payload.timetrex.event[i].type === 'open_view_immediate' ) {
						NotificationConsumer.openViewLinkedToNotification( this.selected_payload.timetrex.event[i] );
						break;
					}
				}
			} else {
				if ( this.selected_payload.link_target && this.selected_payload.link_target === '_blank' ) {
					window.open(
						this.navigate_link,
						'_blank'
					);
				} else {
					window.location = this.navigate_link;
				}
			}
		}
	}

	setNavigateLink() {
		this.navigate_link = '';

		//Error: Unable to get property 'getGridParam' of undefined or null reference
		if ( !this.grid ) {
			return;
		}

		var data = this.grid.getGridParam( 'data' );

		if ( !data ) {
			return false;
		}

		var notification_ids = [];

		if ( this.current_edit_record && this.current_edit_record.id ) {
			notification_ids.push( this.current_edit_record.id );
		} else {
			notification_ids = this.getGridSelectIdArray();
		}

		if ( notification_ids.length === 1 ) {
			var len = data.length;

			for ( var i = 0; i < len; i++ ) {
				var item = data[i];

				if ( item.id === notification_ids[0] ) {
					//Check if payload has an "open_view" or "open_view_immediate" event for opening and passing data to a edit view.
					if ( item.payload_data.timetrex !== undefined && item.payload_data.timetrex.event !== undefined ) {
						for ( let i = 0; i < item.payload_data.timetrex.event.length; i++ ) {
							if ( item.payload_data.timetrex.event[i].type === 'open_view' || item.payload_data.timetrex.event[i].type === 'open_view_immediate' ) {
								this.navigate_link = 'open_view';
								this.selected_payload = item.payload_data;
								return true;
							}
						}
					} else if ( item.payload_data.link !== undefined && item.payload_data.link !== '' ) {
						//Normal navigation link found.
						this.navigate_link = item.payload_data.link;
						this.selected_payload = item.payload_data;
						return true;
					}
				}
			}
		}
		this.navigate_link = '';
		this.selected_payload = {};
		return false;
	}

	initEditView() {
		if ( this.current_edit_record && this.current_edit_record.status_id == 10 ) {
			var $this = this;

			//Set current notification being viewed as read.
			$this.current_edit_record.status_id = 20;
			this.api['setNotificationStatus']( [this.current_edit_record.id], 20, {
				onResult: function( res ) {
					$this.search( false );
				}
			} );
		}

		super.initEditView();
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'read':
				this.setDefaultMenuReadIcon( context_btn, grid_selected_length );
				break;
			case 'unread':
				this.setDefaultMenuUnReadIcon( context_btn, grid_selected_length );
				break;
			case 'navigate':
				this.setDefaultMenuNavigateIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case'navigate':
				this.setDefaultMenuNavigateIcon( context_btn );
				break;
			case'read':
				this.setEditMenuReadIcon( context_btn );
				break;
			case'unread':
				this.setEditMenuUnReadIcon( context_btn );
				break;
		}
	}

	setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length, pId ) {
		if ( this.is_viewing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteAndNextIcon( context_btn, grid_selected_length, pId ) {
		if ( this.is_viewing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length >= 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn, grid_selected_length, pId ) {

		if ( this.is_viewing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuViewIcon( context_btn, grid_selected_length, pId ) {

		if ( this.is_viewing == false && grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuCancelIcon( context_btn, pId ) {
		if ( this.is_viewing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuReadIcon( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length >= 1 ) {
			//Check if any notifications are unread.
			var selected_items = this.getSelectedItems();
			for ( var i = 0; i < selected_items.length; i++ ) {
				if ( selected_items[i] !== null && selected_items[i].status_id == 10 ) {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
					return;
				}
			}
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuUnReadIcon( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length >= 1 ) {
			//Check if any notifications are read.
			var selected_items = this.getSelectedItems();
			for ( var i = 0; i < selected_items.length; i++ ) {
				if ( selected_items[i] !== null && selected_items[i].status_id == 20 ) {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
					return;
				}
			}
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuNavigateIcon( context_btn, grid_selected_length, pId ) {
		if ( this.is_viewing == true || grid_selected_length === 1 ) {
			// check a link is set in the payload data
			if ( this.setNavigateLink() == true ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuReadIcon( context_btn ) {
		//Because the notification view closes when clicking "Mark Read / Unread" this icon will always be disabled as the message is always read when viewed.
		//But may be needed in the future.
		if ( this.current_edit_record && this.current_edit_record.status_id == 10 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuUnReadIcon( context_btn ) {
		if ( this.current_edit_record && this.current_edit_record.status_id == 20 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_notification': { 'label': $.i18n._( 'Notification' ) }
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APINotification,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_notification',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_notification = $( '#tab_notification' );

		var tab_notification_column1 = tab_notification.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_notification_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_notification_column1, '' );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'created_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_notification_column1, '' );

		// Title
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'title_long' } );
		this.addEditFieldToColumn( $.i18n._( 'Title' ), form_item_input, tab_notification_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'body_long_text' } );
		form_item_input.off( 'click' ).on( "click", function() {
			$this.onNavigateClick();
		});
		this.addEditFieldToColumn( $.i18n._( 'Message' ), form_item_input, tab_notification_column1, '' );
	}

	buildSearchFields() {
		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Title' ),
				in_column: 1,
				field: 'title_long',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} )

		];
	}

	// Search is triggered when a notification is marked as read, unread or deleted.
	// Search also pulls in new notifications if any have been created which makes it a good time to update the notification bell.
	search( set_default_menu, page_action, page_number, callBack ) {
		super.search( set_default_menu, page_action, page_number, callBack );
		if ( Global.UNIT_TEST_MODE == false ) {
			NotificationConsumer.getUnreadNotifications();
		}
	}

}