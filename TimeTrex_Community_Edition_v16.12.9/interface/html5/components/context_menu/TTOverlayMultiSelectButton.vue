<template>
    <div class="tt-overlay-multi-select-menu" v-if="items.some( item => item.visible === true || item.visible === undefined )" :disabled="items.every( item => item.disabled === true )">
        <Menu ref="menu" :model="wrapped_items" :popup="true" appendTo="body"/>
        <Button class="menu-button" type="button" :label="get_menu_label" :icon="!showGroupLabel() ? items[0].vue_icon : 'pi pi-chevron-down'" iconPos="right" @click="toggleMenu"/>
    </div>
</template>

<script>

import Menu from 'primevue/menu';
import Button from 'primevue/button';

export default {
    name: "TTOverlayMultiSelectButton",
    props: {
        items: Array,
    },
    computed: {
        wrapped_items() {
            return this.wrapItemCommands(this.items);
        },
        get_menu_label() {

            if ( !this.showGroupLabel() ) {
                return '';
            }

            // Filter out all active items, and if none, then set the default as shown in the last line (action_group id).
            return this.items
                .filter(( element ) => element.active )
                .map(( element ) => element.label)
                .join(', ')
                || this.items[0].action_group; // Sets default label as action_group for fallback. Can't simply be first item in array, as the view controller data wont match this.
        }
    },
    methods: {
        toggleMenu(event) {
            this.$refs.menu.toggle(event);
        },
        wrapItemCommands( items_array ) {
            // TODO: Consolidate this with the function in TTSplitButton (maybe in TTContextButton), and the command function in ContextMenuManager.convertBackBoneMenuModelToPrimeVue so that we are not parsing the command functions in more than one place.
            // Have to do this, currently see no quick way to detect when a button menu item has been clicked.
            var new_array = items_array.map((item, item_index) => {

                if( item.active === true ) {
                    this.setItemActive( item );
                } else {
                    this.setItemInactive( item ); // Set all default item states to inactive, so that they get the correct active false flag, but also the correct inactive icon class to display in the menu.
                }

                // Copied from TTOverlayButton.vue
                // The below is technically a Vue anti-pattern, as we are in effect also modifying the parent data.
                // Normally data goes parent => child components, and events go child => parent. And then its up to the parent to handle that event to update data where needed.
                // But in this case, we just want to manipulate data before it goes further down the line, but due to JS pass by reference, the parent gets updated here too.
                // Perhaps fix this later on, after a Proof of concept for the SplitButtons has been achieved.

                //this.onSelectionClick was being set and triggered multiple times.
                //This caused some buttons such as Schedule -> Drag & Drop: Overwrite to not trigger properly until user clicked multiple times.
                //For now just making sure not to set the same command multiple times.
                if ( !item.command_set ) {
                    var original_command = item.command;
                    item.command = Global.debounce( () => {
                        this.onSelectionClick( item );
                        original_command( item.id ); // trigger the original command, and pass in the item.id of the button icon. TODO: item.id is non-generic logic. Not ideal for a 'dumb' component.
                    }, Global.calcDebounceWaitTimeBasedOnNetwork(), true );
                    item.command_set = true;
                }

                // add a setActive and setDeactivated command which can be called from parent components, that sets itself active, and other options inactive. Alternatively could also be done as a child to parent emit, but then harder to tie into EventBus events.
                item.setOnlySelfActive = () => {
                    this.setOnlyOneActive( item );
                }
                item.setOnlySelfDeactivated = () => {
                    this.setOnlyOneDeactivated( item );
                }

                // check if there is an existing flag for default active item.
                if( item.default_active_item ) {
                    item.setOnlySelfActive();
                }

                return item;
            });
            return new_array;
        },
        onSelectionClick( item ) {
            // Check if the number of items in the group equals one. If so, then just toggle states.
            if ( this.isSingleGroupItem( item ) ) {
                this.toggleActive( item );
            } else {
                // We want to ensure only one active item at a time.
                this.setOnlyOneActive( item );
            }
        },
        toggleActive( item ) {
            if ( item.active ) {
                this.setItemInactive( item )
            } else {
                this.setItemActive( item );
            }
        },
        setOnlyOneActive( item ) {
            this.items.map(( element ) => {
                // Ensure we only clear the ones from the same group.
                if( element.multi_select_group === item.multi_select_group ) {
                    this.setItemInactive( element );
                }
            });
            this.setItemActive( item );
        },
        setOnlyOneDeactivated( item ) {
            this.setItemInactive( item );
        },
        setItemActive( item ) {
            if ( this.isSingleGroupItem( item ) ) {
                item.icon = 'tticon tticon-show-in-dropdown tticon-check_box_black_24dp ';
            } else {
                item.icon = 'tticon tticon-show-in-dropdown tticon-radio_button_checked_black_24dp';
            }
            item.active = true;
        },
        setItemInactive( item ) {
            if ( this.isSingleGroupItem( item ) ) {
                item.icon = 'tticon tticon-show-in-dropdown tticon-check_box_outline_blank_black_24dp ';
            } else {
                item.icon = 'tticon tticon-show-in-dropdown tticon-radio_button_unchecked_black_24dp';
            }
            item.active = false;
        },
        getItemById( find_item_id ) {
            return this.items.find( element => element.id === find_item_id );
        },
        isSingleGroupItem( item ) {
            //Check if select belongs to a group or is a Single-selection. Single-selection is displayed as a checkbox.
            var group_count = this.items.filter( ( element ) => element.multi_select_group === item.multi_select_group ).length;
            return group_count === 1;
        },
        showGroupLabel() {
            if ( this.items.some( item => item.no_group_label ) ) {
                return false;
            }
            return true;
        }
    },
    components: {
        Menu,
        Button
    }
};
</script>

<style scoped>
.tt-overlaymenu { /* Copy the p-splitbutton CSS to allow in-line display */
    display: inline-flex;
}
.menu-button {
    width: 100%;
}
</style>
