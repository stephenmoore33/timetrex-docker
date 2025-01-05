<template>
    <transition name="layout-menu-container">
        <div class="layout-menu-container" @click="onMenuClick" v-show="isMenuVisible">
            <TTMenuSearch v-if="layoutMode === 'static'" :model="menu_model_for_layout_mode"></TTMenuSearch>
            <div ref="layout-menu-content" class="layout-menu-content" @mouseover="onMenuContentMouseOver" @mouseleave="onMenuContentMouseLeave">
                <!-- <div class="layout-menu-title">MENU</div>-->
                <TTAppMenu :key="'appmsenu'"
                           :model="menu_model_for_layout_mode"
                           :layoutMode="layoutMode"
                           :active="menuActive"
                           :mobileMenuActive="staticMenuMobileActive"
                           @root-menuitem-click="onRootMenuItemClick"></TTAppMenu>
                <div class="layout-menu-footer">
                    <!-- <div class="layout-menu-footer-title"></div>-->
                    <div class="layout-menu-footer-content">
                        <a id="copy_right_logo_link" class="copy-right-logo-link" target="_blank"><img id="copy_right_logo" class="copy-right-logo"></a>
                        <div class="signal-copyright-container">
                            <div><ul class="signal-strength">
                                <li class="signal-strength-very-weak">
                                    <div></div>
                                </li>
                                <li class="signal-strength-weak">
                                    <div></div>
                                </li>
                                <li class="signal-strength-strong">
                                    <div></div>
                                </li>
                                <li class="signal-strength-pretty-strong">
                                    <div></div>
                                </li>
                            </ul></div>
                            <div class="copyright-container">
                                <!-- REMOVING OR CHANGING THIS COPYRIGHT NOTICE IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT -->
                                <span v-html="copyright_notice" id="copy_right_info_1" class="copy-right-info" style="display: none"></span>
                            </div>
                        </div>
                        <div v-if="disable_feedback === false" id="feedbackLinkContainer" class="feedback-link-container">
                            <span id="feedback-link">Send feedback to {{ application_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import TTAppMenu from './main_menu/TTAppMenu';
import TTMenuSearch from './main_menu/TTMenuSearch';
import ProgressBar from 'primevue/progressbar';
import { MenuManager } from './main_menu/MenuManager';

export default {
    beforeCreate() {
        this.main_menu = new MenuManager();
        // Set a promise to wait for login to complete
        TTPromise.add( 'VueMenu', 'waitOnLoginSuccess' );
        TTPromise.add( 'VueMenu', 'waitOnTopBarCreated' );
        TTPromise.wait( 'VueMenu', null, () => {
            // We wait for login, as initDefaultMenuItems() within MenuManager needs permission data from login. If run too soon, no icons are shown, as permission_results will all return false.
            // Also needs to wait for TTTopbar to be created first so we do not run into race condition problems.
            this.main_menu.initDefaultMenuItems();
            this.menu_model = this.main_menu.getMenu(); // adding to [0] as there is a hidden top level menu in the PrimeVue menu. Can't replace whole menu as JS reference to menu object will be lost.
        } );
    },
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
        this.event_bus.on( this.component_id, 'rebuild_menu', () => {
            this.rebuildMenu();
        } );
        this.event_bus.on( this.component_id,'reset_slim_offsets', () => {
            this.resetSlimSubmenuOffsets();
        } );
        this.event_bus.on( 'global','reset_vue_data', () => {
            // Reset the Vue data when the user logs out.
            this.resetData();
        } );
    },
    data() {
        return {
            component_id: 'tt_left_container',
            menuActive: false,
            application_name: window.APPLICATION_NAME,
            copyright_notice: APIGlobal.pre_login_data.copyright_notice,
            menu_model: [ // While we wait for login, initiatize the base menu model.
                {
                    label: 'Navigation',
                    items: [{ // The items array here is what we will be dynamically updating via updateMenu(). If auto update through TTPromise fails, menu can be updated using this menu option.
                        label: 'Update Menu',
                        icon: 'pi pi-fw pi-refresh',
                        command: this.rebuildMenu
                    }]
                }
            ],
            disable_feedback: APIGlobal.pre_login_data.disable_feedback
        };
    },
    props: {
        isMenuVisible: Boolean,
        layoutMode: String,
        staticMenuMobileActive: Boolean,
    },
    computed: {
        menu_model_for_layout_mode() {
            //Apollo menu works diffrently depending on the layout mode. In static mode it seems to require a top level menu item.
            //This may be a temporary fix, but was required to get menu to work right now. Further investigation is required.
            let new_menu = null;

            if ( this.layoutMode === 'static' || this.layoutMode === 'overlay' ) {
                new_menu = {
                    label: 'Navigation',
                    icon: 'pi pi-home',
                    items: []
                };

                new_menu.items = this.menu_model;

                return [new_menu];
            } else {
                return this.menu_model;
            }
        },
    },
    methods: {
        resetData() {
            Object.assign( this.$data, this.$options.data() );
        },
        rebuildMenu() {
            this.menu_model = this.main_menu.rebuildMenu();
        },
        isSubMenuClick( event ) {
            return event?.target?.closest('.layout-root-submenulist') !== null;
        },
        onMenuClick( event ) {
            this.$emit( 'menu-click', event );
            if ( this.layoutMode === 'slim' ) {
                let root_sub_menu_list = document.querySelector( '.layout-main-menu .active-menuitem .layout-root-submenulist' );
                if ( root_sub_menu_list ) {
                    if ( !this.isSubMenuClick( event ) && event.target.closest('a') ) { //In slim mode reposition the active menu dropdown to the mouse position.
                        root_sub_menu_list.style.top = ( event.target.closest('a').getBoundingClientRect().top - 50 ) + 'px'; //50 is the offset of the side menu from the top of the screen (topbar height)
                    }
                    if ( root_sub_menu_list.getBoundingClientRect().bottom > window.innerHeight ) { //If the submenu is going off the screen, reposition it just above the bottom of the screen.
                        let submenuHeight = root_sub_menu_list.offsetHeight;
                        root_sub_menu_list.style.top = `calc(100% - ${submenuHeight}px - 10px)`;
                    }
                }
            } else if ( this.layoutMode === 'horizontal' && !this.isSubMenuClick( event ) ) {
                let root_sub_menu_list = document.querySelector( '.layout-main-menu .active-menuitem .layout-root-submenulist' );
                if ( root_sub_menu_list && event.target.closest( 'a' ) ) {
                    root_sub_menu_list.style.top = ( event.target.closest( 'a' ).getBoundingClientRect().top - 10 ) + 'px'; //50 is the offset of the side menu from the top of the screen (topbar height)
                }
            } else if ( this.layoutMode === 'static' || this.layoutMode === 'overlay' ) {
                //A slight delay is required before calculating if the scroll bar should be shown as the dropdown does not expand instantly until it's transition is complete.
                setTimeout( () => {
                    if ( Global.isVerticalScrollBarRequired( this.$refs['layout-menu-content'] ) ) {
                        this.toggleScrollBar( true );
                    } else {
                        this.toggleScrollBar( false );
                    }
                }, 250 );
            }
        },
        onRootMenuItemClick( event ) {
            this.$emit( 'root-menuitem-click', event );
        },
        onTopbarItemClick( event ) {
            this.$emit( 'topbar-item-click', event );
        },
        onMenuContentMouseOver() {
            if ( Global.isVerticalScrollBarRequired( this.$refs['layout-menu-content'] ) ) {
                this.toggleScrollBar( true );
            } else {
                this.toggleScrollBar( false );
            }
        },
        onMenuContentMouseLeave() {
            this.toggleScrollBar( false );
        },
        toggleScrollBar( show ) {
            if ( show == true || Global.UNIT_TEST_MODE == true ) {
                this.$refs['layout-menu-content'].style.maskPosition = 'left top';
                this.$refs['layout-menu-content'].style.webkitMaskPosition = 'left top';
            } else {
                this.$refs['layout-menu-content'].style.maskPosition = 'left bottom';
                this.$refs['layout-menu-content'].style.webkitMaskPosition = 'left bottom';
            }
        },
        resetSlimSubmenuOffsets() {
            let menu = document.querySelector( '.layout-main-menu' );
            let submenu = null;

            //In slim mode we offset submenus to prevent the menu from going off screen.
            //This needs to be reset when switching from slim so that the changes do not carry over to other layout modes.
            for ( let i = 0; i < menu.childNodes.length; i++ ) {
                if ( menu.children[i] && menu.children[i].children[1] ) {
                    submenu = menu.children[i].children[1];
                    submenu.style.top = '';
                    submenu.style.maxHeight = '';
                    submenu.style.overflowY = '';
                }
            }
        }
    },
    components: {
        TTAppMenu,
        TTMenuSearch,
        ProgressBar
    }
};
</script>
<style>
/* Warning: These styles get applied globally. They are not scoped. */

/* TODO: Future refactor: Worth moving a lot of these menu styles over to the actual components/main_menu/TTAppMenu.vue file as some are more directly relevant there to the menu rather than the left container. */

/* Overrides for left menu up/down in/out transitions */
.layout-container .layout-menu-container .layout-menu li ul.layout-submenu-container-leave-active {
    transition-duration: 0s; /* Overrides Apollo default: 0.45s */
}

.layout-container .layout-menu-container .layout-menu li ul.layout-submenu-container-enter-active {
    /*transition: max-height 1s ease-in-out;*/
    transition-duration: 0.2s; /* Overrides Apollo default: 1s */
}

.layout-container .layout-menu-container.layout-menu-container-enter-to {
    transition-duration: 0.2s!important; /* Overrides Apollo default: 1s */
}

/* Hide left main menu scrollbar with a mask until user mouseovers the menu and the menu is large enough to warrant a scrollbar. Mouseover and size check done in JavaScript */
.layout-static .layout-menu-content, .layout-overlay .layout-menu-content {
    overflow-y: scroll;
    mask-image: linear-gradient(0deg, transparent, #000000), linear-gradient(270deg, transparent 17px, #000000 0);
    mask-size: 100% 20000px;
    mask-position: left bottom;
    -webkit-mask-image: linear-gradient(0deg, transparent, #000000), linear-gradient(270deg, transparent 17px, #000000 0);
    -webkit-mask-size: 100% 20000px;
    -webkit-mask-position: left bottom;
    transition: mask-position 0.3s, -webkit-mask-position 0.3s;
}

/*.layout-container.layout-overlay .layout-menu-container,
.layout-container.layout-static .layout-menu-container {
    overflow-y: scroll; !* Force permanent vertical scrollbar space so that the content like menu arrows dont flash/move around when toggling between open/closed menu groups *!
}*/

/* --- Animation and style settings for menu mode toggle from static to slim and back --- */

/* Do not animate width change from slim to static, no matter now fast, text still gets squashed/wrapped. */
/*.layout-container.layout-static .layout-menu-container {*/
/*    transition: width 0.01s; !* From static to slim, do it fast, so the text is not squashed/wrapped during animation. *!*/
/*}*/

.layout-container.layout-slim .layout-menu-container {
    transition: width 0.2s; /* From static to slim, can be slow, but not too slow, because the text dissappears before animation finishes. */
}

.layout-container.layout-slim .layout-menu-container .layout-menu>li>a {
    text-align: left; /* Without this, text jumps left/right during menu state change width animation */
    /* padding-left: 12px; With the text align left above, this ensures icon is still centered in slim mode during and after animation. Trial & error value. */
}
.layout-container .layout-menu-container .layout-menu li>a i {
    line-height: 22px; /* Sets icon line height equal to .layout-menuitem-toggler so that the icons dont move up/down during transition to/from slim/static mode */
}

.layout-container.layout-slim .layout-menu-container {
    width: 42px; /* Sets width of slim menu via a trial & error value that ensures icon stays in same place between slim/static mode toggle. */
}

.layout-container.layout-slim .layout-sidebar {
    width: 42px; /* Sets width of slim menu via a trial & error value that ensures icon stays in same place between slim/static mode toggle. */
}

.layout-container.layout-slim .layout-menu-container .layout-menu>li>a i:first-child {
    font-size: 14px; /* Matches font size for static mode, so icon size stays consistent during mode toggle */
}

.layout-container.layout-slim .layout-menu a {
    border: 1px solid transparent;
    margin-bottom: 0.10rem;
}

.layout-container.layout-slim .layout-sidebar .layout-menu .layout-root-menuitem > a {
    width: 3rem;
    height: 3rem;
    margin: 0.5rem auto 0.5rem auto;
}

.layout-container.layout-overlay .layout-menu-container .layout-menu-title, .layout-container.layout-static .layout-menu-container .layout-menu-title {
    padding: 8px; /* To bring the menu header border-bottom line more inline with the bottom of the context menu */
}

.layout-container.layout-static .layout-menu-container .layout-menu-footer {
    padding-bottom: 0;
}
.feedback-link-container {
    margin-top: 10px;
}
.layout-menu-footer-content {
    padding: 10px 12px;
}
.layout-menu-footer-content,
.layout-container.layout-overlay .layout-menu-container .layout-menu-footer .layout-menu-footer-content,
.layout-container.layout-static .layout-menu-container .layout-menu-footer .layout-menu-footer-content {
    /* Override the base primevue styles */
    border-top: 1px solid #dee2e6;
    text-align: center;
}
.signal-copyright-container {
    margin-top: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.copy-right-info {
    margin-top: 2px;
    font-size: 10px;
    padding-top: 5px;
}
.layout-container .layout-menu-container .layout-menu-content .layout-menu li>a {
    font-size: 13px;
    color: #3b3b3b;
}

.layout-static .layout-main-menu .layout-root-submenulist > li.active-menuitem {
    border: 1px solid #dbdee1;
    border-radius: 2px;
}

.layout-static .layout-main-menu > li {
    margin-left: 5px;
    margin-right: 5px;
    border: 1px solid transparent;
}

#tt_main_ui .layout-container.layout-static .layout-menu-container .layout-menu li.p-menu-separator {
    margin-left: 5px;
    margin-right: 5px;
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu > li > a {
    padding: 9px; /* Centers the vertical alignment of the horizontal menu items */
}

.layout-menu .active-menuitem>ul {
    z-index: 10; /* #3065 Ensures the active menu items are shown properly and not transparently mixing in with the main menu items behind it, making the active menu items unreadable. Especially in horizontal menu mode. */
}

.layout-container.layout-static .layout-menu-container .layout-menu-content {
    height: calc(100% - 37px); /*Adjustment required to take into account the search bar height*/
}

.layout-sidebar .layout-menu ul a {
    padding: 10px 12px;
}

.layout-sidebar .layout-menu ul li {
    border: 1px solid transparent;
}

.layout-menuitem-root-text {
    display: none;
}

.layout-sidebar .layout-menu ul a .layout-submenu-toggler {
    font-size: 1rem;
}

.layout-container.layout-slim .layout-menu-footer, .layout-container.layout-horizontal .layout-menu-footer {
    display: none;
}

.layout-container .layout-menu-container li a.p-menu-separator {
    border-top: 1px solid #dee2e6;
    border-radius: 0 !important;
    width: 95%;
    display: block;
    margin: 0.25rem auto -30px;
    padding-bottom: 0;
    background-color: #fff !important;
    pointer-events: none;
}

.layout-container.layout-static .layout-root-submenulist {
    margin-left: -1px;
}
.layout-container.layout-overlay .layout-menu-container .layout-menu li a>span, .layout-container.layout-static .layout-menu-container .layout-menu li a>span {
    vertical-align: middle;
    display: inline-block;
    margin-left: -1px;
    line-height: 15px;
}

.layout-container.layout-slim .layout-root-submenulist li span:empty, .layout-container.layout-horizontal .layout-root-submenulist li span:empty {
    background-color: #fff;
}

.layout-container.layout-overlay .layout-menu-container .layout-menu>li ul li a, .layout-container.layout-static .layout-menu-container .layout-menu>li ul li a {
    padding-left: 24px;
}

.layout-container.layout-overlay .layout-menu-container .layout-menu>li ul.layout-root-submenulist>li>a, .layout-container.layout-static .layout-menu-container .layout-menu>li ul.layout-root-submenulist>li>a {
    padding-left: 12px;
}

.layout-container.layout-overlay .layout-menu-container .layout-menu li a, .layout-container.layout-static .layout-menu-container .layout-menu li a {
    padding: 10px 12px;
}

.layout-container.layout-overlay .layout-menu-container .layout-menu li a:not(.p-disabled):hover, .layout-container.layout-slim .layout-menu-container .layout-menu li a:not(.p-disabled):hover, .layout-container.layout-static .layout-menu-container .layout-menu li a:not(.p-disabled):hover {
    background-color: #32689b !important;
    color: #fff !important;
}

.layout-container.layout-static .layout-menu-container .layout-menu li a:not(.p-disabled):hover .layout-menuitem-icon {
    background-color: #32689b !important;
    color: #fff !important;
}

.layout-container.layout-static .layout-menu-container .layout-menu li a:not(.p-disabled):hover .layout-submenu-toggler {
    background-color: #32689b !important;
    color: #fff !important;
}

.layout-container ul li a {
    transition: none !important;
}

.layout-container.layout-horizontal .layout-menu .layout-root-menuitem > ul a:hover {
    background-color: #32689b;
    color: #fff;
    border-radius: 0;
}

.layout-container.layout-slim .layout-menu-container .layout-menu li a {
    border-radius: 0;
}

.layout-container.layout-static .layout-sidebar .layout-menu ul ul {
    border-radius: 0;
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu>li>a:not(.p-disabled):hover {
    border-bottom: 2px solid #32689b;
    color: #32689b;
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu>li.layout-root-menuitem.active-menuitem>a {
    border-bottom: 2px solid #32689b;
    border-radius: 0;
    background-color: #32689b;
    color: #fff;
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu>li>a:not(.p-disabled) {
    border-bottom: 2px solid transparent;
}

.layout-container .layout-sidebar .layout-menu ul.layout-root-submenulist>li:hover>a>i {
    color: #fff;
}

.layout-container .layout-sidebar .layout-menu-container .layout-menu .layout-root-submenulist li.active-menuitem>a {
    background-color: #fff;
    color: #32689b;
}

.layout-container.layout-static .layout-menu-container .layout-menu li.active-menuitem>a .layout-menuitem-icon, .layout-container.layout-static .layout-menu-container .layout-menu li.active-menuitem>a .layout-submenu-toggler {
    background-color: #fff;
    color: inherit;
}

.layout-container.layout-horizontal .layout-menu-container .layout-menu li.active-menuitem>a .layout-menuitem-icon {
    color: inherit;
}

.layout-container.layout-slim .layout-sidebar .layout-menu-container .layout-menu-content .layout-menu li .layout-menuitem-icon {
    color: inherit;
}

.layout-container.layout-slim .layout-sidebar .layout-menu-container .layout-menu-content .layout-root-menuitem.active-menuitem {
    background-color: #32689b;
    color: #fff;
}
.layout-container.layout-slim .layout-sidebar .layout-menu-container .layout-menu-content .layout-root-menuitem.active-menuitem .layout-menuitem-icon {
    color: #fff;
}

.layout-container.layout-slim .layout-sidebar .layout-menu .layout-root-menuitem > ul, .layout-container.layout-horizontal .layout-sidebar .layout-menu .layout-root-menuitem > ul {
    box-shadow: 0 2px 4px #0000004d;
    max-height: 75vh;
    padding: 0;
    border: none;
    border-radius: 0;
}

.layout-container.layout-slim .layout-sidebar .layout-menu ul li, .layout-container.layout-horizontal .layout-sidebar .layout-menu ul li {
    border: none;
    margin-bottom: 0;
}

.layout-container.layout-horizontal .layout-sidebar  .layout-menu {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    align-items: center;
    height: 100%;
}

.layout-container.layout-horizontal .layout-root-submenulist {
    margin-top: 50px; /* Adjustment for topbar height */
    left: inherit !important;
}

.layout-container .layout-sidebar .layout-menu ul ul {
    transition: max-height 0.05s cubic-bezier(0.86, 0, 0.07, 1);
}

.layout-container .layout-sidebar a {
    user-select: none;
    -webkit-user-drag: none;
}

.layout-container li span {
    font-size: 13px;
}

.layout-sidebar .layout-menu li.active-menuitem > ul {
    max-height: inherit;
}

@media screen and (max-width: 1130px) {
    .layout-container .layout-sidebar {
        z-index: 999;
        transform: translateX(-100%);
        transition: transform var(--transition-duration);
        box-shadow: none;
    }
}
</style>
