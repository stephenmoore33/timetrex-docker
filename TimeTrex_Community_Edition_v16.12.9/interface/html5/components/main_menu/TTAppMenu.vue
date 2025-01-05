<template>
	<AppSubmenu ref="appSubMenu" id="main-menu" class="layout-menu layout-main-menu clearfix"
                :model="model"
                :layoutMode="layoutMode"
                :menuActive="active"
                :root="false"
                :parentMenuItemActive="true"
                :mobileMenuActive="mobileMenuActive"/>
</template>

<script>
import AppSubmenu from '@/framework/apollo-vue/src/layout/AppSubMenu.vue';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
        this.event_bus.on( this.component_id, 'set_active_index', ( event_data ) => {
            this.$refs.appSubMenu.activeIndex = event_data.index;
        } );
    },
    data() {
        return {
            component_id: 'app_menu',
        }
    },
	props: {
		model: Array,
		layoutMode: String,
		active: Boolean,
        mobileMenuActive: Boolean
	},
	components: {
		'AppSubmenu': AppSubmenu
	}
}
</script>

<style scoped>

</style>