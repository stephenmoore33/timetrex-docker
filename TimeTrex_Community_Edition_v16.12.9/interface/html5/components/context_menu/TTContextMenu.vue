<template>
    <div class="context-menu-bar">
        <div class="left-container">
            <TTContextButton :id="'context-group-'+item.items[0].id" :class="[dynamicClasses.nowrap, item.action_group_id]" v-for="item in alignItems.left" :items="item.items" />
        </div>
        <div class="center-container">
            <TTContextButton :id="'context-group-'+item.items[0].id" :class="[dynamicClasses.nowrap, item.action_group_id]" v-for="item in alignItems.center" :items="item.items" />
        </div>
        <div class="right-container">
            <TTContextButton :id="'context-group-'+item.items[0].id" :class="[dynamicClasses.nowrap, item.action_group_id]" v-for="item in alignItems.right" :items="item.items" />
        </div>
    </div>
</template>

<script>
import TTContextButton from '@/components/context_menu/TTContextButton';
// Note: No longer need to import the context menu manager, as we only respond to events generated from it.

export default {
    name: "TTContextMenu",
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id + this.menu_id, //Context menu is unique to each view and is removed when the view is closed.
        } );
        // Debug.Text( 'Context menu created.', 'TTContextMenu.vue', 'TTContextMenu', 'created', 10 );

        /*
         * Init event listeners with EventBus to handle data coming from outside the component/Vue.
         */

        /* -------- Event Listener for MENU updates -------- */
        let onUpdateMenu = function ( event_data ) {
            // If the view id changes when the menu is re-built, update current view id. Only menu update should change active view id.
            // if( this.active_view_id !== event_data.view_id ) {
            //     // Debug.Highlight('Active view for context menu updated to: '+event_data.view_id+', previous was: '+ this.active_view_id);
            //     this.active_view_id = event_data.view_id;
            // }

            if( this.menu_id === event_data.menu_id ) {
                // Debug.Highlight('Context menu update received for: '+ event_data.menu_id, event_data );
                Debug.Text( 'Contextmenu\n MENU update RECEIVED for: '+ event_data.menu_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:update_context_menu', 11 );
                this.rebuildMenu( event_data.menu_model );
            } else {
                Debug.Warn( 'Error: Context Menu update does not match menu id.\nThis menu:  '+ this.menu_id +'\nEvent menu: '+event_data.menu_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:update_context_menu', 11 );
            }
        }.bind(this); // Must bind to this at variable definition, not in the EventBus on/off as each .bind creates a new function reference, so it wont be able to match up on delete.

        // EventBus.on( this.viewId + '.updateContextMenu', ( event_data ) => {
        this.event_bus.on( this.component_id, 'update_context_menu', onUpdateMenu, TTEventBusStatics.AUTO_CLEAR_ON_EXIT  );

        /* -------- Event Listener for ITEM updates -------- */
        let onUpdateItem = function( event_data ) {
            if( this.menu_id === event_data.menu_id ) {
                Debug.Text( 'Contextmenu\n ITEM update RECEIVED for: '+ event_data.menu_id + ':' + event_data.item_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:update_item', 11 );
                this.updateMenuItem( event_data.item_id, event_data.item_attributes );
            } else {
                // If the view id does not match active view, then ignore the update, as its probably the previous view.
                // Must ignore update if no match, otherwise item updates from old and new view will conflict and context menu items may then not display as expected. (BugFix: TimeSheet->JumpTo->AddRequest->Cancel. Visible icons not as expected. Cancel icon not disabled).
                Debug.Warn( 'Error: Context Menu Item update does not match menu id.\nThis menu:  '+ this.menu_id +'\nEvent menu: '+event_data.menu_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:updateItem', 11 );
            }
        }.bind(this); // Must bind to this at variable definition, not in the EventBus on/off as each .bind creates a new function reference, so it wont be able to match up on delete.


        // EventBus.on( this.viewId + '.updateItem', ( event_data ) => {
        this.event_bus.on( this.component_id, 'update_item', onUpdateItem, TTEventBusStatics.AUTO_CLEAR_ON_EXIT  );

        /* -------- Event Listener for MULTISELECTITEM updates -------- */
        let onActivateMultiSelectItem = function( event_data ) {
            if( this.menu_id === event_data.menu_id ) {
                Debug.Text( 'Contextmenu\n MULTI_ITEM update RECEIVED for: '+ event_data.menu_id + ':' + event_data.item_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_multi_select_item', 11 );
                this.activateMultiSelectItem( event_data.item_id );
            } else {
                // If the view id does not match active view, then ignore the update, as its probably the previous view.
                // Must ignore update if no match, otherwise item updates from old and new view will conflict and context menu items may then not display as expected. (BugFix: TimeSheet->JumpTo->AddRequest->Cancel. Visible icons not as expected. Cancel icon not disabled).
                Debug.Warn( 'Error: Context Menu Multi-select Item activation does not match menu id.\nThis menu:  '+ this.menu_id +'\n Event menu: '+event_data.menu_id+' )', 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_multi_select_item', 11 );
            }
        }.bind(this); // Must bind to this at variable definition, not in the EventBus on/off as each .bind creates a new function reference, so it wont be able to match up on delete.

        this.event_bus.on( this.component_id, 'activate_multi_select_item', onActivateMultiSelectItem, TTEventBusStatics.AUTO_CLEAR_ON_EXIT  );

        /* -------- Event Listener for SPLITBUTTON updates -------- */
        let onActivateSplitButtonItem = function( event_data ) {
            if ( this.menu_id === event_data.menu_id ) {
                Debug.Text( 'Contextmenu\n SPLIT BUTTON update RECEIVED for: ' + event_data.menu_id + ':' + event_data.item_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_split_button_item', 11 );
                this.activateSplitButtonItem( event_data.item_id );
            } else {
                // If the view id does not match active view, then ignore the update, as its probably the previous view.
                Debug.Warn( 'Error: Context Menu Split Button Item activation does not match menu id.\nThis menu:  ' + this.menu_id + '\n Event menu: ' + event_data.menu_id + ' )', 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_split_button_item', 11 );
            }
        }.bind( this ); // Must bind to this at variable definition, not in the EventBus on/off as each .bind creates a new function reference, so it wont be able to match up on delete.

        this.event_bus.on( this.component_id, 'activate_split_button_item', onActivateSplitButtonItem,TTEventBusStatics.AUTO_CLEAR_ON_EXIT  );

        let onFreezeSplitButtonActiveItem = function( event_data ) {
            if ( this.menu_id === event_data.menu_id ) {
                Debug.Text( 'Contextmenu\n SPLIT BUTTON update RECEIVED for: ' + event_data.menu_id + ':' + event_data.item_id, 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_split_button_item', 11 );
                this.freezeSplitButtonActiveItem( event_data.item_id );
            } else {
                // If the view id does not match active view, then ignore the update, as its probably the previous view.
                Debug.Warn( 'Error: Context Menu Split Button Item activation does not match menu id.\nThis menu:  ' + this.menu_id + '\n Event menu: ' + event_data.menu_id + ' )', 'TTContextMenu.vue', 'TTContextMenu', 'created:EventBus:activate_split_button_item', 11 );
            }
        }.bind( this ); // Must bind to this at variable definition, not in the EventBus on/off as each .bind creates a new function reference, so it wont be able to match up on delete.

        this.event_bus.on( this.component_id, 'freeze_split_button_active_item', onFreezeSplitButtonActiveItem, TTEventBusStatics.AUTO_CLEAR_ON_EXIT  );
    },
    unmounted() {
        Debug.Text( 'Vue context menu component unmounted ('+ this.menu_id +').', 'TTContextMenu.vue', 'TTContextMenu', 'unmounted', 10 );
        this.event_bus.autoClear();
    },
    props: {
        menu_id: String
    },
    data() {
        return {
            component_id: 'context_menu',
            built_menu: [],
            removeEventsOnUnmount: [],
            nowrap_views: [ 'TimeSheet' ], // TimeSheet is to handle the New Punch label wrapping. This is here to test if this will work globally, nowrap might cause issues.
        }
    },
    computed: {
        alignItems() {
            let filter_left = ( element ) => element.menu_align === 'left';
            let filter_center = ( element ) => element.menu_align === 'center';
            let filter_right = ( element ) => element.menu_align === 'right';

            return {
                left: this.built_menu.filter( filter_left ),
                center: this.built_menu.filter( filter_center ),
                right: this.built_menu.filter( filter_right )
            };
        },
        dynamicClasses() {
            return {
                // 'nowrap': this.nowrap_views.includes( this.viewId ) ? 'no-wrap' : '', // If this returns true, this prevents the text label of a nav button wrapping to a new-line, affecting the button heights for the whole menu. Care needs to be taken if not all buttons will fit with no-wrap.
                nowrap: 'no-wrap', // Simplifing this while we no longer pass in viewId. If this feature is needed, then we need to get viewId another way.
            }
        }
    },
    methods: {
        rebuildMenu( menu_model ) {
            this.built_menu.length = 0; // TODO: Should not need to do this once TTContextMenu has an independant instance for each view. At the moment, all views are one Vue View (LegacyView).
            this.built_menu.push(...menu_model ); // TODO: Need to handle the eventuality where icons array needs to be cleared first without losing the JS reference to the array.
        },
        /**
         * Update several item params in one go.
         * This will also be useful for event driven updates, reduces the need to have an event function for each action.
         * @param {string} item_id - ID of the item to update
         * @param {Object} new_item_attributes - The new data as attributes in an object.
         * @returns {Object}
         */
        updateMenuItem( item_id, new_item_attributes ) {
            var item = this.getMenuItemById( item_id );
            if( item === undefined ) {
                // If this happens, it might be that the Vue menu has cleared already, and the menu is trying to set states on old icons no longer present. Might be a legacy->Vue issue on a view change. But the vue menu items should always match the legacy menu on the view controller. Trace the icons and try and fix the disconnect.
                Debug.Warn( 'Menu item not found ('+ item_id +') unable to update with: ' + JSON.stringify( new_item_attributes ), 'TTContextMenu.vue', 'TTContextMenu', 'updateMenuItem', 1 );
                return false;
            }
            return Object.assign( item, new_item_attributes);
        },
        activateMultiSelectItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            if( item === undefined ) {
                // If this happens, it might be that the Vue menu has cleared already, and the menu is trying to set states on old icons no longer present. Might be a legacy->Vue issue on a view change. But the vue menu items should always match the legacy menu on the view controller. Trace the icons and try and fix the disconnect.
                Debug.Warn( 'Menu item not found ('+ item_id +') unable to update with: ' + JSON.stringify( new_item_attributes ), 'TTContextMenu.vue', 'TTContextMenu', 'updateMenuItem', 1 );
                return false;
            }
            if( item.setOnlySelfActive === undefined ) {
                item.default_active_item = true;
                return 1;
            } else {
                item.setOnlySelfActive();
                return 2;
            }
        },
        activateSplitButtonItem( item_id ) {
            for ( let i = 0; i < this.built_menu.length; i++ ) {
                //Only modify the action group that contains the item_id we want. This is to avoid affecting any other action groups active item.
                if ( this.built_menu[i].items.some(item => item.id === item_id ) ) {
                    for ( let j = 0; j < this.built_menu[i].items.length; j++ ) {
                        if ( this.built_menu[i].items[j].id === item_id ) {
                            this.built_menu[i].items[j].menu_force_active = true;
                        } else {
                            this.built_menu[i].items[j].menu_force_active = false;
                        }
                    }
                }
            }
        },
        freezeSplitButtonActiveItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            if ( item === undefined ) {
                // If this happens, it might be that the Vue menu has cleared already, and the menu is trying to set states on old icons no longer present. Might be a legacy->Vue issue on a view change. But the vue menu items should always match the legacy menu on the view controller. Trace the icons and try and fix the disconnect.
                Debug.Warn( 'Menu item not found (' + item_id + ') unable to update', 'TTContextMenu.vue', 'TTContextMenu', 'setSplitButtonTemporarilyIgnoreDisabled', 1 );
                return false;
            }
            item.freeze_active_item = true;
        },
        enableMenuItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            item.disabled = false;
        },
        disableMenuItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            item.disabled = true;
        },
        hideMenuItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            item.visible = false;

        },
        showMenuItem( item_id ) {
            var item = this.getMenuItemById( item_id );
            item.visible = true;

        },
        getMenuItemById( item_id ) {
            // TODO: Have I already done a similar function elsewhere? It seems familiar, but with ES6 array functions instead of for loops?
            // Check the context menu class as well as the left menu class. Put in Global if found.

            var result;
            function recursiveFind( haystack_array, needle_id ) {
                return haystack_array.find( element => {
                    if ( Array.isArray( element ) ) {
                        return recursiveFind( element, needle_id );
                    } else {
                        if( element.id === undefined && Array.isArray( element.items ) ) {
                            return recursiveFind( element.items, needle_id );
                        } else if( element.id === needle_id ) {
                            result = element;
                            return true;
                        }
                    }
                } );
            }
            recursiveFind( this.built_menu, item_id );

            return result;
        }
    },
    components: { TTContextButton }
};
</script>

<style>
/* Hide icons from dropdown menus of context buttons and not the buttons themselves. */
.p-menuitem-link .p-menuitem-icon {
    display: none;
}
/* Icons can be displayed in dropdowns by using the class "tticon-show-in-dropdown"
   for example: Multi-select (radio and check boxes) still need to be displayed.
*/
.p-menuitem-link .tticon-show-in-dropdown {
    display: inline;
}
</style>


<style scoped>
.context-menu-bar {
    display: flex;
    justify-content: space-between;
    /*background: #e9ecef; !* Chose #e9ecef, previously #ced4da but looked too dark once the rest of the page background is white/off-white. *!*/
    margin-bottom: 10px;
    padding-bottom: 5px; /* To balance out the top padding applied from .context-border padding. Trying 5px instead of 10px to balance out closeness of the context-border heading to the menu, but now top and bottom not equal but might be ok. */
    border-bottom: 1px solid #dbdee1;
}
.left-container,
.center-container,
.right-container {
    display: flex;
    justify-content: flex-start;
    padding: 4px; /* To match figma design */
}
.no-wrap {
    white-space: nowrap;
}
.p-menuitem-icon {
    display: none;
}
</style>
