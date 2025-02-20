export class AccrualPolicyUserModifierViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#accrual_policy_user_modifier_view_container', //Must set el here and can only set string, so events can work

			user_api: null,

			parent_view: null,

			result_details: null
		} );

		super( options );
	}

	init( options ) {
		this.parent_view = this.parent_view_controller.viewId === 'Employee' ? 'employee' : 'accrual_policy'; //Previously was passed in the <script> tag of edit view html.
		//this._super('initialize', options );
		if ( this.parent_view === 'employee' ) {
			this.context_menu_name = $.i18n._( 'Accruals' );
			this.navigation_label = $.i18n._( 'Accrual' );
		} else if ( this.parent_view === 'accrual_policy' ) {
			this.context_menu_name = $.i18n._( 'Employee Settings' );
			this.navigation_label = $.i18n._( 'Employee Accrual Modifier' );
		}
		this.edit_view_tpl = 'AccrualPolicyUserModifierEditView.html';
		this.permission_id = 'accrual_policy';
		this.script_name = 'AccrualPolicyUserModifierView';
		this.viewId = 'AccrualPolicyUserModifier';
		this.table_name_key = 'accrual_policy_user_modifier';

		this.api = TTAPI.APIAccrualPolicyUserModifier;
		this.user_api = TTAPI.APIUser;

		this.render();
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['add', 'copy'],
			include: ['default']
		};

		return context_menu_model;
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {
			filter['table_name_object_id'] = {
				'accrual_policy_user_modifier': [this.parent_edit_record.accrual_policy_id]
			};

			return filter;
		};

		return true;
	}

	onAddClick() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		$this.openEditView();

		var user_id;

		if ( $this.sub_view_mode && $this.parent_key ) {
			switch ( $this.parent_key ) {
				case 'user_id':
					user_id = $this.parent_value;
					break;
				case 'accrual_policy_id':
					user_id = false;
					break;
			}
		} else {
			user_id = false;
		}

		$this.api['get' + $this.api.key_name + 'DefaultData']( user_id, {
			onResult: function( result ) {
				$this.onAddResult( result );

			}
		} );
	}

	setSelectLayout( column_start_from ) {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		var column_info_array = [];

		var column_info;

		if ( !this.select_layout ) { //Set to default layout if no layout at all
			this.select_layout = { id: '' };
			this.select_layout.data = { filter_data: {}, filter_sort: {} };
			this.select_layout.data.display_columns = this.default_display_columns;
		}
		var layout_data = this.select_layout.data;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( !this.sub_view_mode ) {

			//Set Display Column in layout panel
			this.column_selector.setSelectGridData( display_columns );

			//Set Sort by awesomebox in layout panel
			this.sort_by_selector.setSourceData( this.buildSortSelectorUnSelectColumns( display_columns ) );
			this.sort_by_selector.setValue( this.buildSortBySelectColumns() );

			//Set Previoous Saved layout combobox in layout panel
			var layouts_array = this.search_panel.getLayoutsArray();

			this.setPreviousSavedSearchSourcesAndValue( layouts_array );

		}

		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

		if ( !this.grid ) {
			//		for ( i = start_from; i < len; i++ ) {
			//			var view_column_data = display_columns[i];
			//
			//			var column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 100, sortable: false, title: false};
			//			column_info_array.push( column_info );
			//		}

			if ( this.parent_view === 'accrual_policy' ) {
				column_info = {
					name: 'full_name',
					index: 'full_name',
					label: $.i18n._( 'Employee' ),
					width: 50,
					sortable: false,
					title: false
				};
				column_info_array.push( column_info );

			} else if ( this.parent_view === 'employee' ) {
				column_info = {
					name: 'accrual_policy',
					index: 'accrual_policy',
					label: $.i18n._( 'Accrual Policy' ),
					width: 50,
					sortable: false,
					title: false
				};
				column_info_array.push( column_info );
			}

			column_info = {
				name: 'length_of_service_date',
				index: 'length_of_service_date',
				label: $.i18n._( 'Length of Service Date' ),
				width: 80,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'length_of_service_modifier',
				index: 'length_of_service_modifier',
				label: $.i18n._( 'Length of Service Modifier' ),
				width: 90,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'accrual_rate_modifier',
				index: 'accrual_rate_modifier',
				label: $.i18n._( 'Accrual Rate Modifier' ),
				width: 80,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'annual_maximum_time_modifier',
				index: 'annual_maximum_time_modifier',
				label: $.i18n._( 'Annual Accrual Maximum Modifier' ),
				width: 110,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'maximum_time_modifier',
				index: 'maximum_time_modifier',
				label: $.i18n._( 'Accrual Maximum Balance Modifier' ),
				width: 110,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			//		column_info = {name: 'minimum_time_modifier', index: 'minimum_time_modifier', label: 'Accrual Total Minimum Modifier', width:110, sortable: false, title: false};
			//		column_info_array.push( column_info );

			column_info = {
				name: 'rollover_time_modifier',
				index: 'rollover_time_modifier',
				label: $.i18n._( 'Annual Maximum Rollover Modifier' ),
				width: 110,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );

			var container = 'body';

			if ( $this.sub_view_mode ) {
				if ( $( '#tab_accruals:visible' ).length > 0 ) {
					container = '#tab_accruals';
				} else {
					container = '#tab_employee_settings';
				}
			}

			var grid_setup = this.getGridSetup();
			this.grid = new TTGrid( this.ui_id + '_grid', grid_setup, column_info_array );
		}
		// else {
		// 	grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		// }

		$this.setGridSize();

		//Add widget on UI and bind events. Next set data in it in search result.
		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.paging_widget.parent().length > 0 ) {
				this.paging_widget.remove();
			}

			this.paging_widget.css( 'width', this.grid.width() );
			this.grid.grid.append( this.paging_widget );

			this.paging_widget.click( $.proxy( this.onPaging, this ) );

		} else {
			if ( this.paging_widget.parent().length < 1 ) {
				$( this.el ).find( '.total-number-div' ).append( this.paging_widget );
				$( this.el ).find( '.bottom-div' ).append( this.paging_widget_2 );

				this.paging_widget.bind( 'paging', $.proxy( this.onPaging2, this ) );
				this.paging_widget_2.bind( 'paging', $.proxy( this.onPaging2, this ) );
			}

		}

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		if ( !this.sub_view_mode ) {
			this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.
		}

		this.showGridBorders();
	}

	setGridSetup() {
		var $this = this;
		return {
			height: 200,
			onResizeGrid: true,
			sub_grid_mode: this.sub_view_mode,
			container_selector: container,
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
			}
		};
	}

	getFilterColumnsFromDisplayColumns() {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.grid.getGridParam( 'colModel' );
		}
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.accrual_policy_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.type_id = true;

		//Fixed possible exception -- Error: Unable to get property 'length' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 5031
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	}

	doEditAPICall( filter, api_args, _callback ) {
		var record_id = this.getCurrentSelectedRecord();
		if ( TTUUID.isUUID( record_id ) && record_id != TTUUID.not_exist_id && record_id != TTUUID.zero_id ) {
			return super.doEditAPICall( filter, api_args, _callback );
		} else {
			var result_data = this.getRecordFromGridById( record_id );

			if ( result_data && result_data.id ) {
				result_data.id = '';
			}
			return this.handleEditAPICallbackResult( result_data );
		}
	}

	doViewAPICall( filter, api_args, _callback ) {
		var record_id = this.getCurrentSelectedRecord();
		if ( TTUUID.isUUID( record_id ) && record_id != TTUUID.not_exist_id && record_id != TTUUID.zero_id ) {
			return super.doViewAPICall( filter, api_args, _callback );
		} else {
			var result_data = this.getRecordFromGridById( record_id );

			if ( result_data && result_data.id ) {
				result_data.id = '';
			}
			return this.handleViewAPICallbackResult( result_data );
		}
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			//Setting is_add false too early can cause determineContextMenuMountAttributes() to have unexpected side effects. However not setting it here might have other side effects.
			//$this.is_add = false;
			var result_data = result.getResult();

			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.refresh_id = result_data;
				}

				if ( Global.isSet( $this.refresh_id ) === false ) {
					$this.result_details = result.getDetails();
				}

				$this.search();
			}

			$this.onSaveDone( result );

			$this.removeEditView();

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	search( set_default_menu, page_action, page_number, callBack ) {
		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {

				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}

			}

		} else {
			filter.filter_page = 1;
		}

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}

		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		if ( TTUUID.isUUID( this.refresh_id ) ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {

			if ( Global.isSet( this.result_details ) && this.result_details.length > 0 ) {
				this.result_details = $.map( this.result_details, function( n ) {
					return n === true ? 0 : n;
				} );
				this.last_select_ids = Global.concatArraysUniqueWithSort( this.result_details, this.getGridSelectIdArray() );
			} else {
				this.last_select_ids = this.getGridSelectIdArray();
			}

		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				var len;

				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );

					len = result_data.length;
				}
				if ( TTUUID.isUUID( $this.refresh_id ) ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.getData();
					len = grid_source_data.length;

					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}

					var found = false;
					var new_record = result_data[0];

					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
						var new_grid_source_data = [];

						for ( var i = 0; i < len; i++ ) {
							var record = grid_source_data[i];

							//Fixed === issue. The id set by jQGrid is string type.
							// if ( !isNaN( parseInt( record.id ) ) ) {
							// 	record.id = parseInt( record.id );
							// }

							if ( record.id == new_record.id ) {
								$this.grid.grid.setRowData( new_record.id, new_record );
								found = true;
								break;
							}

							if ( record.id < 0 && record.user_id == new_record.user_id ) {

							} else {
								new_grid_source_data.push( record );
							}
						}

						if ( !found ) {
							//Refresh the search because this is a special case where a new record is added, but the UI sees an edit of the existing join row.
							// $this.grid.clearGridData();
							// $this.grid.setGridParam( {data: new_grid_source_data.concat( new_record )} );
							$this.search();

							// if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGrid ) ) {
							// 	len = Global.isSet( len ) ? len : 0;
							// 	$this.resizeSubGrid( len + 1 );
							// }

							//s$this.grid.grid.trigger( 'reloadGrid' );
							$this.reSelectLastSelectItems();
							$this.highLightGridRowById( new_record.id );
						}
					}

				} else {
					//Set Page data to widget, next show display info when setDefault Menu
					$this.pager_data = result.getPagerData();

					//CLick to show more mode no need this step
					if ( LocalCacheData.paging_type !== 0 ) {
						$this.paging_widget.setPagerData( $this.pager_data );
						$this.paging_widget_2.setPagerData( $this.pager_data );
					}

					if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
						var current_data = $this.grid.getData();
						result_data = current_data.concat( result_data );
					}

					if ( $this.grid ) {
						$this.grid.setData( result_data );

						// if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGrid ) ) {
						// 	$this.resizeSubGrid( len );
						// }

						$this.reSelectLastSelectItems();
					}
				}

				$this.result_details = null;

				$this.setGridCellBackGround(); //Set cell background for some views

				ProgressBar.closeOverlay(); //Add this in initData

				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				$this.searchDone();

			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_employee_accrual_modifier': { 'label': this.parent_view === 'employee' ? $.i18n._( 'Accrual' ) : $.i18n._( 'Employee Accrual Modifier' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAccrualPolicyUserModifier,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_wage',
			show_search_inputs: true,
			navigation_mode: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_employee_accrual_modifier = this.edit_view_tab.find( '#tab_employee_accrual_modifier' );

		var tab_employee_accrual_modifier_column1 = tab_employee_accrual_modifier.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_employee_accrual_modifier_column1 );

		var form_item_input;

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'

		} );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// Accrual Policy

		var default_args = {};
		default_args.filter_data = {};
		default_args.filter_data.type_id = [20, 30];

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAccrualPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_accrual',
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy_id'

		} );
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Policy' ), form_item_input, tab_employee_accrual_modifier_column1, '' );

		// Length of Service Date

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'length_of_service_date', width: 120 } );

		this.addEditFieldToColumn( $.i18n._( 'Length of Service Date' ), form_item_input, tab_employee_accrual_modifier_column1, '', null );

		//Modifier Rates
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Modifier Rates' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_employee_accrual_modifier_column1, '', null, true, false, 'separated_1' );

		// Length of Service Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'length_of_service_modifier', width: 40 } );

		this.addEditFieldToColumn( $.i18n._( 'Length of Service' ), form_item_input, tab_employee_accrual_modifier_column1 );

		// Accrual Rate Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'accrual_rate_modifier', width: 40 } );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_employee_accrual_modifier_column1 );

		//Annual Accrual Maximum
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'annual_maximum_time_modifier', width: 40 } );

		this.addEditFieldToColumn( $.i18n._( 'Annual Accrual Maximum' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// "Accrual Maximum Balance Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_time_modifier', width: 40 } );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Maximum Balance' ), form_item_input, tab_employee_accrual_modifier_column1 );

		//Annual Maximum Rollover Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'rollover_time_modifier', width: 40 } );

		this.addEditFieldToColumn( $.i18n._( 'Annual Maximum Rollover' ), form_item_input, tab_employee_accrual_modifier_column1 );

		//Modifier Rates
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Current Milestone' ) + ': ' } );
		this.addEditFieldToColumn( null, form_item_input, tab_employee_accrual_modifier_column1, '', null, true, false, 'separated_2' );

		// Accrual Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'accrual_rate' } );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// "Accrual Maximum Balance Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'maximum_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Maximum Balance Time' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// Accrual Maximum Rollover
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'rollover_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Maximum Rollover' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( this.current_edit_record.type_id == 30 ) {
			this.attachElement( 'annual_maximum_time_modifier' );
		} else {
			this.detachElement( 'annual_maximum_time_modifier' );
		}

		this.setAccrualPolicyDataFromUserModifier();
		this.getAccrualPolicyDataFromUserModifier();
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'user_id':
				if ( this.is_add ) {
					this.api['get' + this.api.key_name + 'DefaultData']( c_value, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							if ( $this.sub_view_mode && $this.parent_key === 'accrual_policy_id' ) {
								result_data[$this.parent_key] = $this.parent_value;
							}

							$this.current_edit_record = result_data;
							$this.setCurrentEditRecordData();
							$this.validate();

						}
					} );
				} else {
					this.current_edit_record[key] = c_value;
					this.validate();
					this.getAccrualPolicyDataFromUserModifier();
				}
				break;
			default :
				this.current_edit_record[key] = c_value;
				this.validate();
				this.getAccrualPolicyDataFromUserModifier();
				break;
		}
	}

	getAccrualPolicyDataFromUserModifier() {
		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );
		this.api['getAccrualPolicyDataFromUserModifier']( record, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.setAccrualPolicyDataFromUserModifier( result_data );
			}
		} );
	}

	setAccrualPolicyDataFromUserModifier( result_data ) {
		var $this = this;
		if ( Global.isSet( result_data ) && !Global.isFalseOrNull( result_data ) ) {

			var accrual_rate = 0;
			if ( result_data && result_data.accrual_policy_type_id && result_data.accrual_policy_type_id == 20 ) { //Calendar
				accrual_rate = Global.getTimeUnit( result_data.accrual_rate )
			} else { //Hourly
				accrual_rate = result_data.accrual_rate;
			}

			if ( !$this.edit_view_form_item_dic['separated_2'] ) {
				//Can trigger an error if this runs after the view has closed.
				//Uncaught TypeError: Cannot read properties of undefined (reading 'find')
				return;
			}

			$this.edit_view_form_item_dic['separated_2'].find( '.label' ).text( $.i18n._( 'Current Milestone' ) + ': ' + result_data.milestone_number );
			$this.edit_view_ui_dic['accrual_rate'].setValue( accrual_rate );
			$this.edit_view_ui_dic['maximum_time'].setValue( Global.getTimeUnit( result_data.maximum_time ) );
			$this.edit_view_ui_dic['rollover_time'].setValue( Global.getTimeUnit( result_data.rollover_time ) );

			$this.attachElement( 'separated_2' );
			$this.attachElement( 'accrual_rate' );
			$this.attachElement( 'maximum_time' );
			$this.attachElement( 'rollover_time' );

		} else {

			if ( !$this.is_edit ) {

				$this.detachElement( 'separated_2' );
				$this.detachElement( 'accrual_rate' );
				$this.detachElement( 'maximum_time' );
				$this.detachElement( 'rollover_time' );
			}

		}
	}

	removeEditView() {
		super.removeEditView();

		if ( this.parent_view === 'accrual_policy' ) {

			this.context_menu_name = $.i18n._( 'Employee Settings' );
			$( '.ribbonTabLabel' ).find( 'a[ref=' + this.viewId + 'ContextMenu' + ']' ).text( $.i18n._( 'Employee Settings' ) );
		}
	}

	getAPIFilters() {
		var filter = super.getAPIFilters();

		if ( this.sub_view_mode && this.parent_key ) {
			filter.filter_data[this.parent_key] = this.parent_value;
		}

		return filter;
	}

	onMassEditClick() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		if ( this.sub_view_mode && this.parent_key ) {
			filter.filter_data[this.parent_key] = this.parent_value;
		}

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();
								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}
								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );
	}

	validate() {
		var $this = this;

		var record;

		if ( this.is_mass_editing ) {

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {

				var check_fields = {};
				if ( value < 0 ) {
					check_fields = $this.getRecordFromGridById( value );
				} else {
					check_fields.id = value;
				}

				for ( var key in $this.edit_view_ui_dic ) {

					if ( !$this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						continue;
					}

					var widget = $this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							switch ( key ) {
								case 'user_id':
//									if ( value > 0 ) {
//										check_fields[key] = $this.current_edit_record[key];
//									}
									break;
								default:
									check_fields[key] = widget.getValue();
									break;
							}
						}
					}
				}

				var common_record = Global.clone( check_fields );
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );

//			for ( var key in this.edit_view_ui_dic ) {
//
//				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
//					continue;
//				}
//
//				var widget = this.edit_view_ui_dic[key];
//
//				if ( Global.isSet( widget.isChecked ) ) {
//					if ( widget.isChecked() && widget.getEnabled() ) {
//						record[key] = widget.getValue();
//					}
//
//				}
//			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	}

	onSaveClick( ignoreWarning ) {
		var $this = this;
		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {

				var check_fields = {};
				if ( value < 0 ) {
					check_fields = $this.getRecordFromGridById( value );
				} else {
					check_fields.id = value;
				}

				for ( var key in $this.edit_view_ui_dic ) {
					var widget = $this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() ) {
							switch ( key ) {
								case 'user_id':
//									if ( value > 0 ) {
//										check_fields[key] = $this.current_edit_record[key];
//									}
									break;
								default:
									check_fields[key] = $this.current_edit_record[key];
									break;
							}
						}
					}
				}

				var common_record = Global.clone( check_fields );
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {

				$this.onSaveResult( result );

			}
		} );
	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length, pId ) {
		if ( ( !this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId ) ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		var grid_selected_id_array = this.getGridSelectIdArray();
		var enabled = false;

		for ( var i in grid_selected_id_array ) {
			if ( TTUUID.isUUID( grid_selected_id_array[i] ) ) {
				enabled = true;
			}
		}

		if ( !enabled ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'AccrualView', 'init' );
	}

	setEditViewWidgetsMode() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			if ( this.is_viewing || ( this.parent_view === 'employee' && ( key === 'accrual_policy_id' || key === 'user_id' ) ) ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}
		}
	}

}

AccrualPolicyUserModifierViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'AccrualPolicyUserModifier', 'SubAccrualPolicyUserModifierView.html', function( result ) {
		var args = {};
		if ( Global.isSet( beforeViewLoadedFun ) ) {
			var template_data = beforeViewLoadedFun( result );
			var template = template_data.template;
			args = template_data.args;
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_accrual_policy_user_modifier_view_controller );
			}

		}

	} );

};