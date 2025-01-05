import mitt from 'mitt';
const EventBus = mitt();

//Issue #3049 - Moved class static fields outside of main TTEventBus class as Safari v14.1 and older do not support class field declarations.
window.TTEventBusStatics = { AUTO_CLEAR_ON_EXIT: true, mitt: EventBus, _events_by_listener_scope: {} }; //Constants and external libraries
//_events_by_listener_scope  - Internal data element, only to be accessed/changed via functions in this class. Static as its shared across scope_id's.

/**
 * How to understand the ID's used in this class.
 * this.scope_id:	is tied to the owner of the instance of TTEventBus.
 * 					so that they can be removed when the owner of that scope is unloaded/unmounted.
 * mitt_event_id:	is passed to the mitt event library. It uses the event scope id rather than stored instance scope_id,
 * 					because listeners within a view/component might listen to different scope_id's depending on event.
 * unique_listener_id: is only needed for debugging currently, to be able to differentiate between two different listeners
 * 					listening to the same event on the same scope, but using different event handler functions.
 *
 *
 */
class TTEventBus {
	// Standard mitt calls we want to expose for backwards compatibility to our old code.
	// static on = TTEventBusStatics.mitt.on;
	// static off = TTEventBusStatics.mitt.off;

	constructor( options = {} ) {
		// TTEventBus will happily works for both Views and Vue Components using a single id variable, but tracking them both might be more useful in the future.
		this._options = options; // Unlikely to use directly, but will store here for debugging and future options.
		this.scope_id = null; //scope_id of the listening view or component. Not neccessarily the scope of an event. When this scope unloads, we want to clear listener events related to that scope.
		this._setInstanceScopeId( this.generateScopeIdFromOptions( options ) );
		Debug.Text( 'constructor called ('+ this.scope_id +').', 'TTEventBus.js', 'TTEventBus', 'constructor', 11 );
	}

	/**
	 * Scope is created depending on availability of view id and component id. In most cases, the standard is to either use the view_id or component_id, to use both would overcomplicate storage of the components id's in the views..
	 * @param options
	 * @returns {string|boolean}
	 */
	generateScopeIdFromOptions( options ) {
		// E.g. Schedule.vue-schedule-control-bar
		// var scope_string = '';
		// if( options?.view_id ) {
		// 	scope_string += options.view_id;
		// }
		// if( options?.component_id ) {
		// 	if( scope_string !== '' ) { scope_string += '.' }
		// 	scope_string += options.component_id;
		// }
		//
		// return scope_string;
		if ( options && options.view_id && options.component_id ) {
			Debug.Warn( 'Are you sure you want to set both view and component id? This complicates things.', 'TTEventBus.js', 'TTEventBus', 'generateScopeIdFromOptions', 2 );
			return options.view_id + '.' + options.component_id;
		} else if ( options && options.view_id ) {
			return options.view_id;
		} else if ( options && options.component_id ) {
			return options.component_id;
		} else {
			return false;
		}
	}

	/**
	 * Don't call this directly, as we need to generate the scope_id via generateScopeFromOptions first.
	 * @param scope_id
	 * @private
	 */
	_setInstanceScopeId( scope_id ) {
		return this.scope_id = scope_id;
	}

	getInstanceScopeId() {
		return this.scope_id;
	}

	generateMittId( scope_id, event_id ) {
		// E.g. Schedule.vue-schedule-control-bar.scheduleModeOnChange
		return scope_id + '.' + event_id;
	}

	/**
	 * Event that should only last for that view/vue component and be removed when scope is destroyed/unloaded.
	 * @param event_scope The scope_id related to the event.
	 * @param event_id id of the event, should be unique within the provided scope.
	 * @param event_handler Function to call when event is triggered.
	 * @param auto_clear_on_exit Specifies if this event should not be auto cleared when the vue/component is unloaded. Set using TTEventBusStatics.AUTO_CLEAR_ON_EXIT
	 */
	on( event_scope, event_id, event_handler, auto_clear_on_exit ) {
		TTEventBusStatics._events_by_listener_scope[ this.scope_id ] = TTEventBusStatics._events_by_listener_scope[ this.scope_id ] || [];

		// If we want unique ID's then use TTUUID.generateUUID(), but we want unique to a scope, so that duplicates can be prevented.
		var mitt_event_id = this.generateMittId( event_scope, event_id);
		var unique_listener_id = this.scope_id + ':' + mitt_event_id + ':' + TTUUID.generateUUID();

		TTEventBusStatics._events_by_listener_scope[ this.scope_id ].push( {
			unique_listener_id: unique_listener_id,
			mitt_event_id: mitt_event_id,
			event_scope: event_scope,
			event_id: event_id,
			event_handler: event_handler,
			auto_clear_on_exit: auto_clear_on_exit
		} );
		TTEventBusStatics.mitt.on( mitt_event_id, event_handler );
		Debug.Text( this.scope_id + ': Listener created for ('+ unique_listener_id +').', 'TTEventBus.js', 'TTEventBus', 'on', 11 );

		return unique_listener_id;
	}

	/**
	 * Trigger EventBus event, but converts the scope_id and event_id into the mitt event id that the event is registered with.
	 * @param event_scope The scope_id related to the event.
	 * @param event_id id of the event, should be unique within the provided scope.
	 * @param event_data Object containing event data as parameters.
	 */
	emit( event_scope, event_id, event_data ) {
		var mitt_event_id = this.generateMittId( event_scope, event_id);
		Debug.Text( this.scope_id + ': Event emitted for ('+ mitt_event_id +').', 'TTEventBus.js', 'TTEventBus', 'emit', 11 );

		return TTEventBusStatics.mitt.emit( mitt_event_id, event_data );
	}

	/**
	 * TODO: UNFINISHED.
	 * TODO: Improve this by adding ability to remove by scope and name, or scope, name and callback, or by unique ID.
	 * Warning: This will remove all events that match the scope_id and event_id, even if there are multiple.
	 * @param scope_id the scope of the event that needs to be switched off.
	 * @param event_id the event_id of the event tyhat needs to be switched off.
	 * @returns {void|number}
	 */
	off( scope_id, event_id ) {
		var scope_array = TTEventBusStatics._events_by_listener_scope[ scope_id ];
		if( scope_array === undefined ) {
			// scope_id not found.
			Debug.Error( 'Error: invalid params passed. scope_id not found.', 'TTEventBus.js', 'EventBus', 'off', 1 );
			return -1;
		}
		var removeIndex = scope_array.map( item => item.event_id ).indexOf( event_id ); // TODO: Will only match the FIRST found, problem for multiple listeners like in Schedule.scheduleModeOnChange
		if( removeIndex >= 0 ) {
			var stored_event = scope_array[ removeIndex ];
			scope_array.splice(removeIndex, 1);
			Debug.Text( this.scope_id + ': Listener removed for ('+ stored_event.mitt_event_id +').', 'TTEventBus.js', 'EventBus', 'off', 11 );

			return TTEventBusStatics.mitt.off( stored_event.mitt_event_id, stored_event.event_handler );
		} else {
			// event_id not found in array.
			Debug.Error( 'Error: invalid params passed. event_id not found.', 'TTEventBus.js', 'EventBus', 'off', 1 );
			return -1;
		}
	}

	/**
	 * Used to trigger allOff() when unloading a view/component, using stored scope_id.
	 * @returns {number|boolean}
	 */
	autoClear() {
		Debug.Text( 'Auto off triggered for ('+ this.scope_id +').', 'TTEventBus.js', 'EventBus', 'autoClear', 11 );
		return this.allOff( this.scope_id );
	}
	/**
	 * This removes all events registered on the given scope. This will only apply to events that have the AUTO_CLEAR_ON_EXIT flag.
	 * @param scope_id
	 */
	allOff( scope_id ) {
		var scope_array = TTEventBusStatics._events_by_listener_scope[ scope_id ];
		if( scope_array === undefined ) {
			// scope_id not found.
			Debug.Text( 'Scope not found. But could be normal if this is a global function triggered on a scope with no events.', 'TTEventBus.js', 'EventBus', 'allOff', 2 );

			return -1;
		}

		//Loop in reverse to easily remove array values.
		for ( let i = scope_array.length - 1; i >= 0; i-- ) {
			if ( scope_array[i].auto_clear_on_exit ) {
				// Remove event
				TTEventBusStatics.mitt.off( scope_array[i].mitt_event_id, scope_array[i].event_handler );
				Debug.Text( 'Auto removed ' + scope_array[i].mitt_event_id + ' event on scope close.', 'TTEventBus.js', 'EventBus', 'allOff', 2 );
				scope_array.splice( i, 1 );
			} else {
				Debug.Text( 'Event does not have AUTO_CLEAR_ON_EXIT. Skipping ' + scope_array[i].mitt_event_id, 'TTEventBus.js', 'EventBus', 'allOff', 2 );
			}
		}

		if ( !TTEventBusStatics._events_by_listener_scope[ scope_id ] || TTEventBusStatics._events_by_listener_scope[ scope_id ].length === 0 ) {
			//Remove empty scope array.
			delete TTEventBusStatics._events_by_listener_scope[ scope_id ];
		}

		return true;
	}
}

export default TTEventBus;
