import { Global } from '@/global/Global';
import { createApp } from 'vue';
import main_ui_router from '@/components/main_ui_router';
import TTContextMenu from '@/components/context_menu/TTContextMenu';
import PrimeVue from 'primevue/config';
import TTEventBus from '@/services/TTEventBus';

/*
 * -- Structure of the ContextMenuManager --
 * You can search the code for `#VueContextMenu#` which will help identify the key areas during development and refactor.
 * Dynamic: For the Dynamic creation of the menu, you can search for #VueContextMenu#Dynamic-View and #VueContextMenu#Dynamic-EditView and look at mountContextMenu
 *
 * There are 3 elements to the ContextMenuManager
 * 1) The TT applications view code, 2) the ContextMenuManager, and 3) the Vue context menu.
 * The Vue component is controlled by events, the events work as follows:
 * 1) The Vue component is setup to listen to events from the EventBus (mitt)
 * 2) The control events are fired from the ContextMenuManager.
 * 3) The view controller calls functions in the ContextMenuManager to trigger these events.
 * - ContextMenuManagers are created in BaseViewController.buildContextMenu
 * - ContextMenu Models are built in BaseViewController.buildContextMenuModels() and all other ViewControllers that override those functions.
 * - The Vue component is created currently by the LegacyView component, and only when a viewId is passed to the LegacyView, currently done by the Vue Router.
 * The Vue router in turn is triggered NOT by the hash changes, as this currently leads to some race conditions/out of sync with the TT app.
 * Instead, the Vue router is running in createMemoryHistory mode, and routing manually triggered by VueRouter.push, which is currently done from BaseViewController.loadView
 *
 * -- Creating a new Context Menu --
 * So step 1 in adding a new context menu, is make sure the Vue Router is triggered, to create a new menu.
 * The new contextMenu responds to a viewId, so this needs to be unique. (Approach might have to be re-evaluated for places with subviews.
 * Maybe in the router we pass a contextmenu id which can be the same as the viewId in most cases? Discuss with Mike.
 * Either way, having the router control that Vue contextmenu generation will control duplicates/clashes.
 * -- Points of note
 * - Make sure any new listeners in the Vue component are also destroyed once no longer used (e.g. when component is unMounted).
 *
 * Initialized from BaseViewController rather than the Vue component, so that each manager created is controlled from this point.
 * Every contextmenu Vue component will then listen to a given viewId which is triggered by BaseVC.loadview.
 * If it was the other way around, there could be more chance for a viewId manager to be overriden in the array.
 * Still, the organisation between when a Vue contextmenu is created, and a manager from the TT app side, could be improved.
 * Also, worst case scenario, a manager is created in BaseView, and no vue component to listen to it.
 * If the vue component was to control when a manager was created, then we would get problems when functions like this.context_menu.parseContextRibbonAction are called from setDefault menu functions within views. So BaseView MUST be in charge of creating the manager.
 * - An area to improve would be to have our own EventBus, not re-use the one from PrimeVue.
 */

class ContextMenu {
	constructor( options ) {
		// Set validation to reject if minimum data not supplied.
		this.id = options.id || null;
		this.type = options.type || null;
		this.vue_menu_instance = options.vue_menu_instance || null;
		this.view_controller_instance = options.view_controller_instance || null;
		this.menu_model = null;
	}
}


class ContextMenuManager {
	constructor() {
		this._menus = {}; // Will contain ContextMenu's
		this.event_bus = new TTEventBus({ view_id: 'context_menu' });
	}

	createAndMountMenu( menu_id, parent_mount_container, parent_context ) {
		if( !menu_id || !parent_mount_container || !parent_context ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'createAndMountMenu', 1 );
			return false;
		}
		if( this.getMenu( menu_id ) !== undefined ) {
			Debug.Error( 'Error: Context Menu Manager ('+ menu_id +') already exists and mounted.', 'ContextMenuManager.js', 'ContextMenuManager', 'createAndMountMenu', 1 );
			return false;
		}
		// TODO:
		// - Tie in a onDestroy function where the vue context and reference in the array are deleted if the menu is removed from the dom.

		// #VueContextMenu#Dynamic-View - Create dynamic container for the vue context menu
		parent_mount_container.prepend('<div id="'+ menu_id +'" class="context-menu-mount-container"></div>');

		// Create and mount unique context menu for this view.
		let vue_context = this.mountContextMenu( menu_id );

		let menu = new ContextMenu({
			id: menu_id,
			vue_menu_instance: vue_context,
			view_controller_instance: parent_context ,
		});

		// Add context menu to the array of active context menu's so we can track them.
		this._menus[ menu_id ] = menu;
		return menu.id;
	}
	generateMenuId( parent_type, parent_id ) {
		if( !parent_type || !parent_id ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'generateMenuId', 1 );
			return false;
		}
		/* -- Examples --
		* View: BranchView
		* EditView: BranchView -> Edit
		* EditView with tabs: Employee -> Edit
		* SubView: Example?
		* SubViewLists: Employee -> Edit -> Qualifications.
		* */

		return `CM-${parent_id}-${parent_type}`; // Maybe need a unique menu on the end? or not?
	}
	getMenu( id ) {
		return this._menus[ id ];
	}
	mountContextMenu( menu_id ) {
		if( menu_id === undefined ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'mountContextMenu', 1 );
			return false;
		}
		if( this.getMenu( menu_id ) !== undefined ) {
			Debug.Error( 'Error: Context Menu Manager ('+ menu_id +') already exists and mounted.', 'ContextMenuManager.js', 'ContextMenuManager', 'mountContextMenu', 1 );
			return false;
		}

		// Used by #VueContextMenu#Dynamic-View and #VueContextMenu#Dynamic-EditView
		let mount_component = TTContextMenu;
		let mount_reference = '#' + menu_id;
		// TODO: Check if component has not already been mounted or existing in the menu list.

		let vue_menu_instance = createApp( mount_component, { menu_id: menu_id } ); // Can pass an object in here too for proper JS only code, and allow data in without eventBus.
		vue_menu_instance.use( PrimeVue, { ripple: true, inputStyle: 'filled' }); // From: AppConfig.vue this.$primevue.config.inputStyle value is filled/outlined as we dont use AppConfig in TT.
		vue_menu_instance.use( main_ui_router ); // #VueContextMenu# FIXES: Failed to resolve component: router-link when TTOverlayMenuButton is opened. Because each context menu is a separate Vue instance, and they did not globally 'use' the Router, only in main ui.
		vue_menu_instance.mount( mount_reference ); // e.g. '#tt-edit-view-test'

		return vue_menu_instance;
	}
	unmountContextMenu ( id ) {
		if( this._menus[ id ] ) {
			this._menus[ id ].vue_menu_instance.unmount();
			delete this._menus[ id ];
			Debug.Text( 'Context menu successfully unmounted ('+ id +').', 'ContextMenuManager.js', 'ContextMenuManager', 'unmountContextMenu', 11 );
			return true;
		} else {
			Debug.Warn( 'Unable to unmount context menu. Menu not found ('+ id +'). Maybe already removed?', 'ContextMenuManager.js', 'ContextMenuManager', 'unmountContextMenu', 11 );
			return false;
		}
	}
	buildContextMenuModelFromBackbone( menu_id, final_context_menu_model, view_controller_context ) {
		if( menu_id === null ) return false;

		var parsed_bb_menu = this.convertBackBoneMenuModelToPrimeVue( final_context_menu_model, view_controller_context );
		this.updateMenuModel( menu_id, parsed_bb_menu );

		if ( this.getMenu( menu_id ) ) {
			//Fixes context menu flashing. For example when a user has lower permission levels context icons will appear and then dissapear half a second later.
			//NOTE: These branching paths may be removed in the future when setDefaultMenu() and setEditMenu() are consolidated and some changes may be needed to reduce duplicate calls to the set*Menu() functions.
			if ( !this.getMenu( menu_id ).view_controller_instance.is_edit && !this.getMenu( menu_id ).view_controller_instance.is_add && !this.getMenu( menu_id ).view_controller_instance.is_viewing ) {
				this.getMenu( menu_id ).view_controller_instance.setDefaultMenu();
			} else {
				//Fixes issue where refreshing an edit view would flash context menus.
				this.getMenu( menu_id ).view_controller_instance.setEditMenu();
			}
		} else {
			Debug.Error( 'Error: Cannot find Vue context menu.', 'ContextMenuManager.js', 'ContextMenuManager', 'getMenuModelByMenuId', 1 );
		}
	}
	convertBackBoneMenuModelToPrimeVue( backbone_menu_format, view_controller_context ) {
		/*
		* This would have arrays for groups & icons
		* icons is very similar to the PrimeVue MenuModel.
		*
		* Note: This function only builds the menu when its asked to do so, like on view controller init.
		* However, if the menu icons are manipulated via permission controls or user selecting items in a data table, this function does not run.
		* Thats mostly fine, as the menu structure itself does not really change based on user interaction,
		* But some things might. E.g. (Paystubs ->Edit Employee) when the Save menu initially has all the options during this build run, but later ends up with only one Save option. That needs to be handled dynamically in Vue.
		* */

		var icons = backbone_menu_format.icons;
		var action_groups = {};
		var parsed_icons = [];

		// TODO: Is the below still needed? And can we do this in a neater/clearer way.
		if ( icons && Object.keys( icons ).length && Object.keys( icons ).length < 1) {
			// Invalid data.
			console.error('ContextMenuManager: Invalid data format. No icons or not an array.');
			return false;
		}

		// Sort icons by their sort_order attribute.
		icons = Object.values(backbone_menu_format.icons).sort(this.sortCompareIcons);

		// parse the icons item.icons further
		for ( var key in icons ) {
			// Dev warning: iteration order is not guaranteed during for...in loops. And only modify the currently iterated key. https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/for...in#deleted_added_or_modified_properties
			if(icons.hasOwnProperty( key )) {
				var item = icons[key];

				// Validation checks and default values.
				// When modifying these, make sure to update sub_menu_item checks later on too.
				if( !item.id ) {
					Debug.Error( 'ERROR: Invalid data. Missing ID.', 'ContextMenuManager.js', 'ContextMenuManager', 'convertBackBoneMenuModelToPrimeVue', 1 );
				}
				if( item.icon ) delete item.icon; // Remove the .icon attribute, because it interferes with PrimeVue <button> element's icon attribute. Currently still needed for legacy context menu icon display.
				if( item.vue_icon ) item.icon = item.vue_icon;
				if( item.items ) delete item.items; // Remove the .items array, as this is from the old menu, and PrimeVue will incorrectly interpret it as a submenu.
				if( !item.action_group ) item.action_group = item.id; // If no action_group, then just use the id, as we will use action_group later on as a unique id.
				if( !item.menu_align ) item.menu_align = 'left'; // Set default alignment to left
				if( !item.command ) item.command = default_command; // Modify the command related to the nav item

				function default_command( item ) {
					// Perhaps put this into a default parser just like MenuManager.
					// console.log('Default command triggered for ', icon_id);
					if ( item.id ) {
						view_controller_context.onContextMenuClick( item, item.id );
					} else {
						view_controller_context.onContextMenuClick( null, item );
					}
				}

				// Convert TT legacy selected state to Vue active state. (Used in places like MessageControlVC).
				if( item.selected == true ) { // == rather than === as this is based on legacy code which sometimes comes as strings from API.
					delete item.selected;
					item.active = true;
					item.icon = 'tticon tticon-show-in-dropdown tticon-radio_button_checked_black_24dp';
				} else if ( item.selected == false ) { // == rather than === as this is based on legacy code which sometimes comes as strings from API.
					delete item.selected;
					item.active = false;
					item.icon = 'tticon tticon-show-in-dropdown tticon-radio_button_unchecked_black_24dp';
				}

				// Modify the label for a nav item, parse the <br> tags - might be possible to remove this in a couple of months, once confirmed the old menu/data is not needed.
				if ( item.label ) {
					item.label = item.label.replace( '<br>', ' ' ); // Removing the <br> here rather than in the source data, just incase we want to keep the old menu/data working for now.
				}
				// Parse old style contextmenu dropdowns to new primevue menu
				if( item.original_items && item.original_items.length > 0 ) {
					// This results in making a special version of the split-button menu, using the overlay menu, which has a header instead of the currently 'active' action button. See TTOverlayMenuButton.vue for more detailed explanation.

					// Create on-the-fly action_group entry for the legacy drop down menus like Print in TimeSheetViewController
					if( !item.action_group ) {
						item.action_group = item.id;
					}

					item.items = []; // RibbonSubMenuNavItem adds the backbone menu models to the parent .items array, so we need to clear it before it confuses the PrimeVue menu which uses .items too.

					item.action_group_header = true;
					item.permission_result = false; // to hide it in legacy context menu and avoid errors in legacy parsers.
					this.parseIconActionGroup( item, action_groups, parsed_icons ); // Parse the header to generate the group array.

					for ( var sub_key in item.original_items ) {
						if( item.original_items.hasOwnProperty( sub_key )) {
							var sub_menu_item = item.original_items[sub_key];

							// Modify the command related to the sub menu/nav item.
							if( !sub_menu_item.command ) sub_menu_item.command = default_submenu_command;

							// Set the alignment to match the parent item.
							sub_menu_item.menu_align = item.menu_align;

							// Set the action_group to match the parent item.
							sub_menu_item.action_group = item.action_group;
							this.parseIconActionGroup( sub_menu_item, action_groups, parsed_icons ); // Parse the sub items to add them to the group.
						}
					}

					function default_submenu_command( icon_id ) {
						// Perhaps put this into a default parser just like MenuManager.
						view_controller_context.onReportMenuClick( icon_id);
					}

				} else {
					this.parseIconActionGroup( item, action_groups, parsed_icons );
				}
			}
		}

		return parsed_icons;
	}

	// Parse action_group's
	// If an action group exists, add icons into groups, even if there is just one, as TTContextButton will handle single item arrays as a single object anyway.
	parseIconActionGroup( parse_item, action_groups, parsed_icons ) {
		var action_group_id = parse_item.menu_align + '-' + parse_item.action_group;

		if( action_groups[ action_group_id ] === undefined ) {
			// First item encountered for this action_group. Create new and add current item.
			action_groups[ action_group_id ] = {
				action_group_id: action_group_id,
				menu_align: parse_item.menu_align, // set the align based on first item, as in theory they would all have the same value.
				items: [ parse_item ]
			};
			parsed_icons.push( action_groups[ action_group_id ] ); // Add action group reference to the main icon array.
		} else {
			// This action group already exists, add the item to the existing action group.
			if( parse_item.action_group_header ) {
				action_groups[ action_group_id ].items.unshift( parse_item ); // move to the front, to treat as group label
			} else {
				action_groups[ action_group_id ].items.push( parse_item );
			}
		}
	}

	// Sort the icons based on their sort_order attribute
	// Based on Global.compareMenuItems from the original ribbon menu.
	sortCompareIcons( a, b ) {
		// If no sort_order, or sort_order is equal, then base on add_order
		if ( a.sort_order === undefined || b.sort_order === undefined || a.sort_order == b.sort_order ) {
			// Debug.Text( 'sort_order equal or undefined. Check sort_order/add_order for '+ a.id + ' ('+ a.sort_order +') '+' & '+ b.id +' ('+ b.sort_order +').', 'ContextMenuManager.js', 'ContextMenuManager', 'sortCompareIcons', 10 );

			if ( a.add_order < b.add_order ) {
				return -1; // Leave a and b order unchanged.
			}
			if ( a.add_order > b.add_order ) {
				return 1; // Sort b before a.
			}
		}

		// Base sorting on sort_order, regardless of add_order
		if ( a.sort_order < b.sort_order ) {
			return -1; // Leave a and b order unchanged.
		}

		if ( a.sort_order > b.sort_order ) {
			return 1; // Sort b before a.
		}

		// No criteria matched at all - skip basically. Could also apply to action_group_header's (they will be pushed to the front of their group later in action group parsing)
		Debug.Text( 'sort_order returned 0. Check sort_order/add_order for '+ a.id + ' ('+ a.sort_order +') '+' & '+ b.id +' ('+ b.sort_order +').', 'ContextMenuManager.js', 'ContextMenuManager', 'sortCompareIcons', 10 );
		return 0; // leave a and b unchanged with respect to each other, but sorted with respect to all different elements.
	}

	filterVisibleItems( items ) {
		let check_visible = ( element ) => element.visible !== false;
		let filtered_items = items.filter( check_visible );

		return {
			items: filtered_items,
			count: filtered_items.length
		};
	}

	getMenuModelByMenuId( menu_id ) {
		var menu_model = [];

		if ( this.getMenu( menu_id ) !== undefined && this.getMenu( menu_id ).menu_model ) {
			for ( var menu_item of this.getMenu( menu_id ).menu_model ) {
				menu_model.push( ...menu_item.items );
			}
		} else {
			//This should never happen, the one case it did happen (now fixed) was when right click menu was being created before edit view when opening a record.
			Debug.Error( 'Fatal Error: Vue context menu has not been build yet and is undefined.', 'ContextMenuManager.js', 'ContextMenuManager', 'getMenuModelByMenuId', 1 );
			// debugger; // Useful to debug here if this error happens.
		}

		return menu_model;
	}

	// Context Menu Event Emitters

	updateMenuModel( menu_id, menu_model ) {
		if( !menu_id || !menu_model ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'updateMenuModel', 1 );
			return false;
		}

		//This can be done differently, just creating an easy to access reference to the menu model.
		if ( this.getMenu( menu_id ) ) {
			this.getMenu( menu_id ).menu_model = menu_model;
		} else {
			Debug.Error( 'Error: Cannot find Vue context menu..', 'ContextMenuManager.js', 'ContextMenuManager', 'getMenuModelByMenuId', 1 );
		}

		// EventBus.emit( view_id + '.updateContextMenu', {
		this.event_bus.emit( 'context_menu', 'update_context_menu', {
			menu_id: menu_id,
			menu_model: menu_model
		});
		Debug.Text( 'Contextmenu\n MENU update SENT for: '+ menu_id, 'ContextMenuManager.js', 'ContextMenuManager', 'updateMenuModel', 11 );
	}

	updateItem( menu_id, item_id, item_attributes ) {
		if( !menu_id || !item_id || !item_attributes ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'updateItem', 1 );
			return false;
		}

		// EventBus.emit( this.view_controller_instance.viewId + '.updateItem', {
		this.event_bus.emit( 'context_menu', 'update_item', {
			menu_id: menu_id,
			item_id: item_id,
			item_attributes: item_attributes
		});
		Debug.Text( 'Contextmenu\n ITEM update SENT for: '+ menu_id + ':' + item_id, 'ContextMenuManager.js', 'ContextMenuManager', 'updateItem', 11 );
	}

	// Context Menu actions

	multiSelectActivateItem( item_id ) {
		if( !item_id ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'multiSelectActivateItem', 1 );
			return false;
		}
		this.event_bus.emit( 'context_menu', 'activate_multi_select_item', {
			item_id: item_id
		});
		Debug.Text( 'Contextmenu\n MULTI_ITEM update SENT for: '+ menu_id + ':' + item_id, 'ContextMenuManager.js', 'ContextMenuManager', 'multiSelectActivateItem', 11 );
	}

	activateSplitButtonItem( menu_id, item_id ) {
		if ( !menu_id || !item_id ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'activateSplitButtonItem', 1 );
			return false;
		}
		this.event_bus.emit( 'context_menu', 'activate_split_button_item', {
			menu_id: menu_id,
			item_id: item_id
		} );
		Debug.Text( 'Contextmenu\n SPLIT BUTTON update SENT for: ' + menu_id + ':' + item_id, 'ContextMenuManager.js', 'ContextMenuManager', 'activateSplitButtonItem', 11 );
	}

	freezeSplitButtonActiveItem( menu_id, item_id ) {
		if ( !menu_id || !item_id ) {
			Debug.Error( 'Error: Invalid parameters passed to function.', 'ContextMenuManager.js', 'ContextMenuManager', 'setSplitButtonTempIgnoreDisabled', 1 );
			return false;
		}
		this.event_bus.emit( 'context_menu', 'freeze_split_button_active_item', {
			menu_id: menu_id,
			item_id: item_id
		} );
		Debug.Text( 'Contextmenu\n SPLIT BUTTON update SENT for: ' + menu_id + ':' + item_id, 'ContextMenuManager.js', 'ContextMenuManager', 'setSplitButtonTempIgnoreDisabled', 11 );
	}

	disableMenuItem( menu_id, item_id, reverse_action ) {
		if( reverse_action ) {
			this.updateItem( menu_id, item_id, { disabled: false });
		} else {
			this.updateItem( menu_id, item_id, { disabled: true });
		}
	}

	hideMenuItem( menu_id, item_id, reverse_action ) {
		if( reverse_action ) {
			this.updateItem( menu_id, item_id, { visible: true });
		} else {
			this.updateItem( menu_id, item_id, { visible: false });
		}
	}

	activateMenuItem( menu_id, item_id, reverse_action ) {
		// Note: This is also done during menu build for selected items, in convertBackBoneMenuModelToPrimeVue
		if( reverse_action ) {
			this.updateItem( menu_id, item_id, {
				active: false,
				icon: 'tticon tticon-show-in-dropdown tticon-radio_button_unchecked_black_24dp',
			});
		} else {
			this.updateItem( menu_id, item_id, {
				active: true,
				icon: 'tticon tticon-show-in-dropdown tticon-radio_button_checked_black_24dp',
			});
		}
	}

}

// Export as below to share one instance of the manager to manage all Context Menu's.
export default new ContextMenuManager()
