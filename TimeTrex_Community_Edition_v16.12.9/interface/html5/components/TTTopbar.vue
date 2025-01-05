<!-- This file is originally from the primevue apollo layout, but has been copied into our components for customization -->
<template>
    <div class="topbar layout-topbar">
        <div class="left-group">
            <button class="p-link menu-button" @click="onMenuButtonClick">
                <i class="pi pi-bars"></i>
            </button>
            <div class="company-logo-container">
                <router-link to="/" class="logo-link">
                    <img class="logo" id="topbar-company-logo" :src="company_logo" alt="company logo" @click="onCompanyLogoClicked"/>
                </router-link>
            </div>
            <div class="slash-line company-name">{{ company_name }}</div>
            <span class="topbar-icon topbar-feedback" v-if="show_feedback">
                <i class="tticon tticon-heart_red_24dp p-mr-4 p-text-secondary" id="profile-feedback" v-tooltip.bottom="tooltips.feedback" @click="onFeedbackClick"></i>
            </span>
        </div>
        <div class="middle-group">
            <div v-if="isSandboxMode()" class="sandbox-title">{{ sandbox_mode_text }}</div>
        </div>
        <div class="right-group">
            <ProgressSpinner class="job-queue-spinner" v-if="progress_bar_visible" v-tooltip.bottom="tooltips.job_queue" style="width:30px;height:30px" strokeWidth="5" animationDuration="2s" @click="toggleJobQueuePanel"/>
            <OverlayPanel ref="job-queue-panel" @show="getRunningJobsData" @hide="onHideJobQueuePanel">
                <ul class="job-queue-list">
                    <div class="job-queue-item" v-if="pending_job_queue_tasks.length === 0">{{ job_queue_complete_text }}</div>
                    <div class="job-queue-item" v-for="item in pending_job_queue_tasks">
                        <div class="job-queue-item-summary">{{ item.name }}</div>
                        <div class="job-queue-item-detail">Started {{ item.elapsed_time }} ago</div>
                    </div>
                </ul>
            </OverlayPanel>
            <span class="topbar-icon topbar-inout" v-if="show_punch_in_out">
                <i class="tticon tticon-timer_black_24dp p-mr-4 p-text-secondary" id="profile-in-out" v-tooltip.bottom="tooltips.in_out" @click="onInOutClick"></i>
            </span>
            <span class="topbar-icon topbar-notification-bell">
                <i v-if="notification_count === 0" class="tticon tticon-notifications_black_24dp p-mr-4 p-text-secondary" id="profile-notifications" v-tooltip.bottom="tooltips.notifications" @click="onNotificationBellClick"></i>
                <i v-else class="tticon tticon-notifications_black_24dp p-mr-4 p-text-secondary" id="profile-notifications" v-tooltip.bottom="tooltips.notifications" v-badge.info="notification_count" @click="onNotificationBellClick"></i>
            </span>
            <span class="topbar-icon topbar-assistant icon-get-attention" v-if="show_assistant">
                <i class="tticon tticon-stars_black_24dp p-mr-4 p-text-secondary" id="profile-assistant" v-tooltip.bottom="tooltips.assistant" @click="onAssistantClick"></i>
            </span>
            <span class="topbar-icon topbar-help" v-show="help_menu_items.length > 0">
                <i class="tticon tticon-help_center_black_24dp p-mr-4 p-text-secondary" id="profile-help" v-tooltip.bottom="tooltips.help" @click="onTopbarMenuButtonClickHelp"></i>
            </span>
            <!-- Help Menu -->
            <ul :class="topbarItemsClassHelp" role="menu" id="profile-help-items">
                <li class="profile-menu-item" v-for="item in help_menu_items">
                    <div v-if="item.separator === true" class="profile-menu-separator"></div>
                    <button v-else class="p-link" :id="createMenuId(item.id)" @click=handleMenuClick(item)>
                        <i class="topbar-icon" :class="item.icon"></i>
                        <span class="topbar-item-name">{{ item.label }}</span>
                        <span v-if="item.badge_number" v-badge.info="item.badge_number"></span>
                    </button>
                </li>
            </ul>
            <div class="slash-line">&nbsp;</div> <!--None breaking space required to show slash-->
            <button class="p-link profile" id="profile-button" @click="onTopbarMenuButtonClickProfile" v-tooltip.bottom="tooltips.profile">
                <span class="username">{{ current_user.first_name }} {{ current_user.last_name }}</span>
                <div class="profile-image-holder">
                    <img class="profile-image" :src="profile_image_url" alt="apollo-layout"/>
                </div>
                <i v-if="totalPendingCount === 0" class="pi pi-angle-down"></i>
                <i v-else class="pi pi-angle-down" v-badge.info="totalPendingCount"></i>
            </button>
            <!-- My Profile Menu -->
            <ul :class="topbarItemsClassProfile" role="menu" id="profile-menu-items">
                <li class="profile-menu-item" v-for="item in profile_menu_items">
                    <div v-if="item.separator === true" class="profile-menu-separator"></div>
                    <button v-else class="p-link" :id="createMenuId(item.id)" @click=handleMenuClick(item)>
                        <i class="topbar-icon" :class="item.icon"></i>
                        <span class="topbar-item-name">{{ item.label }}</span>
                        <span v-if="item.badge_number" v-badge.info="item.badge_number"></span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
import InputText from 'primevue/inputtext';
import { Global } from '@/global/Global';
import ProgressSpinner from 'primevue/progressspinner';
import OverlayPanel from 'primevue/overlaypanel';
import TTVueUtils from '@/services/TTVueUtils';
import TTAssistant from '@/components/TTAssistant';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
        this.event_bus.on( this.component_id, 'notification_bell', ( event_data ) => {
            this.updateNotificationCount( event_data.notification_count );
        } );
        this.event_bus.on( this.component_id, 'open_assistant', ( event_data ) => {
            this.onAssistantClick();
        } );
        this.event_bus.on( this.component_id, 'refresh_login_data', this.refreshCompanyAndUserInfo );
        this.event_bus.on( this.component_id, 'refresh_profile_image', ( event_data ) => {
            this.profile_image_url = this.getProfileImage( true, event_data.image_url );
        } );
        this.event_bus.on( this.component_id, 'profile_menu_data', ( event_data ) => {
            // Get the account menu data from the main menu (MenuManager)
            this.refreshProfileMenuData( event_data.profile_menu_data );
        } );
        this.event_bus.on( this.component_id, 'help_menu_data', ( event_data ) => {
            // Get the help menu data from the main menu (MenuManager)
            this.refreshHelpMenuData( event_data.help_menu_data );
        } );
        this.event_bus.on( this.component_id, 'profile_pending_counts', ( event_data ) => {
            // Get the number of pending authorizations, notifications and messages for the current user.
            this.updateProfilePendingTotals( event_data.object_types );
        } );
        this.event_bus.on( 'global', 'reset_vue_data', this.resetData );
        this.event_bus.on( this.component_id, 'toggle_job_queue_spinner', ( event_data ) => {
            if ( event_data.get_job_data ) {
                this.getRunningJobsData();
            }
            if ( event_data.show ) {
                this.showJobQueueSpinner();
            }
            if ( event_data.check_completed ) {
                this.checkForJobQueuePendingTasks();
            }
        } );
        TTPromise.resolve( 'VueMenu', 'waitOnTopBarCreated' ); //Final promise before main menu is built, as this component needs to be created first.
        this.refreshCompanyAndUserInfo();
    },
    beforeUnmount() {
        this.hideJobQueueSpinner( false );
    },
    props: {
        topbarMenuActive: Boolean,
        activeTopbarItem: String
    },
    data() {
        return {
            component_id: 'tt_topbar',
            company_name: '',
            company_logo: '',
            current_user: {},
            profile_image_url: '',
            notification_count: 0,
            profile_menu_items: [],
            help_menu_items: [],
            pending_job_queue_tasks: [],
            progress_bar_visible: false,
            show_punch_in_out: true, //See refreshCompanyAndUserInfo()
            show_feedback: false, //See refreshCompanyAndUserInfo()
            show_assistant: true, //See refreshCompanyAndUserInfo()
            active_dropdown_menu: '', //Instead of using activeTopbarItem prop, only need to set active_dropdown_menu in this component.
            has_mounted_tt_assistant: false,
            tooltips: {
                assistant: $.i18n._( 'AI Assistant' ),
                feedback: $.i18n._( 'Show your support!' ),
                in_out: $.i18n._( 'In/Out' ),
                notifications: $.i18n._( 'Notifications' ),
                help: $.i18n._( 'Help' ),
                profile: $.i18n._( 'Employee Profile' ),
                job_queue: $.i18n._( 'Running Tasks' ),
            },
            sandbox_mode_text: $.i18n._( 'Sandbox Mode' ),
            job_queue_complete_text: $.i18n._( 'All Tasks Completed' )
        };
    },
    interval: null,
    computed: {
        topbarItemsClassProfile() {
            return ['topbar-menu fadeInDown', {
                'topbar-menu-visible': this.topbarMenuActive && this.active_dropdown_menu === 'profile',
            }];
        },
        topbarItemsClassHelp() {
            return ['topbar-menu fadeInDown', {
                'topbar-menu-visible': this.topbarMenuActive && this.active_dropdown_menu === 'help',
            }];
        },
        totalPendingCount() {
            return this.profile_menu_items.filter( ( item ) => item.badge_number > 0 ).reduce( ( total, item ) => total + item.badge_number, 0 );
        }
        /*notificationsCount() {
            Notifications currently are not shown on profile dropdown but we may wish to show it there in the future;
            let notification_item = this.profile_menu_items.find( ( item ) => item.id === 'notification' );
            return notification_item && notification_item.badge_number ? notification_item.badge_number : 0;
        },*/
    },
    methods: {
        resetData() {
            this.hideJobQueueSpinner( false ); //Make sure job queue spinner is hidden and cancelled (especially during logout).
            Object.assign( this.$data, this.$options.data() );
        },
        handleMenuClick( item ) {
            item.command();
        },
        refreshProfileMenuData( data ) {
            this.profile_menu_items.length = 0;
            this.profile_menu_items.push( ...data );
        },
        refreshHelpMenuData( data ) {
            this.help_menu_items.length = 0;
            this.help_menu_items.push( ...data );
        },
        updateProfilePendingTotals( object_types ) {
            TTAPI.APIUser.getUserPendingTotals( object_types, {
                onResult: ( result ) => {
                    let pending_counts = result.getResult();

                    //Assign totals to the corresponding profile menu item badge number.
                    this.profile_menu_items.forEach( item => {
                        if ( pending_counts[item.id] || pending_counts[item.id] === 0 ) {
                            if ( Global.UNIT_TEST_MODE == true ) {
                                pending_counts[item.id] = 999;
                            }
                            item.badge_number = pending_counts[item.id];
                        }
                    } );

                    if ( pending_counts['notification'] ) {
                        if ( Global.UNIT_TEST_MODE == true ) {
                            pending_counts['notification'] = 999;
                        }
                        this.notification_count = pending_counts['notification'];
                    }
                }
            } );
        },
        onMenuButtonClick( event ) {
            this.$emit( 'menubutton-click', event );
        },
        onTopbarMenuButtonClickProfile( event ) {
            this.active_dropdown_menu = 'profile';
            if ( this.profile_menu_items.length === 0 ) {
                //This should never happen. It previously would happen consistently on FireFox and rarely Chrome/Other Browsers due to race conditions.
                Debug.Error( 'Error: Profile Menu Items are empty. Rebuilding the menu.', 'ContextMenuManager.js', 'ContextMenuManager', 'getMenuModelByMenuId', 1 );
                this.event_bus.emit( 'tt_left_container', 'rebuild_menu' );
            }
            this.$emit( 'topbar-menubutton-click', event );
        },
        onTopbarMenuButtonClickHelp( event ) {
            this.active_dropdown_menu = 'help';
            this.$emit( 'topbar-menubutton-click', event );
        },
        onCompanyLogoClicked() {
            Global.closeEditViews( function() {
                MenuManager.goToView( 'Home' );
            } );
        },
        onNotificationBellClick() {
            Global.closeEditViews( function() {
                MenuManager.goToView( 'Notification' );
            } );
        },
        onFeedbackClick() {
            var current_user = LocalCacheData.getLoginUser();

            TAlertManager.showConfirmAlert( LocalCacheData.getLoginData().application_name + ' ' + $.i18n._( 'is <strong>free</strong> because of people like you, ' + current_user.first_name + '!<br><br>Show your support with a 5-star review!<br><br>⭐⭐⭐⭐⭐' ), $.i18n._( 'Show your support!' ), function( flag ) {
                if ( flag === true ) {
                    Global.sendAnalyticsEvent( 'feedback', 'review', 'review:feedback:yes' );

                    var support_email_address = ( current_user.work_email != '' ? current_user.work_email : ( current_user.home_email != '' ? current_user.home_email : 'EmailNotSpecified@NoDomain.com' ) ); //Fall back to a bogus email address otherwise the live chat will be rejected, and some people have complained about that.
                    var url = 'https://www.timetrex.com/community-reviews?v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition() + '&registration_key=' + LocalCacheData.getLoginData().registration_key + '&email=' + support_email_address + '&first_name=' + current_user.first_name + '#reviews';
                    window.open( url, '_blank' );
                } else {
                    Global.sendAnalyticsEvent( 'feedback', 'review', 'review:feedback:no' );
                }
            }, $.i18n._( 'Leave a Review!' ), $.i18n._( 'No, thanks.' ) );

            Global.sendAnalyticsEvent( 'feedback', 'review', 'review:feedback:click' );
        },
        onAssistantClick() {
            if ( !this.has_mounted_tt_assistant ) {
                TTVueUtils.mountComponent( 'tt-assistant-ui', TTAssistant, {
                    view_id: 'tt-assistant-view',
                    component_id: 'tt-assistant-ui',
                } );

                this.has_mounted_tt_assistant = true;
            } else {
                this.event_bus.emit( 'tt_assistant', 'show_tt_assistant' );
            }
        },
        onInOutClick() {
            if ( LocalCacheData.getLastPunchTime() === null || ( ( new Date().getTime() - LocalCacheData.getLastPunchTime() ) / 1000 ) > 60 ) {
                Global.closeEditViews( function() {
                    MenuManager.openSelectView( 'InOut' );
                } );
            } else {
                let seconds_remaining = ( 60 - ( new Date().getTime() - LocalCacheData.getLastPunchTime() ) / 1000 );
                TAlertManager.showAlert( $.i18n._( 'Please wait at least ' ) + Math.round( seconds_remaining ) + $.i18n._( ' seconds to punch again.' ) );
            }
        },
        updateNotificationCount( count ) {
            if ( Global.UNIT_TEST_MODE == true ) {
                count = 999;
            }
            this.notification_count = count;
        },
        getProfileImage( cache_buster = false, image_url = null ) {
            let profile_image_url = '';

            if ( image_url ) {
                //Image was provided by event bus from EmployeeViewController when image was updated.
                profile_image_url = image_url;
            } else {
                //User logged in and we need to get the image from the server.
                profile_image_url = ServiceCaller.getURLByObjectType( 'user_photo' ) + '&object_id=' + this.current_user.id;
                if ( cache_buster ) {
                    //Helps useds see their profile image update when they change it we need to update the URL, as Vue does not detect the change.
                    profile_image_url += '&refresh_id=' + TTUUID.generateUUID();
                }
            }

            return profile_image_url;
        },
        refreshCompanyAndUserInfo( force ) {
            if ( !this.company_name || force ) {
                if ( !this.current_user.id || ( this.current_user.id && LocalCacheData.getLoginUser().id !== this.current_user.id ) ) {
                    //This condition triggers when a new user logs in or page is refreshed.

                    //Setup notifications and ping check for the new user.
                    IndexViewController.initializeNotifications( 'login' );
                    Global.setupPing();

                    //This ensures the main menu updates and shows the correct menu items for the current users permissions.
                    //Otherwise the user may see menu items from the last logged in user.
                    this.event_bus.emit( 'tt_left_container', 'rebuild_menu' );
                    this.updateProfilePendingTotals( [] );
                }
                this.company_name = LocalCacheData.getCurrentCompany().name;
                this.current_user = LocalCacheData.getLoginUser();
                this.profile_image_url = this.getProfileImage();
                this.company_logo = ServiceCaller.getURLByObjectType( 'company_logo' );
                this.show_punch_in_out = PermissionManager.validate( 'punch', 'punch_in_out' );
                this.show_assistant = Global.getFeatureFlag( 'assistant' ) && ( PermissionManager.getPermissionLevel() >= 40 );
                this.show_feedback = ( Global.getProductEdition() == 10 && LocalCacheData.deployment_on_demand == true && APIGlobal.pre_login_data.demo_mode == false && Global.getFeatureFlag( 'support_chat' ) == true && Global.getFeatureFlag( 'show_feedback' ) == true && PermissionManager.getPermissionLevel() >= 40 );
                this.event_bus.emit( 'tt_main_ui', 'get_user_saved_layout_mode' );
            }
        },
        isSandboxMode() {
            return APIGlobal.pre_login_data['sandbox'];
        },
        toggleJobQueuePanel( event ) {
            this.$refs['job-queue-panel'].toggle( event );
        },
        showJobQueueSpinner() {
            this.progress_bar_visible = true;
            this.startIntervalJobQueueTimer();
        },
        hideJobQueueSpinner( update_timesheet ) {
            this.progress_bar_visible = false;
            this.pending_job_queue_tasks = [];
            clearInterval( this.interval );
            this.interval = null;
            this.$refs['job-queue-panel'].hide();

            LocalCacheData.setJobQueuePunchData( null );

            if ( update_timesheet && LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
                LocalCacheData.current_open_primary_controller.search();
            }
        },
        getRunningJobsData() {
            let data = {};
            data.filter_data = { user_id: this.current_user.id, status_id: [10, 20] };
            TTAPI.APISystemJobQueue.getSystemJobQueue( data, {
                onResult: ( result ) => {
                    if ( result.isValid() ) {
                        let result_data = result.getResult();
                        if ( Array.isArray( result_data ) ) {
                            this.pending_job_queue_tasks = result_data;
                        } else {
                            this.pending_job_queue_tasks = [];
                        }
                    }
                }
            } );
        },
        startIntervalJobQueueTimer() {
            clearInterval( this.interval );
            this.interval = setInterval( () => {
                this.checkForJobQueuePendingTasks();
            }, 60000 );
        },
        checkForJobQueuePendingTasks() {
            TTAPI.APISystemJobQueue.getPendingAndRunningSystemJobQueue( {
                onResult: ( result ) => {
                    let pending_counts = result.getResult();
                    if ( pending_counts == 0 ) {
                        this.hideJobQueueSpinner( true );
                    }
                }
            } );
        },
        onHideJobQueuePanel() {
            if ( this.pending_job_queue_tasks.length === 0 ) {
                this.hideJobQueueSpinner( true );
            }
        },
        createMenuId( menu_id ) {
            return 'profile-menu-' + menu_id;
        },
    },
    components: {
        InputText, // Added by RT
        ProgressSpinner,
        OverlayPanel
    }
};
</script>
<style scoped>
.layout-container .topbar {
    padding: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.left-group {
    display: flex;
    align-items: center;
    /* No padding-left here, as it's put in the menu-button to make for better shaped selection border/accessibility border) */
}

.right-group {
    display: flex;
    align-items: center;
    padding-right: 5px;
}

.topbar-icon {
    margin: 5px 7px;
}

.topbar-icon .tticon {
    color: #ffffff;
    font-size: 2rem !important;
    cursor: pointer;
}

.layout-container .topbar .username {
    font-weight: 700;
    font-size: 13px;
}

.layout-container .topbar .company-name {
    font-size: 13px;
    font-weight: bold;
}

.layout-container .topbar .topbar-menu {
    right: 5px; /* Bring it closer to the edge, so it looks less like its under the bell, and more under the profile menu. */
}

.layout-container .topbar {
    width: 100%;
}

.topbar .topbar-item-name {
    font-size: 13px;
}

.profile-image-holder {
    width: 40px;
    height: 40px;
    vertical-align: middle;
    overflow: hidden;
    margin-right: 8px;
    display: inline-block;
    border-radius: 50%;
}

.profile-image {
    height: 40px;
    object-fit: cover;
}

.profile-menu-separator {
    border-top: 1px solid #dee2e6;
    margin: .25rem 0;
}

.sandbox-title {
    font-size: 24px;
    color: white;
}

::v-deep(.profile .p-overlay-badge .p-badge) {
    right: 23px;
    top: -5px;
}

::v-deep(.topbar-notification-bell .p-badge) {
    top: 1px; /* This is to ensure notification badge is at same height as the profile badge */
    right: 3px; /* Reduce overlap with help icon */
}

.topbar .profile-menu-item .p-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.job-queue-spinner {
    margin-right: 5px;
}

.job-queue-list {
    padding: 0 10px 0 10px;
}

.job-queue-item {
    margin: 10px;
}

.job-queue-item-summary {
    font-weight: 600;
}

.job-queue-item-detail {
    color: #6c757d;
}


::v-deep(.p-progress-spinner-circle) {
    animation: p-progress-spinner-dash 1.5s ease-in-out infinite, custom-progress-spinner-color 6s ease-in-out infinite;
}

@keyframes custom-progress-spinner-color {
    100%,
    0% {
        stroke: #ffffff;
    }
    40% {
        stroke: #ffffff;
    }
    66% {
        stroke: #ffffff;
    }
    80%,
    90% {
        stroke: #ffffff;
    }
}

/* Topbar menu classes taken from Apollo Vue 3.3 */

.layout-container .topbar {
    background-color: #32679b;
    /* padding: 10px 16px; */
    height: 50px;
    box-sizing: border-box;
    position: fixed;
    top: 0px;
    width: 100%;
    z-index: 102;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

.layout-container .topbar .menu-button {
    cursor: pointer;
    vertical-align: top;
    height: 50px;
    width: 50px;
    line-height: 50px;
    text-align: center;
    margin-top: -10px;
    margin-left: 30px;
    color: #ffffff;
    user-select: none;
    transition: background-color 0.2s;
}

.layout-container .topbar .menu-button i {
    font-size: 28px;
    line-height: inherit;
}

.layout-container .topbar .menu-button:hover {
    background-color: #69b9f7;
}

.layout-container .topbar .profile {
    float: right;
    text-align: right;
    margin-top: -5px;
    font-weight: 700;
    cursor: pointer;
}

.layout-container .topbar .profile img {
    vertical-align: middle;
    width: 40px;
    margin-right: 8px;
}

.layout-container .topbar .profile .username {
    vertical-align: middle;
    margin-right: 8px;
    color: #ffffff;
}

.layout-container .topbar .profile .pi {
    font-size: 16px;
    vertical-align: middle;
    color: #ffffff;
}

.layout-container .topbar .topbar-menu {
    display: none;
    position: absolute;
    cursor: pointer;
    /* right: 0; */
    top: 50px;
    width: 250px;
    list-style-type: none;
    padding: 0;
    margin: 0;
    background-color: #32679b;
    animation-duration: 0.2s;
}

.layout-container .topbar .topbar-menu.topbar-menu-visible {
    display: block;
}

.layout-container .topbar .topbar-menu > li button {
    width: 100%;
    font-family: "Source Sans Pro", Arial, sans-serif;
    font-size: 14px;
    color: #ffffff;
    padding: 10px 16px;
}

.layout-container .topbar .topbar-menu > li button i {
    font-size: 16px;
    display: inline-block;
    vertical-align: middle;
}

.layout-container .topbar .topbar-menu > li button span {
    margin-left: 6px;
    display: inline-block;
    vertical-align: middle;
}

.layout-container .topbar .topbar-menu > li button img {
    display: inline-block;
    vertical-align: middle;
}

.layout-container .topbar .topbar-menu > li button .topbar-badge {
    float: right;
    background-color: #ffffff;
    display: block;
    color: #39a3f4;
    width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    margin-top: 1px;
    border-radius: 50%;
}

.layout-container .topbar .topbar-menu > li button:hover {
    background-color: #69b9f7;
}

.layout-container .topbar .topbar-menu > li ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.layout-container .topbar .topbar-menu > li ul button {
    padding-left: 32px;
}

.layout-container.layout-overlay .topbar .menu-button, .layout-container.layout-static .topbar .menu-button {
    display: inline-block;
}

.layout-container.layout-slim .topbar .menu-button, .layout-container.layout-horizontal .topbar .menu-button {
    display: inline-block;
}

/* Profile Menu Overrides */

.layout-container .topbar .topbar-menu {
    border-top: 1px solid #264e74;
    border-top-width: 1px;
    border-top-style: solid;
}

.profile-menu-item .topbar-item-name {
    flex: 1;
}

.topbar .profile-menu-item .p-overlay-badge {
    margin-right: 9px;
}

.layout-container .topbar .menu-button {
    width: auto;
    margin-top: 0; /* This overrides the apollo -10px but does not work with our flexbox model */
    margin-left: 5px; /* This overrides the apollo margin-left: 30px and balances the left and right spacing around the menu button in static and overlay menu mode. */
    margin-right: 5px;
    padding-left: 10px;
    padding-right: 10px;
}

.layout-container .topbar .menu-button:focus {
    outline: none;
    box-shadow: none;
}

.layout-container.layout-slim .topbar .menu-button, .layout-content-wrapper.layout-horizontal .topbar .menu-button {
    display: inline-block; /* Override hidden on slim and horizontal layout. Matches the behaviour for static menu, so that the menu button is visible on slim and horizontal mode, and we can use it to toggle between static, slim and horizontal. */
}

.layout-container .topbar .menu-button i {
    font-size: 20px;
}

.layout-container .topbar .logo-link {
    /* Overrides the fixed 185px width for the logo from PrimeVue */
    width: auto;
    max-width: 185px;
}

.company-logo-container {
    background: #ffffff;
    margin-left: 5px; /* Separates the logo from the left egde of the page in Slim Menu mode. */
    padding: 5px;
    border-radius: 4px;
}

.slash-line {
    position: relative;
    display: inline-block;
    margin-left: 20px;
    color: #ffffff;
}

.slash-line:before {
    content: "";
    position: absolute;
    top: -50%;
    left: -10px;
    height: 200%;
    width: 1px;
    background: #ffffff;
}

.profile {
    padding-left: 8px; /* Ensures the active outline box for accessibility looks nicer with a slight gap all around rather than no gap between border and text. */
    padding-right: 5px; /* Ensures the active outline box for accessibility looks nicer with a slight gap all around rather than no gap between border and text. */
    margin-right: 5px;
    margin-top: 0 !important;
}

.layout-container .topbar .logo-link .logo {
    height: 30px;
}

.topbar .profile-menu-item .p-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.layout-container.layout-overlay .topbar, .layout-container.layout-static .topbar {
    box-shadow: 0 2px 4px #0000004d;
}

::v-deep(.p-badge.p-badge-info) {
    background-color: #0288d1;
    color: #ffffff;
}

::v-deep(.p-badge) {
    background: #32689b;
    color: #ffffff;
    font-size: .75rem;
    font-weight: 700;
    min-width: 1.5rem;
    height: 1.5rem;
    line-height: 1.5rem;
}

.layout-container .topbar .left-group .menu-button:hover {
    background-color: #3f82c1;
}

.layout-container .topbar .topbar-menu>li button:hover {
    background-color: #3f82c1;
}

.icon-get-attention {
    animation: shrinkGrow 2s 15 ease-in-out;
}

@keyframes shrinkGrow {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.25);
    }
    100% {
        transform: scale(1);
    }
}
</style>