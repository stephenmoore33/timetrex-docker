<template>
    <SplitButton :label="active_item.label" :icon="active_item.icon" :model="parsed_items" @click="active_item.command" :disable_item="active_item.disabled" :disable_menu="disable_menu"></SplitButton>
</template>

<script>
import SplitButton from './PrimeVueSplitButton'; // Moved SplitButton into TT codebase as we needed to adjust the disabled button states.

export default {
    name: "TTSplitButton",
    props: {
        items: Array
    },
    data() {
        return {
            disable_active_toggle: false, // If true, then active_item_index will not change upon menu selection. Likely used in conjunction with force_active_index.
            active_item_index: 0,
        }
    },
    computed: {
        disable_menu() {
            // FIX: Menu must be disabled independantly via computed disable_menu, as previously it was done in firstEnabledItemIndex function which was not always called on data updates if first item was enabled.
            if( this.firstEnabledItemIndex === -1 ) {
                // -1 means no enabled items found, so lets disable the whole menu.
                return true;
            } else {
                // At least one enabled item found, enable the menu dropdown arrow.
                return false;
            }
        },
        parsed_items() {
            // Converted this to a computed function from data, to ensure its recalculated. If this does not solve context icons updating, then look into Vue.set
            // This should re-run each time the data changes
            return this.parseItemArrayCommands(this.items);
        },
        active_item() {
            // Active item logic has been modified to be overridden in certain situations like Export/Import.
            // You can choose to force the active item default by setting menu_force_active to true on the item definition.
            // You can also choose to disable the active item toggling when an option is selected from the menu. By default, if the above flag is used, toggling will also be disabled. Adjust this below.

            var force_active_item_index = this.findForceActiveItemIndex();
            if( force_active_item_index !== -1 ) {
                // !== -1 means it found a menu_force_active flag
                this.disable_active_toggle = true; // For now we will also disable toggle behaviour as this is the only need for this combo. Remove this line if you only want to set default but allow toggle changes.
                this.active_item_index = force_active_item_index;
            } else {
                // -1 means no menu_force_active flag was found on any of the items, so lets find the first enabled item to make active.
                if ( this.items[this.active_item_index].disabled === true && !this.items[this.active_item_index].freeze_active_item ) {
                    if ( this.firstEnabledItemIndex === -1 ) {
                        // No enabled items found, return 0 to label the menu by the first item in the array. Menu will be disabled via computed disable_menu function.
                        this.active_item_index = 0;
                    } else {
                        // Successfully found the first enabled item.
                        this.active_item_index = this.firstEnabledItemIndex;
                    }
                }
            }

            //Button can be temporarily set to freeze the active item. This stops the active split button item from resetting.
            //Example if doing "Save & Continue" we do not want the button to switch back to "Save" just because validation failed.
            //Being reset here as we only want the freeze temporarily. If in the future this causes issues, the setting of false could be moved
            //elsewhere to prevent any race conditions with order of events - although there are none I am aware of currently.
            this.items[this.active_item_index].freeze_active_item = false;

            return {
                label: this.getParsedLabel( this.active_item_index ),
                icon: this.getParsedIcon( this.active_item_index ),
                command: this.getParsedCommand( this.active_item_index ),
                disabled: this.getParsedDisabledState( this.active_item_index ),
            };
        },
        firstEnabledItemIndex() {
            // Find first enabled item in array. In most cases, should be 0, but not if item zero happens to be disabled, as it would end up being shown as enabled.
            // If index=null, then this is on first load, so calculate which item should be the active one.

            var enabled = ( element ) => element.disabled !== true;
            var find_result = this.items.findIndex( enabled );

            return find_result;
        },
    },
    methods: {
        parseItemArrayCommands( items_array ) {
            // Have to do this, currently see no neater way to detect when a button menu item has been clicked.
            let new_array = items_array.map((item, item_index) => {
                // The below is technically a Vue anti-pattern, as we are in effect also modifying the parent data.
                // Normally data goes parent => child components, and events go child => parent. And then its up to the parent to handle that event to update data where needed.
                // But in this case, we just want to manipulate data before it goes further down the line, but due to JS pass by reference, the parent gets updated here too.
                // Perhaps fix this later on, after a Proof of concept for the SplitButtons has been achieved.

                // Creating a shallow copy of the item to avoid mutating the parent data.
                // This is important to prevent side effects in Vue's reactivity system and infinite recursion with computed properties.
                let new_item = { ...item };

                let original_command = item.command;
                new_item.command = Global.debounce( () => {
                    // Intercept the item click before running the original command as normal.
                    this.updateActiveButton( item_index );
                    original_command( item ); // trigger the original command, and pass in the item.id of the button icon. TODO: item.id is non-generic logic. Not ideal for a 'dumb' component.
                }, Global.calcDebounceWaitTimeBasedOnNetwork(), true );
                return new_item;
            });
            return new_array;
        },
        getParsedCommand( index ) {
            return this.parsed_items[ index ].command;
        },
        getParsedLabel( index ) {
            return this.parsed_items[ index ].label;
        },
        getParsedIcon( index ) {
            return this.parsed_items[ index ].icon;
        },
        getParsedDisabledState( index ) {
            return this.parsed_items[ index ].disabled;
        },
        updateActiveButton( new_active_item_index ) {
            if ( this.disable_active_toggle === false ) {
                // Only update the active button to last action if active toggle is not disabled.
                this.active_item_index = new_active_item_index;
                for ( let i = 0; i < this.items.length; i++ ) {
                    //Setting split_button_active_item so that from other areas of code we can know if the user selected an item.
                    //This is currently used doing error validation so we can check if a user selected an item and freeze it.
                    //For example if doing "Save & Continue" and validation error happens, we do not want to switch
                    //the button back to to "Save". But that would happen as the item is disabled while validation fails.
                    if ( i === this.active_item_index ) {
                        this.items[i].split_button_active_item = true;
                    } else {
                        this.items[i].split_button_active_item = false;
                    }
                }
            }
        },
        findForceActiveItemIndex() {
            var menu_force_active = ( element ) => element.menu_force_active === true;
            var filtered_items = this.items.filter( menu_force_active );
            var first_index = this.items.findIndex( menu_force_active );

            if( filtered_items.length === 1 ) {
                return first_index;
            } else if( filtered_items.length > 1 ) {
                Debug.Error( 'Error: More than one Force Active flag found, defaulting to normal splitbutton behaviour.', 'TTSplitButton.vue', 'TTSplitButton', 'findForceActiveItem', 1 );
                return -1;
            } else {
                // No force active flags found, treat as normal item.
                return -1;
            }
        }
    },
    components: {
        SplitButton
    }
};
</script>

<style scoped>
</style>
