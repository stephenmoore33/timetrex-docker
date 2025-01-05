<template>
    <div class="tt-horizontal-vue-bar">
        <div class="bar-column left"></div>
        <div class="bar-column center">
            <div class="date-chooser-div">
            </div>
        </div>
        <div class="bar-column right">
            <div class="bar-column schedule-mode">
                <SelectButton v-model="schedule_mode_options_selected" :options="schedule_mode_options" optionLabel="label" optionValue="value" @click="this.onScheduleModeChange();"/>
            </div>
            <div class="bar-column menu-item">
                <TTContextButton :class="['no-wrap']" :items="schedule_settings_options" />
            </div>
        </div>
    </div>
</template>

<script>
import SelectButton from 'primevue/selectbutton';
import TTContextButton from '@/components/context_menu/TTContextButton';

export default {
    name: "ScheduleControlBar",
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id
        } );
        this.event_bus.on( this.component_id, 'getValue', this.getValue, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
        this.event_bus.on( this.component_id, 'setValue', this.setValue, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
        this.event_bus.on( this.component_id, 'setScheduleSettingsState', this.setScheduleSettingsState, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
        this.event_bus.on( this.component_id, 'setSettingActive', this.setSettingActive, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
        this.event_bus.on( this.component_id, 'setSettingDeactivated', this.setSettingDeactivated, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
    },
    unmounted() {
        Debug.Text( 'Vue control bar component unmounted ('+ this.component_id +').', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'unmounted', 2 );
        this.event_bus.autoClear();
    },
    props: { // passed in via root props from TimeSheetViewController
        view_id: {
            type: String,
            default: null
        },
        component_id: { /* Note: This is passed in via TTVueUtils.mountComponent param, and auto added to root_props. */
            type: String,
            default: null
        },
        schedule_mode_options: {
            type: Array,
            default: []
        },
        schedule_settings_options: {
            type: Array,
            default: []
        },
    },
    data() {
        return {
            removeEventsOnUnmount: [],
            schedule_mode_options_selected: 'week',
        }
    },
    // watch: {
    //     schedule_mode_options_selected: function ( val ) {
    //         this.event_bus.emit( this.component_id, 'scheduleModeOnChange', {
    //             key: 'schedule_mode_options_selected',
    //             value: val
    //         });
    //     },
    // },
    computed: {
    },
    methods: {
        // getValue( event_data ) {
        //     // Validate
        //     if ( event_data.key && ['schedule_mode_options_selected'].includes( event_data.key) ) {
        //         EventBus.emit( this.vue_control_bar_id +'.getValueReturn', {
        //             key: event_data.key,
        //             value: this[ event_data.key ]
        //         });
        //         return this[ event_data.key ];
        //     } else if ( event_data.key && ['all_employee_btn', 'daily_totals_btn', 'weekly_totals_btn', 'strict_range_btn'].includes( event_data.key) ) {
        //
        //     } else {
        //         // invalid
        //     }
        // },
        setValue( event_data ) {
            var valid_editable_fields = ['schedule_mode_options_selected'];
            // Validate
            if( event_data.key && event_data.key && valid_editable_fields.includes( event_data.key) ) {
                this[ event_data.key ] = event_data.value;
            } else {
                // invalid
                Debug.Error( 'Invalid parameters passed to function.', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'setValue', 1 );

            }
        },
        getScheduleSettingsState( item_id ) {
            var item = this.schedule_settings_options.find( element => element.id === item_id );
            if( item ) {
                return item.active;
            } else {
                Debug.Error( 'Item not found ('+ item_id +'). Check supplied id.', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'getScheduleSettingsState', 1 );
                return undefined;
            }
        },
        setScheduleSettingsState( event_data ) {
            var item = this.schedule_settings_options.find( element => element.id === event_data.item_id );
            if( item ) {
                item[ event_data.item_field ] = event_data.item_value;
            } else {
                Debug.Error( 'Item not found ('+ event_data.item_id +'). Check supplied id.', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'setScheduleSettingsState', 1 );
            }
        },
        setSettingActive( event_data ) {
            var item = this.schedule_settings_options.find( element => element.id === event_data.item_id );
            if( item ) {
                item.setOnlySelfActive();
            } else {
                Debug.Error( 'Item not found ('+ event_data.item_id +'). Check supplied id.', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'setSettingActive', 1 );
            }
        },
        setSettingDeactivated( event_data ) {
            var item = this.schedule_settings_options.find( element => element.id === event_data.item_id );
            if( item ) {
                item.setOnlySelfDeactivated();
            } else {
                Debug.Error( 'Item not found ('+ event_data.item_id +'). Check supplied id.', 'ScheduleControlBar.vue', 'ScheduleControlBar', 'setOnlySelfDeactivated', 1 );
            }
        },
        onScheduleModeChange() {
            this.event_bus.emit( this.component_id, 'scheduleModeOnChange', {
                key: 'schedule_mode_options_selected',
                value: this.schedule_mode_options_selected
            });
        },
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
    ::v-deep(.schedule-mode .p-button) {
        padding: .55rem .6rem;
    }
    ::v-deep(.p-button-label) {
        font-size: 14px;
    }
</style>