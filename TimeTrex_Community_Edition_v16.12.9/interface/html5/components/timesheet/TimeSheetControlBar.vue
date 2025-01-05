<template>
    <div class="tt-horizontal-vue-bar">
        <div class="bar-column left"></div>
        <div class="bar-column center">
            <div class="bar-column date-chooser-div"></div>
            <div class="bar-column employee-nav-div">
                <span class="navigation-label"></span>
                <img class="left-click"/>
                <div class="navigation-widget-div"></div>
                <img class="right-click"/>
            </div>
        </div>
        <div class="bar-column right">
            <div class="bar-column punch-manual">
                <SelectButton v-model="punch_mode_selected" :options="punch_modes" optionLabel="label" optionValue="value" @click="this.onPunchModeChange();"/>
            </div>
            <div class="bar-column menu-item">
                <TTContextButton :class="['no-wrap']" :items="timesheet_settings_options" />
            </div>
        </div>
    </div>
</template>

<script>
import SelectButton from 'primevue/selectbutton';
import TTContextButton from '@/components/context_menu/TTContextButton';

export default {
    name: "TimeSheetControlBar",
    props: {
        component_id: { // passed in via root props from TimeSheetViewController
            type: String,
            default: null
        },
        onPunchModeChange: { // passed in via root props from TimeSheetViewController
            type: Function,
            default: null
        },
        onShowWageClick: { // passed in via root props from TimeSheetViewController
            type: Function,
            default: null
        },
        onTimezoneClick: { // passed in via root props from TimeSheetViewController
            type: Function,
            default: null
        }
    },
    data() {
        return {
            punch_mode_selected: 'punch',
            punch_modes: [
                { label: $.i18n._( 'Punch' ), value: 'punch' },
                { label: $.i18n._( 'Manual' ), value: 'manual' },
            ],
            timesheet_settings_options: [
                {
                    label: $.i18n._( 'Show Wages' ),
                    id: 'show_wages',
                    no_group_label: true,
                    vue_icon: 'tticon tticon-settings_black_24dp',
                    action_group: 'timesheet_settings',
                    multi_select_group: 1,
                    visible: PermissionManager.checkTopLevelPermission( 'Wage' ),
                    command: () => {
                        if( this.onShowWageClick && typeof this.onShowWageClick === 'function' ) {
                            this.onShowWageClick( this.timesheet_settings_options[0].active );
                        }
                    }
                },
                {
                    label: $.i18n._( 'Use Employee Timezone' ),
                    id: 'use_employee_timezone',
                    no_group_label: true,
                    vue_icon: 'tticon tticon-settings_black_24dp',
                    action_group: 'timesheet_settings',
                    multi_select_group: 2,
                    visible: ( PermissionManager.validate( 'punch', 'view' ) || PermissionManager.validate( 'punch', 'view_child' ) ),
                    command: () => {
                        if( this.onTimezoneClick && typeof this.onTimezoneClick === 'function' ) {
                            this.onTimezoneClick( this.timesheet_settings_options[1].active );
                        }
                    }
                },
            ]
        }
    },
    // watch: {
    //     punch_mode_selected: function ( val ) {
    //         if( this.onPunchModeChange && typeof this.onPunchModeChange === 'function' ) {
    //             this.onPunchModeChange( val, false );
    //         }
    //     },
    // },
    computed: {
        getPunchMode() { // This way the value is cached if it doesnt change.
            return this.punch_mode_selected;
        }
    },
    methods: {
        setPunchMode( new_value ) {
            if ( new_value === 'punch' || new_value === 'manual' ) { // validate the input potentially coming from outside Vue.
                this.punch_mode_selected = new_value;
                return true;
            } else {
                Debug.Error( 'Invalid parameters passed to function: ', 'TimeSheetControlBar.vue', 'TimeSheetControlBar', 'setPunchMode', 1 );
                return false;
            }
        },
        getTimesheetSettingsState( item_id ) {
            var item = this.timesheet_settings_options.find( element => element.id === item_id );
            if( item ) {
                return item.active;
            } else {
                Debug.Error( 'Item not found ('+ item_id +'). Check supplied id.', 'TimeSheetControlBar.vue', 'TimeSheetControlBar', 'getTimesheetSettingsState', 1 );
                return undefined;
            }
        },
        setTimesheetSettingsState( item_id, value ) {
            var item = this.timesheet_settings_options.find( element => element.id === item_id );
            if( item ) {
                item.active = value;
            } else {
                Debug.Error( 'Item not found ('+ item_id +'). Check supplied id.', 'TimeSheetControlBar.vue', 'TimeSheetControlBar', 'setTimesheetSettingsState', 1 );
            }
        }
    },
    components: {
        SelectButton: SelectButton,
        TTContextButton: TTContextButton
    }
};
</script>

<style scoped>
    .tt-horizontal-vue-bar {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        margin-top: 3px;
    }
    .bar-column {
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 0 5px;
    }
    .bar-column.left,
    .bar-column.right {
        width: 25%;
    }
    .bar-column.right {
        justify-content: flex-end;
    }
    ::v-deep(.punch-manual .p-button) {
        padding: .55rem .6rem;
    }
    ::v-deep(.p-button-label) {
        font-size: 12px;
    }
</style>