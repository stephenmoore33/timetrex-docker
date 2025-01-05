<template>
    <div class="tt-top layout-topbar">
        <TTTopBar v-if="ready_to_load_top_bar"
                  :topbarMenuActive="topbarMenuActive"
                  :activeTopbarItem="activeTopbarItem"
                  @menubutton-click="onMenuButtonClick"
                  @topbar-menubutton-click="onTopbarMenuButtonClick"
                  @topbar-item-click="onTopbarItemClick"></TTTopBar>
    </div>
</template>
<script>
import TTTopBar from '@/components/TTTopbar';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
        this.event_bus.on( this.component_id, 'ready_to_load_top_bar', ( event_data ) => {
            this.ready_to_load_top_bar = true;
        } );
    },
    data() {
        return {
            component_id: 'tt_top_container',
            ready_to_load_top_bar: false,
        };
    },
    props: {
        topbarMenuActive: Boolean,
        activeTopbarItem: String
    },
    methods: {
        onMenuButtonClick( event ) {
            this.$emit( 'menubutton-click', event );
        },
        onTopbarMenuButtonClick( event ) {
            this.$emit( 'topbar-menubutton-click', event );
        },
        onTopbarItemClick( event ) {
            this.$emit( 'topbar-item-click', event );
        }
    },
    components: {
        TTTopBar: TTTopBar,
    }
};
</script>
<style scoped>
.layout-topbar {
    height: 50px;
    margin-bottom: 0;
    /*padding: 16px 20px 0 20px;*/
    /*background: #32679b;*/
}

.tt-top {
    width: 100%;
}
</style>
