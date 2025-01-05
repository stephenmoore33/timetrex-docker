export class AccrualBalanceSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'AccrualBalanceSummaryReport';
		this.viewId = 'AccrualBalanceSummaryReport';
		this.context_menu_name = $.i18n._( 'Accrual Balance Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'AccrualBalanceSummaryReportView.html';
		this.api = TTAPI.APIAccrualBalanceSummaryReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: ['default']
		};

		return context_menu_model;
	}
}