<template>
    <component
        v-if="valid_types_for_dynamic_component.includes( parseItem.type ) && filterVisibleItems.count !== 0"
        :is="parseItem.type"
        :id="'context-button-'+parseItem.id"
        class="menu-item"
        :items="parseItem.items">
    </component>
    <li
        v-else-if="parseItem.separator === true"
        class="menu-separator">
    </li>
    <div
        v-else-if="parseItem.type === 'PrimeVueButton'"
        class="menu-item">
        <PrimeVueButton
            :label="parseItem.label"
            @click="parseItem.click_handler"
            :style="parseItem.style"
            :icon="parseItem.icon"
            :id="'context-button-'+parseItem.id"
            :disabled="parseItem.disabled">
        </PrimeVueButton>
    </div>
</template>

<script>
import PrimeVueButton from 'primevue/button';
import TTSplitButton from '@/components/context_menu/TTSplitButton';
import TTOverlayMenuButton from '@/components/context_menu/TTOverlayMenuButton';
import TTOverlayMultiSelectButton from '@/components/context_menu/TTOverlayMultiSelectButton';

export default {
    name: "TTContextButton",
    props: {
        items: Array,
    },
    data() {
        return {
            valid_types_for_dynamic_component: ['TTOverlayMenuButton', 'TTSplitButton', 'TTOverlayMultiSelectButton'],
        }
    },
    components: {
        TTOverlayMenuButton,
        PrimeVueButton,
        TTSplitButton,
        TTOverlayMultiSelectButton
    },
    computed: {
        parseItem() {
            // Make sure the returned menu type is listed in the 'vue components' object above.

            let ret_val;

            if( !this.items.length ) {
                Debug.Error( 'Invalid icon format. No length value.', 'TTContextButton.vue', 'TTContextButton', 'parseItem', 1 );
                return {
                    type: 'invalid'
                };
            }

            // Multi-select Overlay button
            if ( this.items.length > 1 && this.items[0].multi_select_group ) {
                ret_val = {
                    type: 'TTOverlayMultiSelectButton',
                    items: this.items,
                    id: this.items[0].id,
                };
            }

            // Overlay button
            else if ( this.items.length > 1 && this.items[0].action_group_header ) {
                ret_val = {
                    type: 'TTOverlayMenuButton',
                    items: this.items,
                    id: this.items[0].id,
                };
            }

            // button - split button single visible. Could also be a single item too.
            else if ( this.filterVisibleItems.count === 1 ) {
                ret_val = {
                    type: 'PrimeVueButton',
                    label: this.filterVisibleItems.items[0].label,
                    click_handler: Global.debounce( () => {
                        this.filterVisibleItems.items[0].command( this.filterVisibleItems.items[0] );
                    }, Global.calcDebounceWaitTimeBasedOnNetwork(), true ),
                    style: { 'min-width': this.filterVisibleItems.items[0].min_width + 'px' },
                    icon: this.filterVisibleItems.items[0].icon,
                    disabled: ( this.filterVisibleItems.items[0].disabled === true ),
                    id: this.filterVisibleItems.items[0].id,
                };
            }

            // split button for action groups - both useable and all items disabled.
            // Show grouped items always as SplitButton, even if only one item is enabled. This is to avoid icon sizes jumping around when the dropdown is added/removed. We cant simply add/remove padding to account for lost space, as they are different items and text would also not be in the same place/centered.
            else if ( this.filterVisibleItems.count > 1 ) {
                ret_val = {
                    type: 'TTSplitButton',
                    items: this.items,
                    id: this.items[0].id,
                };
            }

            // Deprecated. Remove after testing (29/04/2021) This should now no longer be needed, button handler above should work for all required cases.
            // actual single button - likely disabled
            // else if ( this.items.length === 1 && this.items[0].visible !== false ) {
            //     ret_val = {
            //         type: 'PrimeVueButton',
            //         label: this.items[0].label,
            //         click_handler: () => this.items[0].command( this.items[0].id ),
            //         style: { 'min-width': this.items[0].min_width + 'px' },
            //         disabled: (this.items[0].disabled === true ),
            //     };
            // }

            // fallback - no matches - could just not be visible.
            else {
                Debug.Text( 'No menu type criteria matched. Could be invisible menu item.', 'TTContextButton.vue', 'TTContextButton', 'parseItem', 11 );
                // console.log( '', this.items );
                ret_val = {
                    type: 'no-match'
                };
            }

            return ret_val;
        },
        filterVisibleItems() {
            var items = this.items;
            var visible = ( element ) => element.visible !== false;
            var not_group_header = ( element ) => element.action_group_header !== true;
            var not_single_separator = ( element ) => element.separator !== true;
            var filtered_items = items.filter( visible ).filter( not_group_header ).filter( not_single_separator );
            return {
                items: filtered_items,
                count: filtered_items.length
            };
        }
    },
    methods: {
    }
};
</script>
<!-- To fix the Attendance->TimeSheet wrapping on New Punch, you can apply white-space: nowrap; to the p-button-label -->
<style scoped>
.menu-item {
    /*flex: 1 1 0; !* Uncomment to spread out the buttons at equal sized. This together with the styles in TTContextMenu.vue will make all the buttons the same size, regardless of internal padding *!*/
    /*margin-top: 5px;*/ /* Removed the top padding to make it more balanced until we decide how these buttons will look, as bottom padding cannot be applied simply, due to it looking odd when an edit view is open. */
    margin-left: 5px;
}
.menu-item:first-child {
    margin-left: 0; /* outer padding controlled by overall .context-menu-bar */
}
::v-deep(.p-button) {
    height: 100%; /* To ensure the max available height is used, and then all menu buttons will be same height (without this, splitbutton is taller than single button) */
    background: #fff; /* Previously #f8f9fa but the contrast against the background was not great. */
    color: #32689b; /* This will also set the icon colour to this. */
    border-color: #e1e1e1; /* add outline to the vue context menu buttons. improves splitbutton visual. */
    padding: 0.4rem 0.6rem; /* To match figma design */
}
::v-deep(.p-button .p-button-label) {
    color: #3b3b3b;
    text-align: left; /* Edit button label looks odd if centered with the extra width for mass edit. left align looks better. */
}
::v-deep(.p-button:enabled:hover .p-button-label) {
    color: #fff;
}
::v-deep(.p-button):disabled {
    opacity: .4; /* Previously 0.6 same as .p-component:disabled, but the contrast against enabled and contextmenu background was not good enough. */
    color: #cacaca;
    border-color: #cacaca;
}
/* Works together with the dynamic class for item.action_group_id set in TTContextMenu.vue Perhaps refactor to be JS based with a v-if */
/*.sub-view .menu-item.left-cancelIcon,*/
/*.sub-view .menu-item.center-cancelIcon,*/
/*.sub-view .menu-item.right-cancelIcon {*/
/*    display: none; !* Cancel button is not applicable on subview menus *!*/
/*}*/
</style>
