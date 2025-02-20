export class ForgotPasswordWizardController extends BaseWizardController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.wizard-bg'
		} );

		super( options );
	}

	init() {
		//this._super('initialize' );

		this.title = $.i18n._( 'Password Reset' );
		this.steps = 1;
		this.current_step = 1;
		if ( this.default_data && typeof this.default_data.api_class != 'undefined' ) {
			this.api = this.default_data.api_class;
		} else {
			this.api = TTAPI.APIAuthentication;
		}
		this.render();
	}

	render() {
		var $this = this;
		super.render();

		// $( this.el ).css( {left:  ( Global.bodyWidth() - $(this.el ).width() )/2} );

//		this.content_div.css( {height: $(this.el ).height() - 145} );

// 		$( window ).resize( function() {
// 			// $( $this.el ).css( {left:  ( Global.bodyWidth() - $($this.el ).width() )/2} );
// //			$this.content_div.css( {height: $($this.el ).height() - 145} );
// 		} );

		this.initCurrentStep();
	}

	buildCurrentStepUI() {

		var $this = this;
		this.content_div.empty();

		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );
				var item = this.getTextInput( 'email' );
				form_item_label.text( $.i18n._( 'Email Address' ) );
				form_item_input_div.unbind( 'keydown' ).bind( 'keydown', function( e ) {
					if ( e.keyCode === 13 ) {
						$this.onDoneClick();
					}
				} );
				form_item_input_div.append( item );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][item.getField()] = item;
				this.stepsWidgetDic[this.current_step][item.getField()].focus();
				break;
		}
	}

	saveCurrentStep() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			default:
				for ( var key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) {
						continue;
					}
					current_step_data[key] = current_step_ui[key].getValue();
				}
				break;
		}
	}

	buildCurrentStepData() {
	}

	onCloseClick() {
		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controllers = LocalCacheData.current_open_wizard_controllers.filter( wizard => wizard.wizard_id !== this.wizard_id );
		LocalCacheData.extra_filter_for_next_open_view = null;
	}

	onDoneClick() {
		var $this = this;
		super.onDoneClick();
		this.saveCurrentStep();

		var email = this.stepsDataDic[1].email;

		this.stepsWidgetDic[1].email.clearErrorStyle();

		if ( !email ) {
			this.stepsWidgetDic[1].email.setErrorStyle( $.i18n._( 'Email must be specified' ), true );
		} else {
			this.api.resetPassword( email, {
				onResult: function( result ) {
					if ( !result.isValid() ) {
						TAlertManager.showErrorAlert( result );
					} else {
						$this.onCloseClick();
						if ( $this.call_back ) {
							$this.call_back( result );
						}
					}
				}
			} );
		}
	}

	showErrorAlert( result ) {
		var details = result.getDetails();
		// if ( details.hasOwnProperty( 'error' ) ) {
		//
		// }
		if ( !details ) {
			details = result.getDescription(); // If the details is empty, try to get description to show.
		}
		var error_string = '';

		if ( Global.isArray( details ) || typeof details === 'object' ) {

			$.each( details, function( index, val ) {

				if ( val.hasOwnProperty( 'error' ) ) {
					val = val.error;
				}

				for ( var key in val ) {
					error_string = error_string + val[key] + '<br>';
				}
			} );
		} else {

			error_string = details;
		}
		IndexViewController.instance.router.showTipModal( error_string );
	}
}