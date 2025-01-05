<template>
    <div class="tt-menu-search-container">
        <AutoComplete class="tt-menu-search" placeholder="Search Menu" v-model="terms" :suggestions="searchSuggestions"
                      optionGroupLabel="label" optionGroupChildren="items"
                      @complete="searchMenu($event)" @item-select="selectItem($event)" field="label" :minLength="minLength"/>
    </div>
    <div class="search-separator"></div>
</template>
<script>
import AutoComplete from 'primevue/autocomplete';

export default {
    name: 'TTMenuSearch',
    props: {
        model: Array,
        search: String,
    },
    data() {
        return {
            terms: null,
            searchSuggestions: null,
            minLength: 2
        };
    },
    methods: {
        searchMenu( event ) {
            let findItems = ( items, suggestions, group_header ) => {
                let filtered_items = [];
                for ( let i = 0; i < items.length; i++ ) {
                    //If item is a valid menu link compare the label to the search term and add to filtered items if matches.
                    if ( items[i].parent_id && items[i].command && items[i].label && items[i].label.toLowerCase().includes( event.query.toLowerCase() ) ) {
                        filtered_items.push( items[i] );
                    }
                    //If item is a header and has child items, recursively search child items.
                    if ( items[i].items && items[i].items.length > 0 ) {
                        suggestions = findItems( items[i].items, suggestions, items[i].label );
                    }
                    //Finally add all items from that group to the suggestions by the group header.
                    if ( i === items.length - 1 && filtered_items.length > 0 && group_header ) {
                        suggestions.push( { label: group_header, items: filtered_items } );
                    }
                }
                return suggestions;
            };

            this.searchSuggestions = findItems( this.model, [] );
        },
        selectItem( event ) {
            event.value.command();
        },
    },
    components: {
        AutoComplete
    }
};
</script>
<style scoped>
.tt-menu-search-container {
    margin: 5px;
    width: 100%;
}

.search-separator {
    border-top: 1px solid #dee2e6;
}

::v-deep(.tt-menu-search) {
    width: calc(100% - 10px);
}

::v-deep(.p-inputtext) {
    width: 100%;
}
</style>
