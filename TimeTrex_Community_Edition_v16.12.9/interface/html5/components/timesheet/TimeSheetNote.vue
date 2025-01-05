<template>
    <span v-show="show_add_note" class="note-icon tticon tticon-add_black_24dp" @click="noteIconClicked" ref="note_icon"></span>
    <div v-show="visible" class="note-dialog p-component" ref="dialog">
        <h5 class ="note-label">{{ label_note }}</h5>
        <span @click="this.visible = false" class="dialog-close">×</span>
        <TextArea v-model="note" rows="4"/>
        <Button type="submit" class="save-button" @click="saveNote">{{ label_save }}</Button>
    </div>
    <span v-show="note !== '' && !show_add_note" class="blue-circle-icon" ref="blue_circle"></span>
</template>
<script>
import Button from 'primevue/button';
import TextArea from 'primevue/textarea';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id
        } );

        this.event_bus.on( 'timesheet_note', 'close_other_notes', ( event_data ) => {
            if ( event_data.component_id !== this.component_id ) {
                this.visible = false;
            }
        } );
    },
    mounted() {
        this.setNoneVueEvents();
    },
    data() {
        return {
            visible: false,
            note: this.starting_note,
            label_save: $.i18n._( 'Save' ),
            label_note: $.i18n._( 'Note' ),
            show_add_note: false,
        };
    },
    props: {
        component_id: { /* Note: This is passed in via TTVueUtils.mountComponent param, and auto added to root_props. */
            type: String,
            default: null
        },
        id: {
            type: String,
            default: null
        },
        starting_note: {
            type: String,
            default: ''
        },
        //Cells with no hours do not have ID record attached, we need to link this note to a none existant user_date_total record.
        field_reference: {
            type: Object,
            default: null
        }
    },
    methods: {
        handleMouseOver() {
            this.show_add_note = true;
        },
        handleMouseLeave() {
            if ( this.field_reference[0] !== document.activeElement ) {
                this.show_add_note = false;
            }
        },
        setNoneVueEvents() {
            //Input element exists outside VueJS component, so we need to add event listeners manually outside VueJS context, otherwise it will only trigger on elements in this component.
            //These events need to be added back to VueJS @mouseover and @mouseleave when entire TimeSheet is converted to VueJS.
            let parent_td = this.$refs.note_icon.closest( 'td' );
            parent_td.addEventListener( 'mouseover', this.handleMouseOver );
            parent_td.addEventListener( 'mouseleave', this.handleMouseLeave );

            let input = parent_td.querySelector( 'input' );
            input.addEventListener( 'focus', this.handleMouseOver );
            input.addEventListener( 'blur', this.handleMouseLeave );
        },
        noteIconClicked() {
            this.visible = true;
            this.event_bus.emit( 'timesheet_note', 'close_other_notes', {
                component_id: this.component_id
            } );
        },
        saveNote() {
            this.event_bus.emit( this.component_id, 'saveNote', {
                note: this.note
            } );
            this.visible = false;
        }
    },
    components: {
        TextArea,
        Button
    }
};
</script>
<style scoped>
.p-inputtextarea {
    width: 100%;
}

.save-button {
    float: right;
    margin-top: 10px;
}

.note-icon {
    position: absolute;
    top: -5px;
    right: calc(50% - 36px);
    cursor: pointer;
    border: 2px solid #32689b;
    background: white;
    border-radius: 50%;
    z-index: 10;
    font-size: 15px !important;
}

.note-label {
    text-align: left;
    margin-bottom: 0.5rem;
}

.blue-circle-icon {
    position: absolute;
    top: 2px;
    right: calc(50% - 25px);
    width: 5px;
    height: 5px;
    background-color: blue;
    border-radius: 50%;
    z-index: 10;
}

.note-dialog {
    background-color: var(--surface-ground);
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    border: 0 none;
    margin-top: 30px;
    padding: 10px;
    z-index: 100;
    position: fixed;
    display: block;
}

.dialog-close {
    font-size: 1.75rem;
    position: absolute;
    right: -5px;
    top: -13px;
    margin: .75rem;
    cursor: pointer
}
</style>
