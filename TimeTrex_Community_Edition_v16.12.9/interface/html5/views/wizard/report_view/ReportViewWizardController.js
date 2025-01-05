export class ReportViewWizardController extends BaseWizardController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#report-view-wizard'
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.title = $.i18n._( 'Report View' );
		this.steps = 1;
		this.current_step = 1;
		this.render();
	}

	render() {
		super.render();
		this.initCurrentStep();
	}

	buildCurrentStepUI() {
		this.stepsWidgetDic[this.current_step] = {};
		switch ( this.current_step ) {
			case 1:
				this.content_div.children().eq( 0 )[0].contentWindow.document.open();
				if ( this.default_data.post_data ) {
					this.content_div.children().eq( 0 )[0].contentWindow.post_data = this.default_data.post_data;
					this.content_div.children().eq( 0 )[0].contentWindow.document.writeln( this.default_data.result );
				} else {
					this.content_div.children().eq( 0 )[0].contentWindow.document.writeln( this.default_data );
				}
				this.content_div.children().eq( 0 )[0].contentWindow.document.close();
				break;
		}
	}

	onCloseClick() {
		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controllers = LocalCacheData.current_open_wizard_controllers.filter( wizard => wizard.wizard_id !== this.wizard_id );

		var source = 'View'; // Backup value in case the url sm does not exist.
		if ( LocalCacheData.getAllURLArgs() && LocalCacheData.getAllURLArgs().sm ) {
			source = LocalCacheData.getAllURLArgs().sm + '@View';
		}
		$().TFeedback( {
			source: source,
			force_source: true
		} );
	}

	onDoneClick() {
		this.onCloseClick();
	}

}