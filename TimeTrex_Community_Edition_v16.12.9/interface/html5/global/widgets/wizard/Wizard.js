/**
 * For an example of implementation see: interface/html5/views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizard.js
 *
 * CRITICAL: ALL WIZARDS MUST HAVE A HOME STEP SO THAT THEY HAVE SOMEWHERE TO START.
 **/

import { TTBackboneView } from '@/views/TTBackboneView';

export class Wizard extends TTBackboneView {
	constructor( options = {} ) {
		_.defaults( options, {
			current_step: false,
			wizard_id: 'generic_wizard',
			wizard_name: $.i18n._( 'Wizard' ),
			step_history: {},
			step_objects: {},
			el: $( '.wizard' ),
			previous_wizard: null,
			_step_map: null,
			do_not_initialize_onload: false, //when this flag is set, initialize will not be run automagically.
			external_data: null,
			events: {
				'click .close-btn': 'onCloseClick',
				'click .close-icon': 'onCloseClick',
				'click .wizard-overlay.onclick-close': 'onCloseClick',
				'click .forward-btn': 'onNextClick',
				'click .back-btn': 'onPrevClick',
				'click .done-btn': 'onDone'
			}
		} );

		super( options );
	}

	initialize( options ) {
		super.initialize( options );

		if ( options && options.external_data ) {
			this.setExternalData( options.external_data );
		}

		if ( !this.do_not_initialize_onload ) {
			this.step_history = {};
			this.step_objects = {};
			var $this = this;

			this.initStepObject( ( this.getCurrentStepName() ? this.getCurrentStepName() : 'home' ), function( obj ) {
				$this.init();
				$this.render();
				$this.enableButtons();


				if ( $this.wizard_id === null ) {
					$this.wizard_id = $this.constructor.name;
				}

				if ( LocalCacheData.current_open_wizard_controllers.some( wizard => wizard.wizard_id === $this.wizard_id ) ) {
					$this.previous_wizard = LocalCacheData.current_open_wizard_controllers.find( wizard => wizard.wizard_id === $this.wizard_id );
				} else {
					$this.previous_wizard = false;
				}

				LocalCacheData.current_open_wizard_controllers.push( $this );
			} );
		}
	}

	//always override
	init() {
		return;
	}

	setExternalData( data ) {
		this.external_data = data;
	}

	getExternalData() {
		return this.external_data;
	}

	onNextClick( e ) {
		if ( this.button_click_procesing == true ) {
			return false;
		}

		if ( this.getStepObject().isRequiredButtonsClicked() == false ) { //On last step.
			var $this = this;
			TAlertManager.showConfirmAlert( $.i18n._( '<strong>WARNING</strong>: You are about to proceed to the next step without performing all required actions! <br><br><strong>This may result in payments or reports not being submitted to this agency.</strong> <br><br>Are you sure you wish to continue?<br><br>' ), null, function( flag ) {
				if ( flag === true ) {
					//Log the fact that the user skipped a step.
					var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
					api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'step', $this.getStepObject().current_step, 'skip', false, true, {
						onResult: function( result ) {
						}
					} );

					$this.onNextClickComplete( e );
				}
			} );
		} else {
			if ( this.getStepObject().current_step == 'home' ) {
				var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
				api_payroll_remittance_agency_event.doLogWizardEvent( this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', this.getStepObject().current_step, 'start', false, true, {
					onResult: function( result ) {
					}
				} );
			} else {
				var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
				api_payroll_remittance_agency_event.doLogWizardEvent( this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'step', this.getStepObject().current_step, 'complete', false, true, {
					onResult: function( result ) {
					}
				} );
			}

			this.onNextClickComplete( e );
		}
	}

	onNextClickComplete( e ) {
		if ( $( e.target ).hasClass( 'disable-image' ) == false ) {
			this.disableButtons();

			var name = this.getStepObject().getNextStepName();
			var $this = this;
			this.initStepObject( name, function( step_obj ) {
				step_obj.setPreviousStepName( $this.getCurrentStepName() );
				$this.setCurrentStepName( name );
				//$this.enableButtons(); //This should be done at the end of each _render() function to avoid race conditions and hammer clicking right arrow causing JS exceptions.
			} );
		}
	}

	onPrevClick( e ) {
		if ( this.button_click_procesing == true ) {
			return false;
		}

		if ( e === true || $( e.target ).hasClass( 'disable-image' ) == false ) {
			this.disableButtons();

			var name = this.getStepObject().getPreviousStepName();
			var $this = this;

			//Needs to be initialized in the event that we came back from the min_tab.
			this.initStepObject( name, function( step_obj ) {
				//step_obj.setPreviousStepName($this.getCurrentStepName());
				$this.setCurrentStepName( name );
				//$this.enableButtons(); //This should be done at the end of each _render() function to avoid race conditions and hammer clicking right arrow causing JS exceptions.
			} );
		}
	}

	onCloseClick( e ) {
		if ( !e || $( e.target ).hasClass( 'disable-image' ) == false ) {
			var $this = this;

			if ( this.getStepObject().getPreviousStepName() !== false && this.getStepObject().getNextStepName() !== false ) { //Not on first step, and not last step
				TAlertManager.showConfirmAlert( $.i18n._( 'Are you sure you wish to cancel without completing all steps for this event?' ), null, function( flag ) {
					if ( flag === true ) {
						var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
						api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', $this.getStepObject().current_step, 'cancel', false, true, {
							onResult: function( result ) {
							}
						} );

						$this.cleanUp();
					}
				} );
			} else if ( this.getStepObject().getNextStepName() == false ) { //On last step.
				if ( this.getStepObject().isRequiredButtonsClicked() == false ) { //Required actions are not performed.
					TAlertManager.showConfirmAlert( $.i18n._( '<strong>WARNING</strong>: You are about to cancel without performing all required actions on this step! <br><br><strong>This may result in payments or reports not being submitted to this agency.</strong> <br><br>Are you sure you wish to continue?<br><br>' ), null, function( flag ) {
						if ( flag === true ) {
							var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
							api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', $this.getStepObject().current_step, 'cancel', false, true, {
								onResult: function( result ) {
								}
							} );

							$this.cleanUp();
						}
					} );
				} else { //Required actions ARE performed.
					TAlertManager.showConfirmAlert( $.i18n._( 'Are you sure you wish to cancel without marking this event as completed?' ), null, function( flag ) {
						if ( flag === true ) {
							var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
							api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', $this.getStepObject().current_step, 'cancel', false, true, {
								onResult: function( result ) {
								}
							} );

							$this.cleanUp();
						}
					} );
				}
			} else {
				var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
				api_payroll_remittance_agency_event.doLogWizardEvent( this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', this.getStepObject().current_step, 'complete', false, true, {
					onResult: function( result ) {
					}
				} );

				$this.cleanUp();
			}
		}
	}

	onDone( e ) {
		if ( !e || $( e.target ).hasClass( 'disable-image' ) == false ) {
			var $this = this;

			if ( this.getStepObject().getNextStepName() == false && this.getStepObject().isRequiredButtonsClicked() == false ) { //On last step.
				TAlertManager.showConfirmAlert( $.i18n._( '<strong>WARNING</strong>: You are about to mark this event as completed without performing all required actions on this step! <br><br><strong>This may result in payments or reports not being submitted to this agency.</strong> <br><br>Are you sure you wish to continue?<br><br>' ), null, function( flag ) {
					if ( flag === true ) {
						var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
						api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'step', $this.getStepObject().current_step, 'skip', false, true, {
							onResult: function( result ) {
								var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
								api_payroll_remittance_agency_event.doLogWizardEvent( $this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', $this.getStepObject().current_step, 'complete', false, true, {
									onResult: function( result ) {
									}
								} );
							}
						} );

						$this.onDoneComplete();
					}
				} );
			} else {
				var api_payroll_remittance_agency_event = TTAPI.APIPayrollRemittanceAgencyEvent;
				api_payroll_remittance_agency_event.doLogWizardEvent( this.getStepObject().getWizardObject().selected_remittance_agency_event_id, 'wizard', this.getStepObject().current_step, 'complete', false, true, {
					onResult: function( result ) {
					}
				} );

				$this.onDoneComplete();
			}
		}
	}

	//Override this function to perform other actions when the user clicks the green checkmark to complete the wizard.
	onDoneComplete( e ) {
		$this.cleanUp();
	}

	addStepObject( name, obj ) {
		//always override.
		this.step_objects[name] = obj;
		return this.step_objects[name]; //returned for chaining.
	}

	getStepObject( name ) {
		if ( typeof name == 'undefined' ) {
			name = this.getCurrentStepName();
		}

		if ( typeof this.step_objects[name] == 'object' ) {
			return this.step_objects[name];
		}
		return this.step_objects['home'];
	}

	getCurrentStepName() {
		return this.current_step;
	}

	setCurrentStepName( val ) {
		this.current_step = val;
	}

	//Stub to stop backbone from complaining that it's missing, Wizard really doesn't render itself as such, it just displays its template.
	render() {
	}

	/*
	 * Clean up the markup.
	 */
	cleanUp() {
		$( this.el ).remove();
		for ( var n in this.step_objects ) {
			if ( this.step_objects[n] ) {
				this.step_objects[n].reload = true;
			}
		}

		LocalCacheData.current_open_wizard_controllers = LocalCacheData.current_open_wizard_controllers.filter( wizard => wizard.wizard_id !== this.wizard_id );

		$().TFeedback( {
			source: this.wizard_id
		} );
	}

	/**
	 * setup a step object
	 *
	 * @param name
	 * @param callback
	 */
	initStepObject( name, callback ) {
		if ( this._step_map.hasOwnProperty( name ) ) {
			if ( this.step_objects[name] == null || typeof this.step_objects[name] != 'object' ) {
				var $this = this;
				Global.loadScript( this._step_map[name].script_path, function() {
					$this.setCurrentStepName( name );
					$( $this.el ).find( '.content' ).html( '' );

					//var obj = new window[$this._step_map[name].object_name]( $this );
					var obj = eval( 'new ' + $this._step_map[name].object_name + '( $this );' );
					obj.reload = false;
					$this.addStepObject( name, obj );

					if ( typeof callback == 'function' ) {
						callback( obj );
					}
				} );
				return;
			} else {
				//reopening a step
				this.setCurrentStepName( name );
				if ( typeof callback == 'function' ) {
					var obj = this.step_objects[name];

					$( this.el ).find( '.content' ).html( '' );
					//obj = new window[this._step_map[name].object_name]( this );
					var obj = eval( 'new ' + this._step_map[name].object_name + '( this );' );

					//reopening a step that has been opened in a previously closed wizard.
					if ( this.step_objects[name].reload == true ) {
						obj.clicked_buttons = {};
						obj.reload = false;
					}

					this.addStepObject( name, obj );

					callback( obj );
				}
				return;
			}
		}
	}

	disableButtons() {
		this.button_click_procesing = true;

		//Changing the button images causes flashing and isn't required for just disabling the buttons while the view loads.
		// $( this.el ).find( '.forward-btn' ).addClass( 'disable-image' );
		// $( this.el ).find( '.back-btn' ).addClass( 'disable-image' );
	}

	/**
	 * Enables the next/prev buttons
	 * the step object for the first step should return false instead fo a previous step name to disable the previous button
	 * the step object for the last step should return false instead of a next step name to disable the next button and enable the done button.
	 */
	enableButtons() {
		var step = this.getStepObject();

		if ( typeof step.getNextStepName() != 'string' ) {
			$( this.el ).find( '.forward-btn' ).addClass( 'disable-image' );
			$( this.el ).find( '.done-btn' ).removeClass( 'disable-image' ); //When right arrow is disabled, assume last step and enable done button.
		} else {
			$( this.el ).find( '.forward-btn' ).removeClass( 'disable-image' );
			$( this.el ).find( '.done-btn' ).addClass( 'disable-image' ); //When right arrow is enabled, assume *not* last step, disable done button.
		}

		if ( typeof step.getPreviousStepName() != 'string' ) {
			$( this.el ).find( '.back-btn' ).addClass( 'disable-image' );
		} else {
			$( this.el ).find( '.back-btn' ).removeClass( 'disable-image' );
		}

		this._enableButtons();

		this.button_click_procesing = false;
	}

	//override me.
	_enableButtons() {
	}

	/**
	 * minimize the wiazrd to a min_tab
	 */
	minimize() {
		LocalCacheData.PayrollRemittanceAgencyEventWizardController = this;
		//Remove from current_open_wizard_controllers so that when opening a new wizard the user does not get
		//a message asking them to close the previous (minimized) wizard that is not viewable.
		LocalCacheData.current_open_wizard_controllers = LocalCacheData.current_open_wizard_controllers.filter( wizard => wizard.wizard_id !== this.wizard_id );
		Global.addViewTab( this.wizard_id, this.wizard_name, window.location.href );
		this.delegateEvents();
		$( this.el ).remove();
	}

	reload() {
		for ( var i in this.step_objects ) {
			this.step_objects[i].reload = true;
		}
	}

	disableForCommunity( callback ) {
		if ( Global.getProductEdition() <= 10 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage(), $.i18n._( 'Denied' ) );
		} else {
			if ( typeof callback == 'function' ) {
				callback();
			}
		}

	}

}