export class PayStubAccountWizardController extends BaseWizardController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.wizard-bg'
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.title = $.i18n._( 'Migrate PS Accounts' );
		this.steps = 2;
		this.current_step = 1;
		$( this.el ).width( 1010 );
		this.render();
	}

	render() {
		super.render();
		this.initCurrentStep();
	}

	//Create each page UI
	buildCurrentStepUI() {
		this.content_div.empty();
		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.html( $.i18n._( 'This wizard will automatically create Pay Stub Amendments to migrate Year-To-Date amounts from one Pay Stub Account to another as of a specific effective date.' ) + '<br><br>' );
				this.content_div.append( label );
				break;
			case 2:
				//Source Pay Stub Account
				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item.css( 'margin-top', '15px' );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );
				var a_combobox = this.getAComboBox( TTAPI.APIPayStubEntryAccount, true, 'global_PayStubAccount', 'src_ids' );
				form_item_label.text( $.i18n._( 'Source Pay Stub Account(s)' ) );
				form_item_input_div.append( a_combobox );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				// Destination Pay Stub Account
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				a_combobox = this.getAComboBox( TTAPI.APIPayStubEntryAccount, false, 'global_PayStubAccount', 'dst_id' );
				form_item_label.text( $.i18n._( 'Destination Pay Stub Account' ) );
				form_item_input_div.append( a_combobox );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				//Effective Date
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				var effective_date = this.getDatePicker( 'effective_date' );
				form_item_label.text( $.i18n._( 'Effective Date' ) );
				form_item_input_div.append( effective_date );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][effective_date.getField()] = effective_date;

				break;
		}
	}

	buildCurrentStepData() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 2:
				if ( !current_step_data ) {
					var date = new Date();
					current_step_ui.effective_date.setValue( date.format() );
				}
				break;
		}
	}

	onDoneClick() {
		var $this = this;
		super.onDoneClick();
		this.saveCurrentStep();
		var src_ids = this.stepsDataDic[2].src_ids;
		var dst_id = this.stepsDataDic[2].dst_id;
		var effective_date = this.stepsDataDic[2].effective_date;
		var ps_api = TTAPI.APIPayStubEntryAccount;
		ps_api.migratePayStubEntryAccount( src_ids, dst_id, effective_date, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data ) {
					$this.onCloseClick();
					if ( $this.call_back ) {
						$this.call_back();
					}
				} else {
					TAlertManager.showErrorAlert( result );
				}
			}
		} );
	}

	setCurrentStepValues() {

		if ( !this.stepsDataDic[this.current_step] ) {
			return;
		} else {
			var current_step_data = this.stepsDataDic[this.current_step];
			var current_step_ui = this.stepsWidgetDic[this.current_step];
		}

		switch ( this.current_step ) {
			case 1:
				break;
			case 2:
				if ( current_step_data.src_ids ) {
					current_step_ui.src_ids.setValue( current_step_data.src_ids );
				}
				if ( current_step_data.dst_id ) {
					current_step_ui.dst_id.setValue( current_step_data.dst_id );
				}
				if ( current_step_data.effective_date ) {
					current_step_ui.effective_date.setValue( current_step_data.effective_date );
				}
				break;
		}
	}

	saveCurrentStep() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				break;
			case 2:
				current_step_data.src_ids = current_step_ui.src_ids.getValue();
				current_step_data.dst_id = current_step_ui.dst_id.getValue();
				current_step_data.effective_date = current_step_ui.effective_date.getValue();
				break;
		}
	}

}