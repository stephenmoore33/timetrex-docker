<template>
    <div class="tt-overlaymenu" v-if="items[0].visible !== false && items.length > 1">
        <Menu ref="menu" :model="wrapped_items_without_header" :popup="true" appendTo="body"/>
        <Button class="menu-button" type="button" :label="items[0].label" :icon="!items[0].label ? items[0].icon : 'pi pi-chevron-down'" iconPos="right" @click="toggleMenu"/>
    </div>
</template>

<script>
/**
 * This is a special version of the split-button menu, using the overlay menu, which has a header instead of the currently 'active' action button.
 * So where the click of a 'Save & Next' button in a menu will swap the active button to Save & Next, clicking a menu item in a Navigation menu, will leave the label as Navigation.
 * As there is no 'active action' the label and menu open button are one button, instead of split.
 * There are two ways of making an action group menu with a header.
 * 1) Define a header in the getCustomContextMenuModel function or similar, setting action_group and action_group_header. Like TimeSheet Navigation menu.
 * 2) Define a single object in getCustomContextMenuModel, setting the action_group and action_group_header, and then including an 'items' array containing your sub menu items. Like TimeSheet Print menu.
 * Having 2 options is more complicated, but its there to stay compatible with the legacy context menu. Option 1 is needed to stay compatible with the Save, Delete, and Navigation groups. And Option 2 is needed for the way the old menu defined items like the Print menu.
 * Once the old menu and format is gone, we can refactor to simplify this behaviour.
 * Note: permission_result: false is needed on the header items to make sure the old menu ignores this item.
 * Note: the "v-if && items.length > 1" portion above is to ensure that the menu button is hidden if the items list contains only the header and nothing else. Hence the more than 1 criteria.
 */

import Menu from 'primevue/menu';
import Button from 'primevue/button';

export default {
    name: "TTOverlayMenuButton",
    props: {
        items: [Array, Object],
    },
    computed: {
        wrapped_items_without_header() {
            return this.wrapItemCommands(this.items).slice(1); // We slice 1st item away so that the header item is not included. TODO: This means visible: false is no longer needed on the header, remove this from all code.
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
                // The below is technically a Vue anti-pattern, as we are in effect also modifying the parent data.
                // Normally data goes parent => child components, and events go child => parent. And then its up to the parent to handle that event to update data where needed.
                // But in this case, we just want to manipulate data before it goes further down the line, but due to JS pass by reference, the parent gets updated here too.
                // Perhaps fix this later on, after a Proof of concept for the SplitButtons has been achieved.

                // Creating a shallow copy of the item to avoid mutating the parent data.
                // This is important to prevent side effects in Vue's reactivity system and infinite recursion with computed properties.
                let new_item = { ...item };

                var original_command = item.command;
                new_item.command = Global.debounce( () => {
                    // Intercept the item click before running the original command as normal.
                    original_command( item.id ); // trigger the original command, and pass in the item.id of the button icon. TODO: item.id is non-generic logic. Not ideal for a 'dumb' component.
                }, Global.calcDebounceWaitTimeBasedOnNetwork(), true );
                return new_item;
            } );
            return new_array;
        },
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
