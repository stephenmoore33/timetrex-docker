export class PayStubTransactionSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'PayStubTransactionSummaryReport';
		this.viewId = 'PayStubTransactionSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Transaction Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'PayStubTransactionSummaryReportView.html';
		this.api = TTAPI.APIPayStubTransactionSummaryReport;
	}

	onReportMenuClick( id ) {
		this.processTransactions( id );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
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
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Process Transactions' ),
					id: 'direct_deposit',
					menu_align: 'right',
					action_group: 'other'
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'direct_deposit':
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
}