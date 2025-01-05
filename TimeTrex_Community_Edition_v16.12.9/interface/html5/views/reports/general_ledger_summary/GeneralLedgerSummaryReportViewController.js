export class GeneralLedgerSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'GeneralLedgerSummaryReport';
		this.viewId = 'GeneralLedgerSummaryReport';
		this.context_menu_name = $.i18n._( 'General Ledger Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'GeneralLedgerSummaryReportView.html';
		this.api = TTAPI.APIGeneralLedgerSummaryReport;
	}

	onCustomContextClick( id, context_btn ) {
		switch ( id ) {
			case 'export_csv':
			case 'export_csv_flat':
			case 'quickbooks':
			case 'simply':
			case 'sage300':
			case 'xero':
				this.onReportMenuClick( id );
				break;
		}
	}

	onReportMenuClick( id ) {
		this.onViewClick( id );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Export' ),
					id: 'print_checks',
					action_group_header: true,
					action_group: 'export',
					menu_align: 'right',
					permission_result: true,
					permission: true
				}
			]
		};

		var export_general_ledger_result = TTAPI.APIPayStub?.getOptions( 'export_general_ledger', { async: false } ).getResult();

		export_general_ledger_result = Global.buildRecordArray( export_general_ledger_result );

		for ( var i = 0; i < export_general_ledger_result.length; i++ ) {
			var item = export_general_ledger_result[i];
			context_menu_model.include.push( {
				label: item.label,
				id: item.value,
				action_group: 'export',
				menu_align: 'right'
			} );
		}

		return context_menu_model;
	}

}
