export class LogViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#log_view_container',
			tables: {
				'product': ['product', 'product_price'],
				'user_contact': ['user_contact'],
				'users': ['users', 'user_preference', 'user_wage', 'authentication'],
				'user_wage': ['user_wage'],
				'user_title': ['user_title'],
				'user_preference': ['user_preference'],
				'user_preference_notification': ['user_preference_notification'],
				'ui_kit': ['ui_kit'],
				'ui_kit_child': ['ui_kit_child'],
				'bank_account': ['bank_account'],
				'user_default': ['user_default'],
				'user_group': ['user_group'],
				'company': ['company'],
				'pay_period_schedule': ['pay_period_schedule', 'pay_period', 'pay_period_schedule_user'],
				'pay_period': ['pay_period'],
				'branch': ['branch'],
				'department': ['department'],
				'hierarchy_control': ['hierarchy_control', 'hierarchy_object_type', 'hierarchy_user', 'hierarchy_level'],
				'wage_group': ['wage_group'],
				'ethnic_group': ['ethnic_group'],
				'currency': ['currency'],
				'currency_rate': ['currency_rate'],
				'permission_control': ['permission_control', 'permission_user'],
				'custom_field': ['custom_field'],
				'station': ['station', 'station_user_group', 'station_branch', 'station_department', 'station_include_user', 'station_exclude_user'],
				'pay_stub_amendment': ['pay_stub_amendment'],
				'recurring_ps_amendment': ['recurring_ps_amendment', 'recurring_ps_amendment_user'],
				'pay_stub_entry_account': ['pay_stub_entry_account'],
				'company_deduction': ['company_deduction', 'user_deduction', 'company_deduction_pay_stub_entry_account'],
				'user_expense': ['user_expense'],
				'round_interval_policy': ['round_interval_policy'],
				'meal_policy': ['meal_policy'],
				'break_policy': ['break_policy'],
				'over_time_policy': ['over_time_policy'],
				'absence_policy': ['absence_policy'],
				'recurring_holiday': ['recurring_holiday'],
				'holiday_policy': ['holiday_policy', 'holiday_policy_recurring_holiday'],
				'holidays': ['holidays'],
				'premium_policy': ['premium_policy'],
				'policy_group': ['policy_group', 'policy_group_user'],
				'document': ['document', 'document_revision'],
				'document_group': ['document_group'],
				'document_revision': ['document_revision'],
				'schedule_policy': ['schedule_policy'],
				'accrual_policy': ['accrual_policy', 'accrual_policy_milestone', 'accrual_policy_user_modifier'],
				'client': ['client', 'client_contact', 'client_payment'],
				'report_custom_column': ['report_custom_column'],
				'client_contact': ['client_contact'],
				'client_payment': ['client_payment'],
				'invoice_transaction': ['invoice_transaction'],
				'invoice': ['invoice'],
				'job': ['job', 'job_exclude_job_item', 'job_exclude_user', 'job_include_job_item', 'job_include_user', 'job_job_item_group', 'job_user_branch', 'job_user_group', 'job_user_department'],
				'client_group': ['client_group'],
				'product_group': ['product_group'],
				'job_item': ['job_item'],
				'job_group': ['job_group'],
				'job_item_group': ['job_item_group'],
				'report_schedule': ['report_schedule'],
				'accrual_policy_account': ['accrual_policy_account'],
				'accrual': ['accrual'],
				'accrual_balance': ['accrual_balance'],
				'schedule': ['schedule'],
				'recurring_schedule_control': ['recurring_schedule_control'], //, 'recurring_schedule_user'
				'recurring_schedule_template_control': ['recurring_schedule_template_control', 'recurring_schedule_template'],
				'punch': ['punch', 'punch_control'],
				'kpi': ['kpi'],
				'kpi_group': ['kpi_group'],
				'qualification': ['qualification'],
				'qualification_group': ['qualification_group'],
				'user_skill': ['user_skill'],
				'user_education': ['user_education'],
				'user_membership': ['user_membership'],
				'user_license': ['user_license'],
				'user_language': ['user_language'],
				'job_vacancy': ['job_vacancy'],
				'job_application': ['job_application'],
				'job_applicant': ['job_applicant'],
				'invoice_district': ['invoice_district'],
				'job_applicant_employment': ['job_applicant_employment'],
				'job_applicant_reference': ['job_applicant_reference'],
				'job_applicant_location': ['job_applicant_location'],
				'job_applicant_skill': ['job_applicant_skill'],
				'job_applicant_education': ['job_applicant_education'],
				'job_applicant_license': ['job_applicant_license'],
				'job_applicant_membership': ['job_applicant_membership'],
				'job_applicant_language': ['job_applicant_language'],
				'tax_policy': ['tax_policy'],
				'area_policy': ['area_policy'],
				'shipping_policy': ['shipping_policy'],
				'payment_gateway': ['payment_gateway'],
				'request': ['request'],
				'exception_policy_control': ['exception_policy_control', 'exception_policy'],
				'user_review_control': ['user_review_control', 'user_review'],
				'roe': ['roe'],
				'expense_policy': ['expense_policy'],
				'user_report_data': ['user_report_data'],
				'regular_time_policy': ['regular_time_policy'],
				'pay_code': ['pay_code'],
				'pay_formula_policy': ['pay_formula_policy'],
				'contributing_pay_code_policy': ['contributing_pay_code_policy'],
				'contributing_shift_policy': ['contributing_shift_policy'],
				'accrual_policy_user_modifier': ['accrual_policy_user_modifier'],
				'job_item_amendment': ['job_item_amendment'],
				'user_date_total': ['user_date_total'],
				'pay_stub': ['pay_stub', 'pay_stub_entry'],
				'legal_entity': ['legal_entity'],
				'payroll_remittance_agency': ['payroll_remittance_agency'],
				'payroll_remittance_agency_event': ['payroll_remittance_agency_event'],
				'remittance_source_account': ['remittance_source_account'],
				'remittance_destination_account': ['remittance_destination_account'],
				'geo_fence': ['geo_fence']
			},
			log_detail_grid: null,
			log_detail_script_name: null
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'LogEditView.html';
		this.context_menu_name = $.i18n._( 'Audit' );
		this.navigation_label = $.i18n._( 'Audit' );
		this.viewId = 'Log';
		this.script_name = 'LogView';
		this.log_detail_script_name = 'LogDetailView';
		this.api = TTAPI.APILog;
		this.noticeDiv = $( '.audit-info' );

		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var tab_model = {
			'tab_audit_details': {
				'label': $.i18n._( 'Audit Details' ),
				'html_template': this.getAuditLogTabHtml()
			},
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APILog,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_log',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var tab_audit_details = this.edit_view_tab.find( '#tab_audit_details' );
		var tab_audit_details_column1 = tab_audit_details.find( '.first-row' );
		// tab_audit_details column1

		// Date
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_audit_details_column1, '' );

		// Action
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'action'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Action' ), form_item_input, tab_audit_details_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {
			field: 'user_name'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_audit_details_column1 );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'description'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_audit_details_column1, '' );

		// set the log details information.
		this.initLogDetailsView();
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

		if ( this.select_layout && this.select_layout.data ) {
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
		}

		if ( TTUUID.isUUID( this.refresh_id ) ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;
		} else {
			this.last_select_ids = this.getGridSelectIdArray();
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
				$this.setAuditInfo();
				if ( TTUUID.isUUID( $this.refresh_id ) ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.grid.getGridParam( 'data' );
					len = grid_source_data.length;

					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}

					var found = false;
					var new_record = result_data[0];

					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
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
						}

						if ( !found ) {
							grid_source_data.push( new_record );
							$this.grid.setData( grid_source_data );

							// if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGrid ) ) {
							// 	len = Global.isSet( len ) ? len : 0;
							// 	$this.resizeSubGrid( len + 1 );
							// }
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
						var current_data = $this.grid.getGridParam( 'data' );
						result_data = current_data.concat( result_data );
					}

					// Process result_data if necessary, this always needs override.
					result_data = $this.processResultData( result_data );

					if ( $this.grid ) {
						if ( LocalCacheData.paging_type === 0 ) {
							if ( !$this.pager_data || $this.pager_data.is_last_page ) {
								$this.paging_widget.css( 'display', 'none' );
							} else {
								$this.paging_widget.css( 'display', 'block' );
							}
						}
						$this.grid.setData( result_data );
						$this.setGridColumnsWidth();
						if ( $this.sub_view_mode && Global.isSet( $this.baseViewSubTabGridResize ) ) {
							$this.baseViewSubTabGridResize( 'tab_audit' );
						}

						$this.reSelectLastSelectItems();
					}
				}

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

	getGridSetup() {
		var $this = this;
		return {
			container_selector: this.sub_view_mode ? '.edit-view-tab-bar' : 'body',
			sub_grid_mode: this.sub_view_mode,
			onResizeGrid: true,
			multiselect: false,
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
			setGridSize: function() {
				$this.baseViewSubTabGridResize( 'tab_audit' ); //Works for Edit Employee -> Audit tab
			}
		};
	}

	_setGridSizeGridHeight( header_size ) {
		// if ( !this.sub_view_mode ) {
		// 	this.grid.setGridHeight( ($( this.el ).height() - (this.search_panel && this.search_panel.is( ':visible' ) ? this.search_panel.height() : 0) - 68 - header_size) );
		// } else if ( !Global.isSet( this.resizeSubGrid ) ) {
		if ( this.pager_data && this.pager_data.last_page_number > 1 ) {
			this.grid.setGridHeight( $( this.el ).parent().parent().parent().height() - 101 - header_size );
		} else {
			this.grid.setGridHeight( $( this.el ).parent().parent().parent().height() - 78 - header_size );
		}

		// }
	}

	setAuditInfo() {
		var updated_info = ( this.parent_edit_record['updated_date'] || $.i18n._( 'N/A' ) ) + ' ' + $.i18n._( 'by' ) + ' ' + ( this.parent_edit_record['updated_by'] || $.i18n._( 'N/A' ) ) + ' ';
		var created_info = ( this.parent_edit_record['created_date'] || $.i18n._( 'N/A' ) ) + ' ' + $.i18n._( 'by' ) + ' ' + ( this.parent_edit_record['created_by'] || $.i18n._( 'N/A' ) ) + ' ';
		this.noticeDiv.find( '.left > .info' ).text( updated_info );
		this.noticeDiv.find( '.right > .info' ).text( created_info );
	}

	autoOpenEditViewIfNecessary() {
		//Auto open edit view. Should set in IndexController
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();
	}

	initLogDetailsView( column_start_from ) {

		var grid = this.edit_view.find( '#grid' );

		if ( grid ) {
			grid.attr( 'id', this.log_detail_script_name + '_grid' );  //Grid's id is ScriptName + _grid
		}

		//grid = this.edit_view.find( '#' + this.log_detail_script_name + '_grid' );

		var column_info_array = [];
		var display_columns = [
			{ label: $.i18n._( 'Field' ), value: 'display_field' },
			{ label: $.i18n._( 'Before' ), value: 'old_value' },
			{ label: $.i18n._( 'After' ), value: 'new_value' }
		];

		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

		for ( var i = start_from; i < len; i++ ) {
			var view_column_data = display_columns[i];

			var column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );
		}

		var grid_setup = {
			container_selector: this.sub_view_mode ? '.edit-view-tab-bar' : 'body',
			sub_grid_mode: this.sub_view_mode,
			onResizeGrid: true,
			multiselect: false,
		};

		this.log_detail_grid = new TTGrid( this.log_detail_script_name + '_grid', grid_setup, column_info_array );
	}

	initEditViewData() {
		super.initEditViewData();
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_audit_details' ).find( '.detail-grid-row' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
		} else {
			this.edit_view_tab.find( '#tab_audit_details' ).find( '.detail-grid-row' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}

		this.log_detail_grid.setGridColumnsWidth();
	}

	onGridDblClickRow() {
		this.onViewDetailClick();
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			} else {
				switch ( key ) {
					case 'details':
						this.setLogDetailsViewData( this.current_edit_record[key] );
						break;
					default:
						break;
				}
			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	setLogDetailsViewData( log_detail_data ) {

		var $this = this;

		if ( !Global.isArray( log_detail_data ) ) {
			$this.showDetailNoResultCover();
		} else {
			$this.removeNoResultCover();
		}

		log_detail_data = Global.formatGridData( log_detail_data );

		$this.log_detail_grid.setData( log_detail_data );

		$this.setLogDetailGridSize();
	}

	setLogDetailGridSize() {

		if ( !this.log_detail_grid || !$( this.log_detail_grid.grid ).is( ':visible' ) ) {
			return;
		}

		var tab_audit_details = this.edit_view.find( '#tab_audit_details_content_div' );
		var first_row = this.edit_view.find( '.first-row' );
		this.log_detail_grid.grid.setGridWidth( tab_audit_details.width() );
		this.log_detail_grid.grid.setGridHeight( tab_audit_details.height() - first_row.height() );
	}

	showDetailNoResultCover() {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( { related_view_controller: this, is_new: false } );

		var grid_div = this.edit_view.find( '.grid-div' );

		grid_div.append( this.no_result_box );
	}

	showNoResultCover() {
		super.showNoResultCover( false );
	}

	onEditClick( editId, noRefreshUI ) {

		this.onViewDetailClick( editId, noRefreshUI );
	}

	onViewDetailClick( editId ) {

		var $this = this;
		this.setCurrentEditViewState( 'view_detail' );
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;
		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {

			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];
		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			filter.filter_data = this.getSubViewFilter( filter.filter_data );

		}
		filter.filter_columns = this.getFilterColumnsForViewDetails();

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();
			}
		} );
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.id = true;
		column_filter.table_name = true;

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

	getFilterColumnsForViewDetails() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		var column_filter = {};
		column_filter.id = true;
		column_filter.table_name = true;
		column_filter.details = true;

		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			column_filter[column_info.name] = true;
		}

		return column_filter;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'view_detail':
				if ( grid_selected_length === 1 ) {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
				} else {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
				}
				break;
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'view_detail':
				this.onViewDetailClick();
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'view_detail':
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
				break;
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'View Details' ),
					id: 'view_detail',
					group: 'editor',
					},
				'cancel'
			]
		};

		return context_menu_model;
	}

	getSubViewFilter( filter ) {
		if ( Global.isSet( this.table_name_key ) ) {
			filter['table_name'] = this.tables[this.table_name_key];
		}

		return filter;
	}

	searchDone() {
		$( 'window' ).trigger( 'resize' );
		TTPromise.resolve( 'initSubAudit', 'init' );
		super.searchDone();

	}

	getAuditLogTabHtml() {
		return `
		<div id="tab_audit_details" class="edit-view-tab-outside">
			<div class="edit-view-tab" id="tab_audit_details_content_div">
				<div class="first-row full-width-column">
				</div>
				<div class="detail-grid-row">
					<div class="grid-div">
						<table id="grid"></table>
					</div>
				</div>
				<div class="save-and-continue-div permission-defined-div" style="top: 240px">
					<span class="message permission-message"></span>
				</div>
			</div>
		</div>`;
	}

}

LogViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'Log', 'LogView.html', function( result ) {

		var args = {
			updated: $.i18n._( 'Updated' ),
			created: $.i18n._( 'Created' )
		};

		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_log_view_controller );
			}
		}
	} );
};

LogViewController.html_template = `
	<div class="view sub-view logView" id="log_view_container">
	<div class="audit-info">
		<div class="left">
			<div class="label-div"><span class="label"><%= updated %>:</span></div>
			<div class="info"></div>
		</div>
		<div class="right">
			<div class="label-div"><span class="label"><%= created %>:</span></div>
			<div class="info"></div>
		</div>
	</div>
		<div class="clear-both-div"></div>
		<div class="grid-div">
			<div class="grid-top-border"></div>
			<div class="sub-grid-view-div">
				<table id="grid"></table>
			</div>
			<div class="bottom-div">
				<div class="grid-bottom-border"></div>
			</div>
		</div>
	</div>
	`;