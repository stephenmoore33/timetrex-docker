/*
 * This file houses any common utils that will be used by Vue.
 * Similar to Global.js but class based, and Vue specific.
 */


import { createApp } from 'vue';
import main_ui_router from '@/components/main_ui_router';
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';

class TTVueUtils {
	constructor() {
		this._dynamic_vue_components = {};
	}

	mountComponent( mount_id, mount_component, root_props ) {
		if( mount_id === undefined ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'TTVueUtils.js', 'TTVueUtils', 'mountComponent', 1 );
			return false;
		}

		if( document.getElementById( mount_id ) === null ) {
			Debug.Error( 'Error: mount_id "'+ mount_id + '" does not exist in the DOM.', 'TTVueUtils.js', 'TTVueUtils', 'mountComponent', 1 );
			return false;
		}

		if( this._dynamic_vue_components[ mount_id ] !== undefined ) {
			Debug.Error( 'Error: component ('+ mount_id +') already exists and mounted.', 'TTVueUtils.js', 'TTVueUtils', 'mountComponent', 1 );
			return false;
		}

		root_props = root_props || {};
		root_props.component_id = root_props.component_id || mount_id;
		let mount_reference = '#' + mount_id;
		let vue_app_instance = createApp( mount_component, root_props ); // rootProps is useful to pass in data without the need for EventBus.

		vue_app_instance.use( PrimeVue, { ripple: true, inputStyle: 'filled' }); // From: AppConfig.vue this.$primevue.config.inputStyle value is filled/outlined as we dont use AppConfig in TT.
		vue_app_instance.use( main_ui_router ); // #VueContextMenu# FIXES: Failed to resolve component: router-link when TTOverlayMenuButton is opened. Because each component is a separate Vue instance, and they did not globally 'use' the Router, only in main ui.
		vue_app_instance.directive('tooltip', Tooltip);

		let vue_component_instance = vue_app_instance.mount( mount_reference ); // e.g. '#tt-edit-view-test'

		this._dynamic_vue_components[ mount_id ] = {
			mount_id: mount_id,
			_vue_app_instance: vue_app_instance, // Be very careful using these from outside Vue. Could make for messy code!
			_vue_component_instance: vue_component_instance // Be very careful using these from outside Vue. Could make for messy code!
		};

		return this._dynamic_vue_components[ mount_id ];
	}
	unmountComponent ( mount_id ) {
		if( this._dynamic_vue_components[ mount_id ] && this._dynamic_vue_components[ mount_id ]._vue_component_instance ) {
			this._dynamic_vue_components[ mount_id ]._vue_app_instance.unmount();
			delete this._dynamic_vue_components[ mount_id ];
			Debug.Text( 'Component successfully unmounted ('+ mount_id +').', 'TTVueUtils.js', 'TTVueUtils', 'unmountComponent', 2 );
			return true;
		} else {
			Debug.Text( 'Unable to unmount component. Component not found ('+ mount_id +'). Maybe already removed?', 'TTVueUtils.js', 'TTVueUtils', 'unmountComponent', 2 );
			return false;
		}
	}

}

export default new TTVueUtils() // Export this way to share one instance of the class across the app.