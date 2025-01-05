<template>
    <div v-show="showUI" :class="containerClass" @click="onDocumentClick">
        <Toast/>
        <TTTopContainer class="hide-in-login"
                        :topbarMenuActive="topbarMenuActive"
                        :activeTopbarItem="activeTopbarItem"
                        @menubutton-click="onMenuButtonClick"
                        @topbar-menubutton-click="onTopbarMenuButtonClick"
                        @topbar-item-click="onTopbarItemClick"/>
        <div class="layout-content">
            <div class="layout-sidebar hide-in-login" ref="sidebar">
                <TTLeftContainer class="hide-in-login"
                                 :isMenuVisible="isMenuVisible()"
                                 :layoutMode="layoutMode"
                                 @menuitem-click="onMenuItemClick"/>
            </div>
            <div class="layout-content-wrapper">
                <div class="layout-content-container">
                    <!--        <router-view></router-view>router-view-->
                    <router-view :key="$route.fullPath"></router-view> <!-- The router view is setup with this :key to allow the LegacyView component to be re-used instead of cached, ensuring unique component state for each view. -->
                </div>
                <div v-if="staticMenuMobileActive" class="layout-mask"></div>
                <div id="tt-assistant-ui"></div>
            </div>
        </div>
    </div>
</template>
<script>
// TODO: Bug: Noticed on a refresh that the main logo in top left flashes with the Apollo logo before showing the company logo.

import TTTopContainer from '@/components/TTTopContainer';
import TTLeftContainer from '@/components/TTLeftContainer';
import { useLayout } from '@/framework/apollo-vue/src/layout/composables/layout';
const { layoutConfig, layoutState, isSidebarActive } = useLayout();

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id
        } );

        this.event_bus.on( 'global', 'reset_vue_data', this.resetData );
        this.event_bus.on( this.component_id, 'get_user_saved_layout_mode', this.getSavedLayoutMode );
        this.event_bus.on( this.component_id, 'toggle_ui', this.getSavedLayoutMode );
    },
    data() {
        return {
            component_id: 'tt_main_ui',
            savedLayoutId: '',
            showUI: true,
            topbarMenuActive: false,
            activeTopbarItem: null,
            menuActive: false,
            window_width: window.innerWidth,
            previous_layout_mode: null
        };
    },
    methods: {
        resetData() {
            Object.assign( this.$data, this.$options.data() );
        },
        onDocumentClick( event ) {
            if ( event.target.classList.contains( 'menu-button' ) || event.target.classList.contains( 'pi-bars' ) ) {
                return;
            }

            if ( !this.topbarItemClick ) {
                this.activeTopbarItem = null;
                this.topbarMenuActive = false;
            }

            if ( this.isOutsideClicked( event ) && ( this.isHorizontal() || this.isSlim() || this.isOverlay() ) ) {
                layoutState.overlayMenuActive.value = false;
                layoutState.overlaySubmenuActive.value = false;
                layoutState.staticMenuMobileActive.value = false;
                layoutState.menuHoverActive.value = false;

                this.hideOverlayMenu();
            }

            this.topbarItemClick = false;
            this.menuClick = false;
        },
        isOutsideClicked( event ) {
            return !this.$refs.sidebar.contains( event?.target );
        },
        onMenuClick() {
            this.menuClick = true;
        },
        onMenuButtonClick( event ) {
            this.menuClick = true;
            this.topbarMenuActive = false;

            if ( layoutConfig.menuMode.value === 'overlay' ) {
                layoutState.overlayMenuActive.value = !layoutState.overlayMenuActive.value;
                layoutState.staticMenuMobileActive.value = layoutState.overlayMenuActive.value;
            } else {
                if ( this.isLargeWidth() ) {
                    // layoutState.staticMenuDesktopInactive.value = !layoutState.staticMenuDesktopInactive.value;
                    //Close open menus when switching menu layout mode.
                    this.event_bus.emit( 'app_menu', 'set_active_index', {
                        index: null
                    } );
                    if ( layoutConfig.menuMode.value === 'slim' ) {
                        //In slim mode we offset submenus to prevent the menu from going off screen.
                        //This needs to be reset when switching from slim so that the changes do not carry over to layout modes.
                        this.event_bus.emit( 'tt_left_container', 'reset_slim_offsets' );
                    }
                    // Override PrimeVue behaviour to use burger button to toggle menu between static, slim and horizontal layouts.
                    layoutConfig.menuMode.value = layoutConfig.menuMode.value === 'static' ? 'slim' : layoutConfig.menuMode.value === 'slim' ? 'horizontal' : 'static';
                    this.saveLayoutMode( layoutConfig.menuMode.value );
                } else {
                    layoutState.staticMenuMobileActive.value = !layoutState.staticMenuMobileActive.value;
                }
            }

            this.dispatchResizeEvent();
            event.preventDefault();
        },
        rebuildMenu() {
            if ( LocalCacheData.getLoginUser() === null ) {
                //We cannot rebuild the menu if the user is not logged in yet, especially due to LocalCacheData.getRequiredLocalCache throwing exceptions causing infinite browser reloads as company and other data is checked when rebuilding the menu.
                return;
            }

            //Without this delay the slim menu does not build correctly.
            setTimeout( () => {
                this.event_bus.emit( 'tt_left_container', 'rebuild_menu' );
            }, 15 );
        },
        onTopbarMenuButtonClick( event ) {
            this.topbarItemClick = true;
            this.topbarMenuActive = !this.topbarMenuActive;
            this.hideOverlayMenu();
            event.preventDefault();
        },
        onTopbarItemClick( event ) {
            this.topbarItemClick = true;

            if ( this.activeTopbarItem === event.item )
                this.activeTopbarItem = null;
            else
                this.activeTopbarItem = event.item;

            event.originalEvent.preventDefault();
        },
        isLargeWidth() {
            return window.innerWidth >= 1131;
        },
        isSmallWidth() {
            return window.innerWidth <= 1130;
        },
        isHorizontal() {
            return layoutConfig.menuMode.value === 'horizontal';
        },
        isSlim() {
            return layoutConfig.menuMode.value === 'slim';
        },
        isOverlay() {
            return layoutConfig.menuMode.value === 'overlay';
        },
        hideOverlayMenu() {
            layoutState.overlayMenuActive.value = false;
            layoutState.staticMenuMobileActive.value = false;
        },
        onMenuItemClick( event ) {
            if ( !event.item.items ) {
                this.event_bus.emit( this.component_id, 'reset-active-index' );
                this.hideOverlayMenu();
            } else {
                this.menuClick = true;
            }
            if ( !event.item.items && ( this.isHorizontal() || this.isSlim() || this.isOverlay() ) ) {
                this.menuActive = false;
            }
        },
        onRootMenuItemClick() {
            this.menuActive = !this.menuActive;
        },
        isMenuVisible() {
            if ( this.isLargeWidth() ) {
                if ( layoutConfig.menuMode.value === 'static' ) {
                    // return !layoutState.staticMenuDesktopInactive.value;
                    return true; // Always want to show the contents of the left menu, so that when it slides in/out, the menu doesn't just flash away, but properly slides away with the container.
                } else if ( layoutConfig.menuMode.value === 'overlay' )
                    return layoutState.overlayMenuActive.value;
                else
                    return true;
            } else {
                return true;
            }
        },
        toggleConfigurator() {
            this.configuratorActive = !this.configuratorActive;
        },
        hideConfigurator() {
            this.configuratorActive = false;
        },
        resizeContentLayout() {
            //Need to resize main content area to account for height changes when horizontal bar stacks on lower widths.
            if ( this.isHorizontal() ) {
                setTimeout( () => {
                    let content_container = document.querySelector( '.content-container-after-login' );
                    if ( !content_container ) {
                        return;
                    }
                    //Horizontal menu is only shown on desktop resolutions (1024px+).
                    if ( this.isLargeWidth() ) {
                        let height = window.innerHeight - document.querySelector( '.layout-menu-container ' ).getBoundingClientRect().bottom;
                        content_container.style.height = height + 'px';
                    } else {
                        let height = window.innerHeight - document.querySelector( '.layout-topbar ' ).getBoundingClientRect().bottom;
                        content_container.style.height = height + 'px';
                    }
                }, 15 );
            }

            this.window_width = window.innerWidth;
        },
        dispatchResizeEvent() {
            //Trigger resize events such as dashlet resizing on homeview.
            //Delay used to trigger event after layout change to help prevent issues.
            setTimeout( () => {
                window.dispatchEvent( new Event( 'resize' ) );
            }, 15 );
        },
        saveLayoutMode( layoutMode ) {
            let args = {};
            args.script = 'global_main_menu';
            args.name = 'settings';
            args.is_default = false;
            args.data = {};
            args.data.layout_mode = layoutMode;

            this.previous_layout_mode = layoutConfig.menuMode.value;

            if ( this.savedLayoutId ) { //If we have a saved layout id, then the api call is an update and not a create.
                args.id = this.savedLayoutId;
            }

            TTAPI.APIUserGenericData.setUserGenericData( args, {
                onResult: function( res ) {
                    if ( !res.isValid() ) {
                        Debug.Error( 'Error: Saving user layout mode selectiin failed', 'TTMainUI.vue', 'TTMainUI', 'saveLayoutMode', 10 );
                    }
                }
            } );

            layoutConfig.menuMode.value = layoutMode;

            this.resizeContentLayout();
        },
        getSavedLayoutMode() {
            let $this = this;
            let filter_data = {};
            filter_data.script = 'global_main_menu';
            filter_data.name = 'settings';
            filter_data.deleted = false;

            TTAPI.APIUserGenericData.getUserGenericData( { filter_data: filter_data }, {
                onResult: ( results ) => {
                    var result_data = results.getResult();
                    if ( result_data && result_data.length > 0 ) {
                        if ( result_data[0].data.layout_mode ) {
                            layoutConfig.menuMode.value = result_data[0].data.layout_mode;
                            this.previous_layout_mode = layoutConfig.menuMode.value;
                            $this.savedLayoutId = result_data[0].id;
                        }
                    }

                    //Do not show main ui until user layout mode selection retrieved. Otherwise page contents would briefly
                    //shift around going from default width of the static menu to the other layout modes.
                    $this.toggleUI( true );
                    $this.resizeContentLayout();
                }
            } );
        },
        toggleUI( show ) {
            this.showUI = show;
        },
    },
    computed: {
        containerClass() {
            return ['layout-container', 'layout-light', {
                'layout-light': layoutConfig.colorScheme.value === 'light',
                'layout-dim': layoutConfig.colorScheme.value === 'dim',
                'layout-dark': layoutConfig.colorScheme.value === 'dark',
                'layout-colorscheme-menu': layoutConfig.menuTheme.value === 'colorScheme',
                'layout-primarycolor-menu': layoutConfig.menuTheme.value === 'primaryColor',
                'layout-transparent-menu': layoutConfig.menuTheme.value === 'transparent',
                'layout-overlay': layoutConfig.menuMode.value === 'overlay',
                'layout-static': layoutConfig.menuMode.value === 'static',
                'layout-slim': layoutConfig.menuMode.value === 'slim',
                'layout-slim-plus': layoutConfig.menuMode.value === 'slim-plus',
                'layout-horizontal': layoutConfig.menuMode.value === 'horizontal',
                'layout-reveal': layoutConfig.menuMode.value === 'reveal',
                'layout-drawer': layoutConfig.menuMode.value === 'drawer',
                'layout-static-inactive': layoutState.staticMenuDesktopInactive.value && layoutConfig.menuMode.value === 'static',
                'layout-overlay-active': layoutState.overlayMenuActive.value,
                'layout-mobile-active': layoutState.staticMenuMobileActive.value,
                // 'p-input-filled': $primevue.config.inputStyle === 'filled',
                // 'p-ripple-disabled': $primevue.config.ripple === false,
                'layout-sidebar-active': layoutState.sidebarActive.value,
                'layout-sidebar-anchored': layoutState.anchored.value,
                'p-input-filled': this.$primevue.config.inputStyle === 'filled',
                'p-ripple-disabled': this.$primevue.config.ripple === false
            }];
        },
        layoutMode() {
            if ( this.window_width <= 1130 ) {
                layoutConfig.menuMode.value = 'overlay';
                this.rebuildMenu();
            } else if ( layoutConfig.menuMode.value === 'overlay' ) {
                layoutConfig.menuMode.value = this.previous_layout_mode;
                this.rebuildMenu();
            }

            return layoutConfig.menuMode.value;
        },
        staticMenuMobileActive() {
            return layoutState.staticMenuMobileActive.value;
        }
    },
    components: {
        TTTopContainer: TTTopContainer,
        TTLeftContainer: TTLeftContainer
    }
};
</script>
<style>
.login-bg .hide-in-login {
    display: none;
}

.layout-container.layout-static .layout-content {
    padding-top: 0 !important; /* This is just TEMP to remove the padding for unused breadcrumb. */
}

.bottom-container {
    margin-left: 245px; /* TODO this needs to be adjusted. But this footer will likely go into the left menu bottom. */
}

.layout-container .layout-content .layout-content-container {
    display: flex; /* To accompany the flex: 1 1 0 coming from PrimeVue styling. This will correctly align the child components. */
    padding: 0; /* Remove padding around the main content area. Conflicting with apollo theming. */
}

/* TODO: Possibly temp styles below. Doing this for horizontal menu mode */
.layout-container.layout-horizontal .layout-content {
    padding-top: 1px;
}

@media (min-width: 1024px) {
    .layout-container.layout-horizontal .layout-menu-container {
        position: static;
        top: auto;
        left: auto;
    }
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu > li > a {
    height: 40px; /* To override the 50px forced height in PrimeVue, which makes the menu taller than it needs to be. */
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu > li.active-menuitem > ul {
    top: 40px; /* To match to the adjusted height above of .layout-content */
    z-index: 100; /* To ensure the dropdown menu items are always on top of edit views. */
}

.layout-container.layout-slim .layout-content {
    padding-top: 0;
    margin-left: 42px; /* Match new slim menu width. */
}

.layout-container.layout-slim .layout-menu-container .layout-menu > li > ul {
    left: 42px; /* Match new slim menu width. TODO: Best to update this in the primevue scss source file instead, but dont want to do that until we have to import the whole menu code, as it complicated upgrade steps upon new versions. */
}

.layout-container.layout-static .layout-menu-container {
    overflow: visible;
}

/* TODO: temp to move config menu button out of the way */
.layout-config .layout-config-content .layout-config-button {
    top: initial;
    bottom: 100px;
    opacity: 0.8;
}

/* Color overrides. Should eventually go into the SCSS sources */
.layout-container .topbar {
    background-color: #32689b;
}

#tt_main_ui .layout-container .topbar .menu-button {
    margin-left: 0;
    margin-right: 0;
    width: 42px;
}

/* Hide bottom red border, as everything in here should now have moved to the left menu. Note, this also affects the pagination controls at the bottom. */
/* This may need to be expanded to remove the html grid-bottom-border and bottom-div */
.view .bottom-div,
.edit-view .grid-bottom-border, .view .grid-bottom-border,
.grid-bottom-border {
    display: none;
    background: none;
    height: 0;
}

/* Sub Views need bottom paging div to be visible. */
.view.sub-view .bottom-div {
    display: block;
    height: auto;
}

/* Styles to hide the topbar during the transition period */
/* Note: The ribbon menu top bar height is 30px */
/*.top-container .ui-tabs .ui-tabs-nav,*/
/*.top-container .right-tab-bg,*/
/*.ribbon-view .right-logo,*/
/*.ribbon-view .left-logo {*/
/*  display: none;*/
/*}*/
/*.top-container {*/
/*  height: 134px; !* 164px - 30 *!*/
/*}*/

/* Context Menu - Some context menu styles here because overlay menu dropdown attaches to body so needs global styling, and hence want to keep the font sizing all in one place. */
.context-menu-bar span,
.p-menu-overlay span {
    font-size: 13px;
    font-weight: 400;
}
.context-menu-bar .p-button-icon {
    font-size: 14px;
}
.p-button.p-button-icon-only {
    width: 2.357rem !important; /* Apollo vue is now default 3rem, but we want to keep the old size. */
}
.p-button {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    transition: background-color .2s, color .2s, border-color .2s, box-shadow .2s;
    border-radius: 3px;
}
.layout-container .p-inputtext {
    font-family: Source Sans Pro, Arial, sans-serif;
    font-size: 0.9rem;
    color: #495057;
    background: #f8f9fa;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    transition: background-color .2s, color .2s, border-color .2s, box-shadow .2s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: 3px;
}

.layout-content {
    width: 100% !important; /* This is to ensure the content area takes up the full width of the screen. */
}

.layout-container.layout-overlay .layout-menu-container, .layout-container.layout-static .layout-menu-container {
    width: 230px;
    overflow: hidden;
}
.layout-container.layout-static .layout-menu-content {
    display: flex;
    flex-direction: column;
    align-items: stretch;
}
.layout-container.layout-static .layout-menu-content .layout-menu {
    flex-grow: 1;
}
.layout-container.layout-static .layout-menu-content .layout-menu-footer {
    position: relative;
    bottom: 0;
    height: auto;
}
.layout-content .layout-sidebar {
    padding: 0;
    width: 230px;
    height: calc(100% - 50px);
}

.layout-container.layout-static .layout-content-wrapper {
    padding: 0;
    margin-left: 16.5rem;
}

.layout-container.layout-slim .layout-content-wrapper {
    margin-left: 1rem;
    margin-top: -2rem;
}

.layout-container.layout-overlay .layout-content .layout-content-wrapper {
    margin-left: -2rem;
    margin-top: -1rem;
}

@media (max-width: 1999px) {
    .layout-container.layout-slim .layout-content-wrapper {
        margin-left: -2rem;
        margin-top: -2rem;
        width: 100%;
    }
}

@media (max-width: 1130px) {
    .layout-container.layout-static .layout-content-wrapper {
        margin-left: 0;
    }
}

@media (max-width: 1130px) {
    .layout-container.layout-slim .layout-content-wrapper {
        margin-left: -5rem;
    }
}

.layout-container.layout-horizontal .layout-content-wrapper {
    margin-left: -2rem;
    margin-top: -1.9rem;
}

.layout-container.layout-horizontal .layout-menu-container {
    padding: 0 20px;
    width: 100%;
}

.layout-container.layout-horizontal .layout-content-wrapper, .layout-container.layout-slim .layout-content-wrapper {
    padding: 2rem 0 2rem 2rem;
}

.layout-container.layout-horizontal .layout-menuitem-text  {
    margin-left: 6px!important;
    margin-right: 0!important;
}

.layout-sidebar .layout-menu-container {
    background-color: #fff;
}

.layout-sidebar {
    box-shadow: 0 2px 4px #0000004d;
    z-index: 10;
}

.layout-container.layout-slim .topbar {
    box-shadow: 0 2px 4px #0000004d;
}

.layout-static .layout-sidebar, .layout-slim .layout-sidebar {
    margin-top: 50px;
}

body a {
    text-decoration: none;
    color: #32689b;
}

.layout-container .layout-menu-container .layout-menu-content .layout-menu li .layout-menuitem-icon {
    /* Override menu icon color in all modes */
    color: #3b3b3b;
}

.p-tooltip .p-tooltip-text {
    background: #495057;
    color: #fff;
    padding: 0.5rem;
    box-shadow: 0 2px 4px -1px #0003, 0 4px 5px #00000024, 0 1px 10px #0000001f;
    border-radius: 3px;
}

.p-button:enabled:hover {
    background: #2d5e8c;
    color: #fff;
    border-color: #2d5e8c;
}

.p-component {
    font-family: Arial, Helvetica, sans-serif;
}

.layout-container.layout-static .layout-sidebar .layout-menu-container {
    padding-bottom: 0;
}

.p-menu .p-menuitem:not(.p-highlight):not(.p-disabled).p-focus > .p-menuitem-content {
    /* .p-focus makes the first menu item in a dropdown always look selected when menu is initialy opened. */
    color: inherit;
    background: inherit;
}

.p-menu .p-menuitem:not(.p-highlight):not(.p-disabled) > .p-menuitem-content:hover {
    color: #fff;
    background: #32689b;
}

.p-menu .p-menuitem:not(.p-highlight):not(.p-disabled) > .p-menuitem-content:hover a span {
    color: #fff;
}

.layout-overlay.layout-overlay-active .layout-sidebar, .layout-mobile.mobile-active .layout-sidebar {
    margin-top: 50px;
    transition: none;
}

.layout-overlay.layout-overlay-active .layout-menu-container, .layout-overlay.layout-overlay-active .layout-menu-container {
    overflow-y: scroll;
}
</style>
