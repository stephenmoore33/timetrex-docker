export class AccrualPolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#accrual_policy_view_container',

			type_array: null,
			apply_frequency_array: null,
			month_of_year_array: null,
			day_of_month_array: null,
			day_of_week_array: null,
			month_of_quarter_array: null,
			length_of_service_unit_array: null,
			elgible_period_array: null,
			original_milestone_data: [],
			date_api: null,
			accrual_policy_milestone_api: null,
			accrual_policy_user_modifier_api: null,

			sub_accrual_policy_user_modifier_view_controller: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'AccrualPolicyEditView.html';
		this.permission_id = 'accrual_policy';
		this.viewId = 'AccrualPolicy';
		this.script_name = 'AccrualPolicyView';
		this.table_name_key = 'accrual_policy';
		this.context_menu_name = $.i18n._( 'Accrual Policy' );
		this.navigation_label = $.i18n._( 'Accrual Policy' );
		this.api = TTAPI.APIAccrualPolicy;
		this.date_api = TTAPI.APITTDate;
		this.accrual_policy_milestone_api = TTAPI.APIAccrualPolicyMilestone;
		this.accrual_policy_user_modifier_api = TTAPI.APIAccrualPolicyUserModifier;
		this.month_of_quarter_array = Global.buildRecordArray( { 1: 1, 2: 2, 3: 3 } );
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 're_calculate_accrual':
				this.onReCalAccrualClick();
				break;
		}
	}

	onReCalAccrualClick() {
		var default_data = {};
		var $this = this;

		if ( this.edit_view ) {
			default_data.accrual_policy_id = [this.current_edit_record.id];
		} else {
			default_data.accrual_policy_id = this.getGridSelectIdArray();
		}

		IndexViewController.openWizard( 'ReCalculateAccrualWizard', default_data, function() {
			$this.search();
		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'ReCalculate Accrual' ),
					id: 're_calculate_accrual',
					permission_result: true,
					permission: null,
					menu_align: 'right',
					action_group: 'other'
				}
			]
		};

		return context_menu_model;
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'apply_frequency', api: this.api },
			{ option_name: 'eligible_period', api: this.api },
			{ option_name: 'length_of_service_unit', field_name: 'length_of_service_unit_id', api: this.accrual_policy_milestone_api },
		];

		this.initDropDownOptions( options );

		this.date_api.getMonthOfYearArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = res;
			}
		} );
		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = res;
			}
		} );
		this.date_api.getDayOfWeekArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_week_array = res;
			}
		} );
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 're_calculate_accrual':
				this.setDefaultMenuReCalAccrualWizardIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 're_calculate_accrual':
				this.setEditMenuReCalAccrualWizardIcon( context_btn );
				break;
		}
	}

	setEditMenuReCalAccrualWizardIcon( context_btn ) {
		if ( PermissionManager.validate( 'accrual_policy', 'enabled' ) &&
			( PermissionManager.validate( 'accrual_policy', 'edit' ) || PermissionManager.validate( 'accrual_policy', 'edit_child' ) || PermissionManager.validate( 'accrual_policy', 'edit_own' ) )
		) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );

		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.current_edit_record && this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuReCalAccrualWizardIcon( context_btn, grid_selected_length ) {
		if ( PermissionManager.validate( 'accrual_policy', 'enabled' ) &&
			( PermissionManager.validate( 'accrual_policy', 'edit' ) || PermissionManager.validate( 'accrual_policy', 'edit_child' ) || PermissionManager.validate( 'accrual_policy', 'edit_own' ) )
		) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );

		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length >= 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_accrual_policy': { 'label': $.i18n._( 'Accrual Policy' ), 'is_multi_column': true },
			'tab_eligibility': { 'label': $.i18n._( 'Eligibility' ) },
			'tab_length_of_service_milestones': {
				'label': $.i18n._( 'Length Of Service Milestones' ),
				'html_template': this.getAccrualPolicyLengthOfServiceMilestonesTabHtml()
			},
			'tab_employee_settings': {
				'label': $.i18n._( 'Employee Settings' ),
				'init_callback': 'initSubAccrualPolicyUserModifier',
				'display_on_mass_edit': false,
				'show_permission_div': true
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAccrualPolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_accrual',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_accrual_policy = this.edit_view_tab.find( '#tab_accrual_policy' );
		var tab_eligibility = this.edit_view_tab.find( '#tab_eligibility' );
		var tab_length_of_service_milestones = this.edit_view_tab.find( '#tab_length_of_service_milestones' );

		var tab_accrual_policy_column1 = tab_accrual_policy.find( '.first-column' );
		var tab_eligibility_column1 = tab_eligibility.find( '.first-column' );
		var tab_length_of_service_milestones_column1 = tab_length_of_service_milestones.find( '.first-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_accrual_policy_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_accrual_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_accrual_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_accrual_policy_column1 );

		// Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_shift_policy',
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		// Accrual Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAccrualPolicyAccount,
			allow_multiple_selection: false,
			layout_name: 'global_accrual_policy_account',
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'accrual_policy_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_policy_column1 );

		//Length of Service contributing pay codes.
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingPayCodePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_pay_code_policy',
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'length_of_service_contributing_pay_code_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Length Of Service Hours Based On' ), form_item_input, tab_length_of_service_milestones_column1, '', null, true );

		//Milestone Rollover Based On
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Milestone Rollover Based On' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_accrual_policy_column1, '', null, true, false, 'separated_2' );

		//Employee's Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'milestone_rollover_hire_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee\'s Hire Date' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'milestone_rollover_month' } );
		form_item_input.setSourceData( $this.month_of_year_array );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'milestone_rollover_day_of_month' } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		var tab_accrual_policy_column2 = tab_accrual_policy.find( '.second-column' );
		this.edit_view_tabs[0].push( tab_accrual_policy_column2 );

		// Excess Rollover Accrual Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAccrualPolicyAccount,
			allow_multiple_selection: false,
			layout_name: 'global_accrual_policy_account',
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'excess_rollover_accrual_policy_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Move Excess Rollover Time To' ), form_item_input, tab_accrual_policy_column1 );


		//Frequency In Which To Apply Time to Employee Records
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Frequency In Which To Apply Time to Employee Records' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_accrual_policy_column2, '', null, true, false, 'separated_1' );

		//Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_id', set_empty: false } );
		form_item_input.setSourceData( $this.apply_frequency_array );
		this.addEditFieldToColumn( $.i18n._( 'Frequency' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Employee's Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'apply_frequency_hire_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee\'s Hire Date' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_month' } );
		form_item_input.setSourceData( $this.month_of_year_array );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_day_of_month' } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Day Of Week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_day_of_week' } );
		form_item_input.setSourceData( $.extend( {}, $this.day_of_week_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day Of Week' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		// Month of Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_quarter_month', set_empty: false } );
		form_item_input.setSourceData( $this.month_of_quarter_array );
		this.addEditFieldToColumn( $.i18n._( 'Month of Quarter' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Enable Opening Balance
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_opening_balance' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Applies Initial Accrual Amount on Hire Date)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Enable Opening Balance' ), form_item_input, tab_accrual_policy_column2, '', widgetContainer, true );

		//Enable Pro-Rate Initial Period
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_pro_rate_initial_period' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Based on Hire Date)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Prorate Initial Accrual Amount' ), form_item_input, tab_accrual_policy_column2, '', widgetContainer, true );


		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_eligibility_column1 );

		// After Minimum Employed Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_employed_days', width: 30 } );
		this.addEditFieldToColumn( $.i18n._( 'After Minimum Employed Days' ), form_item_input, tab_eligibility_column1, '', null, true );


		if ( Global.getProductEdition() >= 15 ) {
			//Eligibility Tab
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'eligible_period_id', set_empty: false } );
			form_item_input.setSourceData( $this.eligible_period_array );
			this.addEditFieldToColumn( $.i18n._( 'Eligibility Period' ), form_item_input, tab_eligibility_column1 );

			// Minimum Worked Hours
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'minimum_eligible_time', mode: 'time_unit', need_parser_sec: true } );
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
			check_box.TCheckbox( { field: 'minimum_eligible_apply_retroactive' } );
			var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Apply Retroactively' ) + '</span>' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( label );
			widgetContainer.append( check_box );
			this.addEditFieldToColumn( $.i18n._( 'After Minimum Time' ), [form_item_input, check_box], tab_eligibility_column1, '', widgetContainer, true );

			// Maximum Worked Hours
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'maximum_eligible_time', mode: 'time_unit', need_parser_sec: true } );
			this.addEditFieldToColumn( $.i18n._( 'Before Maximum Time' ), form_item_input, tab_eligibility_column1, '', null, true );

			// Contributing Shift
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIContributingShiftPolicy,
				allow_multiple_selection: false,
				layout_name: 'global_contributing_shift_policy',
				show_search_inputs: true,
				set_empty: true,
				set_default: true,
				field: 'eligible_contributing_shift_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_eligibility_column1, '', null, true );
		}


		var tab_length_of_service_milestones = this.edit_view_tab.find( '#tab_length_of_service_milestones' );

		//
		//Inside editor
		//
		var inside_editor_div = tab_length_of_service_milestones.find( '.inside-editor-div' );
		var args = {
			length_of_service: $.i18n._( 'Length Of Service' ),
			accrual_rate: $.i18n._( 'Accrual Rate/Year' ),
			accrual_total_maximum: $.i18n._( 'Accrual Maximum Balance' ),
			annual_maximum_rollover: $.i18n._( 'Annual Maximum Rollover' ),
			annual_maximum_time: $.i18n._( 'Annual Accrual Maximum' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender(),
			parent_controller: this
		} );

		function getRender() {
			return `
			<table class="inside-editor-render">
				<tr class="title">
					<td style="width: 200px"><%= length_of_service %></td>
					<td style="width: 200px"><%= accrual_rate %></td>
					<td class="annual-maximum-time-td" style="width: 200px"><%= annual_maximum_time %></td>
					<td style="width: 200px"><%= accrual_total_maximum %></td>
					<td style="width: 200px"><%= annual_maximum_rollover %></td>
					<td style="width: 25px"></td>
					<td style="width: 25px"></td>
				</tr>
			</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td class="length_of_service cell"></td>
				<td class="accrual_rate cell"></td>
				<td class="annual_maximum_time annual-maximum-time-td cell"></td>
				<td class="accrual_total_maximum cell"></td>
				<td class="annual_maximum_rollover cell"></td>
				<td class="cell control-icon">
					<button class="plus-icon" onclick=""></button>
				</td>
				<td class="cell control-icon">
					<button class="minus-icon " onclick=""></button>
				</td>
			</tr>`;
		}

		inside_editor_div.append( this.editor );
	}

	onMilestoneRolloverHireDate() {
		if ( this.current_edit_record['milestone_rollover_hire_date'] === true ) {
			this.detachElement( 'milestone_rollover_month' );
			this.detachElement( 'milestone_rollover_day_of_month' );
		} else if ( this.current_edit_record['milestone_rollover_hire_date'] === false ) {
			this.attachElement( 'milestone_rollover_month' );
			this.attachElement( 'milestone_rollover_day_of_month' );
		}

		this.editFieldResize();
	}

	onApplyFrequencyHireDate() {
		if ( this.current_edit_record['apply_frequency_id'] == 20 ) {
			this.attachElement( 'apply_frequency_hire_date' );
			if ( this.current_edit_record['apply_frequency_hire_date'] === true ) {
				this.detachElement( 'apply_frequency_month' );
				this.detachElement( 'apply_frequency_day_of_month' );
			} else {
				this.attachElement( 'apply_frequency_month' );
				this.attachElement( 'apply_frequency_day_of_month' );
			}

			this.editFieldResize();
		}
	}

	onApplyFrequencyChange( arg ) {
		if ( !Global.isSet( arg ) ) {
			if ( !Global.isSet( this.current_edit_record['apply_frequency_id'] ) ) {
				this.current_edit_record['apply_frequency_id'] = 10;
			}

			arg = this.current_edit_record['apply_frequency_id'];
		}
		this.detachElement( 'apply_frequency_hire_date' );
		this.detachElement( 'apply_frequency_month' );
		this.detachElement( 'apply_frequency_day_of_month' );
		this.detachElement( 'apply_frequency_day_of_week' );
		this.detachElement( 'apply_frequency_quarter_month' );

		if ( arg == 20 ) {
			this.onApplyFrequencyHireDate();
		} else if ( arg == 30 ) {
			this.attachElement( 'apply_frequency_day_of_month' );
		} else if ( arg == 40 ) {
			this.attachElement( 'apply_frequency_day_of_week' );
		} else if ( arg == 25 ) {
			this.attachElement( 'apply_frequency_day_of_month' );
			this.attachElement( 'apply_frequency_quarter_month' );
		}

		if ( this.edit_view_ui_dic['type_id'].getValue() == 20 && this.edit_view_ui_dic['eligible_period_id'] && this.edit_view_ui_dic['eligible_period_id'].getValue() != 0 ) {
			this.edit_view_ui_dic['eligible_period_id'].setValue( arg );
		}

		this.editFieldResize();
	}

	onContributingShiftPolicyChange() {
		if ( this.edit_view_ui_dic['contributing_shift_policy_id'] && this.edit_view_ui_dic['eligible_contributing_shift_policy_id'] ) {
			this.current_edit_record['eligible_contributing_shift_policy_id'] = this.edit_view_ui_dic['contributing_shift_policy_id'].getValue();
			this.edit_view_ui_dic['eligible_contributing_shift_policy_id'].setValue( this.edit_view_ui_dic['contributing_shift_policy_id'].getValue() );
		}
	}

	onTypeChange() {
		if ( !Global.isSet( this.current_edit_record['type_id'] ) ) {
			this.current_edit_record['type_id'] = 20;
		}

		if ( this.current_edit_record['type_id'] == 20 ) {
			if ( !this.is_mass_editing ) {
				$( this.edit_view_tab.find( 'ul li' )[1] ).show();
				$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			}
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.second-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.first-column' ).removeClass( 'full-width-column' );
			this.attachElement( 'separated_1' );
			this.attachElement( 'apply_frequency_id' );
			this.attachElement( 'minimum_employed_days' );
			this.detachElement( 'contributing_shift_policy_id' );
			this.attachElement( 'enable_opening_balance' );
			this.attachElement( 'enable_pro_rate_initial_period' );

			this.onApplyFrequencyChange();
			this.onApplyFrequencyHireDate();
		} else if ( this.current_edit_record['type_id'] == 30 ) {
			if ( !this.is_mass_editing ) {
				$( this.edit_view_tab.find( 'ul li' )[1] ).show();
				$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			}
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.second-column' ).css( 'display', 'none' );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.first-column' ).removeClass( 'full-width-column' );
			this.attachElement( 'contributing_shift_policy_id' );
			this.attachElement( 'separated_1' );
			this.attachElement( 'minimum_employed_days' );
			this.detachElement( 'enable_opening_balance' );
			this.detachElement( 'enable_pro_rate_initial_period' );
			this.detachElement( 'apply_frequency_id' );

			this.onApplyFrequencyChange( false );
		}

		this.editFieldResize();
		this.setAccrualRateFormat();
	}

	onEligiblePeriodChange() {
		if ( !Global.isSet( this.current_edit_record['eligible_period_id'] ) ) {
			this.current_edit_record['eligible_period_id'] = 0;
		}

		if ( this.current_edit_record['eligible_period_id'] == 0 ) {
			this.detachElement( 'minimum_eligible_time' );
			this.detachElement( 'minimum_eligible_apply_retroactive' );
			this.detachElement( 'maximum_eligible_time' );
			this.detachElement( 'eligible_contributing_shift_policy_id' );
		} else {
			this.attachElement( 'minimum_eligible_time' );
			this.attachElement( 'minimum_eligible_apply_retroactive' );
			this.attachElement( 'maximum_eligible_time' );
			this.attachElement( 'eligible_contributing_shift_policy_id' );
		}

		this.editFieldResize();
	}

	setAccrualRateFormat( type ) {

		var len = this.editor.rows_widgets_array.length;

		for ( var i = 0; i < len; i++ ) {
			var row = this.editor.rows_widgets_array[i];

			if ( this.current_edit_record['type_id'] == 30 ) {
				row.accrual_rate_hourly.show();
				row.accrual_rate_yearly.hide();
			} else {
				row.accrual_rate_yearly.show();
				row.accrual_rate_hourly.hide();
			}
		}

		var td = $( '.inside-editor-render' ).children().eq( 0 ).children().eq( 0 ).children().eq( 1 );
		if ( this.current_edit_record['type_id'] == 30 ) {
			td.text( $.i18n._( 'Accrual Rate/Hour' ) );
			$( '.annual-maximum-time-td' ).show();
		} else {
			td.text( $.i18n._( 'Accrual Rate/Year' ) );
			$( '.annual-maximum-time-td' ).hide();
		}
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;
		if ( key === 'type_id' ) {
			this.onTypeChange();
		}

		if ( key === 'apply_frequency_id' ) {
			this.onApplyFrequencyChange();
		}

		if ( key === 'apply_frequency_hire_date' ) {
			this.onApplyFrequencyHireDate();
		}

		if ( key === 'milestone_rollover_hire_date' ) {
			this.onMilestoneRolloverHireDate();
		}

		if ( key === 'eligible_period_id' ) {
			this.onEligiblePeriodChange();
		}

		if ( key === 'contributing_shift_policy_id' ) {
			this.onContributingShiftPolicyChange()
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	isDisplayLengthOfServiceHoursBasedOn() {
		var len = this.editor.rows_widgets_array.length;

		var count = 0;

		for ( var i = 0; i < len; i++ ) {
			var row = this.editor.rows_widgets_array[i];
			if ( row['length_of_service_unit_id'].getValue() == 50 ) {
				count++;
			}

		}

		if ( count === 0 ) {
			this.detachElement( 'length_of_service_contributing_pay_code_policy_id' );
			this.edit_view_tab.find( '#tab_length_of_service_milestones' ).find( '.first-column' ).css( 'border', 'none' );
		} else {
			this.attachElement( 'length_of_service_contributing_pay_code_policy_id' );
			this.edit_view_tab.find( '#tab_length_of_service_milestones' ).find( '.first-column' ).css( 'border', '1px solid #c7c7c7' );
		}
	}

	insideEditorSetValue( val ) {
		var len = val.length;

		this.removeAllRows();
		for ( var i = 0; i < val.length; i++ ) {
			if ( Global.isSet( val[i] ) ) {
				var row = val[i];
				if ( Global.isSet( this.parent_id ) ) {
					row['id'] = '';
				}
				this.addRow( row );
			}

		}
	}

	insideEditorAddRow( data, index ) {

		if ( !data ) {
			data = {};
		}

		var row_id = ( data.id && this.parent_controller.current_edit_record.id ) ? data.id : TTUUID.generateUUID();

		var $this = this;
		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		var form_item_input;
		var widgetContainer;

		// Length Of Service
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

		var label_1 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'After' ) + ': ' + ' </span>' );
		var label_2 = $( '<span class=\'widget-right-label\'>&nbsp;</span>' );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'length_of_service', width: 30 } );
		form_item_input.setValue( data.length_of_service ? data.length_of_service : 0 );
		form_item_input.attr( 'milestone_id', row_id );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		var form_item_combobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_combobox.TComboBox( { field: 'length_of_service_unit_id' } );
		form_item_combobox.setSourceData( this.parent_controller.length_of_service_unit_array );
		form_item_combobox.setValue( data.length_of_service_unit_id ? data.length_of_service_unit_id : 10 );

		form_item_combobox.bind( 'formItemChange', function( e, target ) {
			$this.parent_controller.isDisplayLengthOfServiceHoursBasedOn();
		} );

		this.setWidgetEnableBaseOnParentController( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_combobox );

		widgetContainer.append( label_1 );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label_2 );
		widgetContainer.append( form_item_combobox );

		widgets[form_item_input.getField()] = form_item_input;
		widgets[form_item_combobox.getField()] = form_item_combobox;
		row.children().eq( 0 ).append( widgetContainer );

		// Accrual Rate/Year

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		form_item_input.TTextInput( { field: 'accrual_rate_hourly', width: 90, need_parser_sec: false } );
		form_item_input.setPlaceHolder( '' );
		form_item_input.setValue( data.accrual_rate ? data.accrual_rate : '0.000' );
		widgetContainer.append( form_item_input );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( widgetContainer );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'accrual_rate_yearly',
			width: 90,
			mode: 'time_unit',
			need_parser_sec: true
		} );
		form_item_input.setValue( data.accrual_rate ? data.accrual_rate : '0' );
		widgetContainer.append( form_item_input );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( widgetContainer );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		if ( data.type_id == 30 ) {
			widgets.accrual_rate_hourly.show();
			widgets.accrual_rate_yearly.hide();
		} else {
			widgets.accrual_rate_yearly.show();
			widgets.accrual_rate_hourly.hide();
		}

		//Annual Accrual Maximum
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'annual_maximum_time',
			width: 90,
			mode: 'time_unit',
			need_parser_sec: true
		} );
		form_item_input.setValue( data.annual_maximum_time ? data.annual_maximum_time : '0' );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		// Accrual Total Maximum
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_time', width: 90, mode: 'time_unit', need_parser_sec: true } );
		form_item_input.setValue( data.maximum_time ? data.maximum_time : '0' );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		// Annual Maximum Rollover
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'rollover_time', width: 90, mode: 'time_unit', need_parser_sec: true } );
		form_item_input.setValue( data.rollover_time ? data.rollover_time : '0' );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 4 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		if ( typeof index != 'undefined' ) {

			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( ( index ), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		if ( this.parent_controller.is_viewing ) {
			row.find( '.control-icon' ).hide();
		}

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();

		this.parent_controller.setAccrualRateFormat();
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].length_of_service.attr( 'milestone_id' );
		if ( remove_id && TTUUID.isUUID( remove_id ) && remove_id != TTUUID.not_exist_id && remove_id != TTUUID.zero_id ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();

		this.parent_controller.isDisplayLengthOfServiceHoursBasedOn();
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var accrual_rate = 0;

			if ( this.parent_controller.current_edit_record.type_id == 30 ) {
				accrual_rate = row.accrual_rate_hourly.getValue();
			} else {
				accrual_rate = row.accrual_rate_yearly.getValue();
			}

			var data = {
				length_of_service: row.length_of_service.getValue(),
				length_of_service_unit_id: row.length_of_service_unit_id.getValue(),
				accrual_rate: accrual_rate,
				maximum_time: row.maximum_time.getValue(),
				rollover_time: row.rollover_time.getValue()

			};

			if ( this.parent_controller.current_edit_record.type_id == 30 ) {
				data.annual_maximum_time = row.annual_maximum_time.getValue();
			}

			data.id = row.length_of_service.attr( 'milestone_id' );

			data.accrual_policy_id = current_edit_item_id;
			result.push( data );

		}

		return result;
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();

		this.onApplyFrequencyChange();
		this.onTypeChange();
		this.onMilestoneRolloverHireDate();
		this.onEligiblePeriodChange();

		this.initInsideEditorData();
	}

	initInsideEditorData() {

		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( this.mass_edit || ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) ) {
			$this.editor.removeAllRows();
			$this.editor.addRow();
			$this.isDisplayLengthOfServiceHoursBasedOn();
			$this.original_milestone_data = [];
		} else {
			args.filter_data.accrual_policy_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			this.accrual_policy_milestone_api.getAccrualPolicyMilestone( args, true, {
				onResult: function( res ) {
					if ( !$this.edit_view ) {
						return;
					}

					var data = res.getResult();
					if ( data === true ) { // result is null
						$this.original_milestone_data = [];
						$this.editor.addRow();
					} else if ( data.length > 0 ) {
						$this.original_milestone_data =  _.map(data, _.clone);
						$this.editor.setValue( data );
					}

					$this.isDisplayLengthOfServiceHoursBasedOn();

				}
			} );

		}
	}

	saveInsideEditorData( result, callBack ) {
		var $this = this;
		var data = this.editor.getValue( this.refresh_id );
		var remove_ids = $this.editor.delete_ids;
		if ( remove_ids.length > 0 ) {
			$this.accrual_policy_milestone_api.deleteAccrualPolicyMilestone( remove_ids, {
				onResult: function( res ) {
					if ( res && res.isValid() ) {
						$this.editor.delete_ids = [];
					}
				}
			} );
		}

		let changed_data = this.getChangedRecords( data, this.original_milestone_data, [] );

		if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
			$this.accrual_policy_milestone_api.setAccrualPolicyMilestone( changed_data, {
				onResult: function( res ) {
					var res_data = res.getResult();
					if ( Global.isSet( result ) ) {
						result();
					}
				}
			} );
		} else {
			if ( Global.isSet( result ) ) {
				result();
			}
		}
	}

	onSaveResult( result ) {
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				this.refresh_id = this.current_edit_record.id; // as add
			} else if ( result_data && TTUUID.isUUID( result_data ) && result_data != TTUUID.not_exist_id && result_data != TTUUID.zero_id ) {
				this.refresh_id = result_data;
			}

			if ( this.is_mass_editing == false ) {
				var $this = this;
				$this.saveInsideEditorData( function() {
					$this.search();
					$this.onSaveDone( result );

					$this.removeEditView();
				} );
			} else {
				this.search();
				this.onSaveDone( result );
				this.removeEditView();
			}

		} else {
			this.setErrorTips( result );
			this.setErrorMenu();
		}
	}

	removeEditView() {

		super.removeEditView();
		this.sub_accrual_policy_user_modifier_view_controller = null;
	}

	onSaveAndCopyResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data && TTUUID.isUUID( result_data ) && result_data != TTUUID.not_exist_id && result_data != TTUUID.zero_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	_continueDoCopyAsNew() {
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		this.is_add = true;
		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				this.editor.rows_widgets_array[i].length_of_service.attr( 'milestone_id', '' );
			}
		}
		super._continueDoCopyAsNew();
	}

	onCopyAsNewResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		this.copied_record_id = result_data.id;

		result_data.id = '';

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
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
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
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
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	initSubAccrualPolicyUserModifier() {
		var $this = this;

		if ( Global.getProductEdition() <= 10 ) { //This must go before the current_edit_record.id check below, otherwise we return too early and it displays the wrong div.
			this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
			this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		} else {
			this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
		}

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_accrual_policy_user_modifier_view_controller ) {
			this.sub_accrual_policy_user_modifier_view_controller.buildContextMenu( true );
			this.sub_accrual_policy_user_modifier_view_controller.setDefaultMenu();
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'accrual_policy_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			return;
		}

		if ( Global.getProductEdition() >= 15 ) {
			Global.loadScript( 'views/policy/accrual_policy/AccrualPolicyUserModifierViewController.js', function() {
				var tab_accrual_policy = $this.edit_view_tab.find( '#tab_employee_settings' );

				var firstColumn = tab_accrual_policy.find( '.first-column-sub-view' );

				Global.trackView( 'Sub' + 'AccrualPolicyUserModifier' + 'View' );
				AccrualPolicyUserModifierViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
			} );
		}

		function beforeLoadView( tpl ) {
			var args = { parent_view: 'accrual_policy' };
			return { template: _.template( tpl ), args: args };
		}

		function afterLoadView( subViewController ) {
			$this.sub_accrual_policy_user_modifier_view_controller = subViewController;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'accrual_policy_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			} );
		}
	}

	getAccrualPolicyLengthOfServiceMilestonesTabHtml() {
		return `<div id="tab_length_of_service_milestones" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_length_of_service_milestones_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column">
						</div>
					</div>
				</div>`;
	}

}
