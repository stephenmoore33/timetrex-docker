export class SavedReportViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#saved_report_view_container',

			//Issue #1187 - Many of these variables are needed when this view is rendered as a sub view
			//in that case, init() will not be run so those variables need to be defined at load time instead.

			sub_report_schedule_view_controller: null,
			edit_view_tpl: 'SavedReportEditView.html',
			permission_id: 'report',
			viewId: 'SavedReport',
			script_name: 'UserReportDataView',
			table_name_key: 'user_report_data',
			context_menu_name: $.i18n._( 'Reports' ),
			navigation_label: $.i18n._( 'Saved Report' ),

			api: TTAPI.APIUserReportData
		} );

		super( options );
	}

	init( options ) {

		this.render();
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );

			//call init data in parent view, don't call initData
		} else {
			this.buildContextMenu();
			if ( !this.sub_view_mode ) {
				this.initData();
			}
		}
	}

	onDeleteResult( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();

		if ( result && result.isValid() ) {
			$this.search();
			$this.onDeleteDone( result );

			if ( $this.edit_view ) {
				$this.removeEditView();
			} else {
				$this.setCurrentEditViewState( '' );
			}

			if ( this.sub_view_mode && this.parent_view_controller ) {
				this.parent_view_controller.onSavedReportDelete();
			}

		} else {
			$this.revertEditViewState();
			TAlertManager.showErrorAlert( result );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'share_report':

				if ( Global.getProductEdition() >= 15 ) {
					var default_data = [];
					if ( this.edit_view && this.current_edit_record.id ) {
						default_data.push( this.current_edit_record.id );
					} else if ( !this.edit_view ) {
						default_data = this.getGridSelectIdArray();
					}
					IndexViewController.openWizard( 'ShareReportWizard', default_data );
				} else {
					TAlertManager.showAlert( Global.getUpgradeMessage() );
				}

				break;
		}
	}

	onGridDblClickRow() {
		ProgressBar.showOverlay();

		if ( this.sub_view_mode ) {
			this.onEditClick();
		} else {
			this.onViewClick();
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				share: {
					label: $.i18n._( 'Share' ),
					id: this.viewId + 'Share'
				}
			},
			exclude: ['default'],
			include: [
				'edit',
				'delete_icon',
				'delete_and_next',
				'save',
				'save_and_continue',
				'save_and_next',
				'cancel',
				{
					label: $.i18n._( 'Share Report' ),
					id: 'share_report',
					menu_align: 'right',
					vue_icon: 'tticon tticon-share_black_24dp',
				}
			]
		};

		if ( !this.sub_view_mode ) {
			context_menu_model.include.unshift( {
				label: $.i18n._( 'Report' ),
				id: 'view',
				group: 'editor',
				vue_icon: 'tticon tticon-visibility_black_24dp',
			} );
		}

		return context_menu_model;
	}

	removeEditView() {

		super.removeEditView();
		this.sub_report_schedule_view_controller = null;
	}

	getGridSetup() {
		var $this = this;

		var grid_setup = {
			container_selector: this.sub_view_mode ? '#tab_saved_reports' : '.view', //tab4 = Saved Report tab.
			sub_grid_mode: this.sub_view_mode,
			onSelectRow: function() {
				$this.onGridSelectRow();
			},
			onCellSelect: function() {
				$this.onGridSelectRow();
			},
			onSelectAll: function() {
				$this.onGridSelectAll();
			},
			ondblClickRow: function( e ) {
				$this.onGridDblClickRow( e );
			},
			onRightClickRow: function( rowId ) {
				var id_array = $this.getGridSelectIdArray();
				if ( id_array.indexOf( rowId ) < 0 ) {
					$this.grid.grid.resetSelection();
					$this.grid.grid.setSelection( rowId );
					$this.onGridSelectRow();
				}
			},
		};

		//Only use custom grid sizing when in sub_view_mode, since we need to use the BaseViewController grid sizing otherwise.
		if ( this.sub_view_mode ) {
			grid_setup.setGridSize = function() {
				if ( $this.sub_view_mode ) {
					$this.baseViewSubTabGridResize( '#tab_saved_reports' );
				}
			};

			grid_setup.onResizeGrid = function() {
				if ( $this.sub_view_mode ) {
					$this.baseViewSubTabGridResize( '#tab_saved_reports' );
				}
			};
		}

		return grid_setup;
	}

	onViewClick( edit_record, noRefreshUI ) {
		var grid_selected_id_array = this.getGridSelectIdArray();
		var id = grid_selected_id_array[0];

		var record = this.getRecordFromGridById( id );
		if ( record && record.script ) {
			var report_name = record.script; //Must use 'script' instead of 'script_name' so it doesn't change with different languages.

			LocalCacheData.current_doing_context_action = 'view';
			LocalCacheData.default_edit_id_for_next_open_edit_view = id;

			switch ( report_name ) {
				case 'UserExpenseReport':
					report_name = 'ExpenseSummaryReport';
					break;
				case 'ExceptionReport':
					report_name = 'ExceptionSummaryReport';
					break;
				case 'JobDetailReport':
					report_name = 'JobAnalysisReport';
					break;
				case 'AffordableCareReport':
					if ( Global.getProductEdition() == 10 ) {
						TAlertManager.showAlert( Global.getUpgradeMessage() );
						report_name = null;
					}
					break;
				// Having a default that errors out makes it so we have to add every new report here. Instead, we'll just error out if the report isn't defined, which should be far fewer cases.
				// default:
				// 	ProgressBar.closeOverlay();
				// 	Debug.Text( 'ERROR: Saved Report name not defined: ' + report_name, 'SavedReportViewController.js', '', 'onViewClick', 10 );
				// 	report_name = null;
				// 	break;
			}

			if ( Global.isSet( report_name ) && report_name ) {
				IndexViewController.openReport( this, report_name );
			}
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_report': { 'label': $.i18n._( 'Report' ) },
			'tab_schedule': {
				'label': $.i18n._( 'Schedule' ),
				'init_callback': 'initSubReportScheduleView',
				'show_permission_div': true
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				api_class: TTAPI.APIUserReportData,
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: 'global_user_report_data',
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();

		}

		//Tab 0 start

		var tab_report = this.edit_view_tab.find( '#tab_report' );

		var tab_report_column1 = tab_report.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_report_column1 );

		// Name

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_report_column1, '' );
		form_item_input.parent().width( '45%' );

		// Default

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input.TCheckbox( { field: 'is_default' } );
		this.addEditFieldToColumn( $.i18n._( 'Default' ), form_item_input, tab_report_column1 );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_report_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Description' ),
				field: 'description',
				basic_search: true,
				adv_search: false,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	initSubReportScheduleView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( Global.getProductEdition() >= 15 ) {
			if ( this.sub_report_schedule_view_controller ) {
				this.sub_report_schedule_view_controller.buildContextMenu( true );
				this.sub_report_schedule_view_controller.setDefaultMenu();
				$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
				$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
				$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
				return;
			}

			Global.loadViewSource( 'ReportSchedule', 'ReportScheduleViewController.js', function() {
				var tab = $this.edit_view_tab.find( '#tab_schedule' );

				var firstColumn = tab.find( '.first-column-sub-view' );

				TTPromise.add( 'initSubReportScheduleView', 'init' );
				TTPromise.wait( 'initSubReportScheduleView', 'init', function() {
					firstColumn.css( 'opacity', '1' );
				} );

				firstColumn.css( 'opacity', '0' ); //Hide the grid while its loading/sizing.

				Global.trackView( 'Sub' + 'ReportSchedule' + 'View' );
				ReportScheduleViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
			} );
		} else {
			this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
			this.edit_view.find( '.save-and-continue-button-div' ).css( 'display', 'none' );
		}

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_report_schedule_view_controller = subViewController;
			$this.sub_report_schedule_view_controller.parent_key = 'user_report_data_id';
			$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_report_schedule_view_controller.parent_view_controller = $this;
			$this.sub_report_schedule_view_controller.sub_view_mode = true;

			$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
		}
	}

	uniformVariable( records ) {

		// Remove in next commit, related to #2698 fix, no longer needed but semi-big refactor to remove
		// if ( records.hasOwnProperty( 'data' ) && records.data.hasOwnProperty( 'config' ) && records.data.config.hasOwnProperty( 'filter' ) ) {
		// 	records.data.config.filter_ = records.data.config.filter;
		// }

		return records;
	}

	onSaveClick( ignoreWarning, force_no_confirm = false ) {
		//Issue #3454 - Saving a report will warn about no changed data which can be confusing for users. This is because saving a report opens a new edit view.
		//Becuase of this on save we do force_no_confirm = true to avoid the warning. This does not affect the warning message when closing the edit view with pending unsaved changes.
		super.onSaveClick( ignoreWarning, true );
	}

	onSaveDone( result ) {
		//onSaveDoneCallback is set in Report controller
		if ( this.parent_view_controller && this.parent_view_controller.onSaveDoneCallback ) {
			this.parent_view_controller.onSaveDoneCallback( result, this.current_edit_record );
		}
	}

	onSaveAndContinueDone( result ) {
		this.onSaveDone( result );
	}

	onSaveAndNextDone( result ) {
		this.onSaveDone( result );
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.id = true;
		column_filter.is_owner = true;
		column_filter.is_child = true;
		column_filter.script = true; //Include script column so onViewClick() knows which view to open for saved reports.

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

	onAddClick( reportData ) {

		ProgressBar.closeOverlay();
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		$this.openEditView();
		$this.current_edit_record = reportData;
		$this.initEditView();
	}

	searchDone() {
		$( 'window' ).trigger( 'resize' );
		if ( this.sub_view_mode ) {
			TTPromise.resolve( 'SubSavedReportView', 'init' );
		}
		super.searchDone();
	}
}

SavedReportViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'SavedReport', 'SubSavedReportView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( window.sub_saved_report_view_controller );
				} );
			}

		}

	} );

};
