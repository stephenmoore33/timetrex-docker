export class PayStubSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'PayStubSummaryReport';
		this.viewId = 'PayStubSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'PayStubSummaryReportView.html';
		this.api = TTAPI.APIPayStubSummaryReport;
	}

	onReportMenuClick( id ) {
		this.processTransactions( id );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				pay_stub: {
					label: $.i18n._( 'Pay Stub' ),
					id: this.script_name + 'PayStub'
				},
				export: {
					label: $.i18n._( 'Export' ),
					id: this.viewId + 'Export'
				}
			},
			exclude: [],
			include: [
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'pay_stub',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Employee Pay Stubs' ),
					id: 'employee_pay_stubs',
					menu_align: 'right',
					action_group: 'pay_stub'
				},
				{
					label: $.i18n._( 'Employer Pay Stubs' ),
					id: 'employer_pay_stubs',
					menu_align: 'right',
					action_group: 'pay_stub'
				},
				{
					label: $.i18n._( 'Process Transactions' ),
					id: 'direct_deposit',
					menu_align: 'right',
					action_group: 'pay_stub'
				}
			]
		};

		return context_menu_model;
	}

	// Overriding empty ReportBaseViewController.processFilterField() called from base.openEditView to provide view specific logic.
	processFilterField() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'status_id' ) {
				item.value = 'filter';
			}
		}
	}

	onFormItemChangeProcessFilterField( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	}

	setFilterValue( widget, value ) {
		widget.setValue( value.status_id );
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'employee_pay_stubs': //All report view
				this.onViewClick( 'pdf_employee_pay_stub' );
				break;
			case 'employer_pay_stubs': //All report view
				this.onViewClick( 'pdf_employer_pay_stub' );
				break;
			case 'direct_deposit':
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
}