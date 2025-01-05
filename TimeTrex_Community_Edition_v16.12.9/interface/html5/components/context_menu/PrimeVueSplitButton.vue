<template>
    <div :class="containerClass" :style="style" :id="$attrs.id">
        <PVSButton type="button" class="p-splitbutton-defaultbutton" v-bind="$attrs" :id="defaultButtonId" :icon="icon" :label="label" :disabled="$attrs.disable_item" @click="onDefaultButtonClick" />
        <PVSButton type="button" class="p-splitbutton-menubutton" :id="$attrs.id+'-split-menu'" icon="pi pi-chevron-down" @click="onDropdownButtonClick" :disabled="$attrs.disable_menu"
                   aria-haspopup="true" :aria-controls="ariaId + '_overlay'"/>
        <PVSMenu :id="ariaId + '_overlay'" ref="menu" :model="model" :popup="true" :autoZIndex="autoZIndex"
                 :baseZIndex="baseZIndex" :appendTo="appendTo" />
    </div>
</template>

<script>
/* Copied from primevue version 3.1.1 */
/* This has been modified from the original PrimeVue SplitButton in order to add customizations to the disable state logic and allow the active button disabling to be independant from the dropdown arrow. */
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import {UniqueComponentId} from 'primevue/utils';

export default {
    inheritAttrs: false,
    props: {
        label: {
            type: String,
            default: null
        },
        icon: {
            type: String,
            default: null
        },
        model: {
            type: Array,
            default: null
        },
        autoZIndex: {
            type: Boolean,
            default: true
        },
        baseZIndex: {
            type: Number,
            default: 0
        },
        appendTo: {
            type: String,
            default: 'body'
        },
        class: null,
        style: null
    },
    methods: {
        onDropdownButtonClick() {
            this.$refs.menu.toggle({currentTarget: this.$el});
        },
        onDefaultButtonClick() {
            this.$refs.menu.hide();
        }
    },
    computed: {
        ariaId() {
            return UniqueComponentId();
        },
        containerClass() {
            return ['p-splitbutton p-component', this.class];
        },
        defaultButtonId() {
            return this.$attrs.id.replace('context-group', 'context-button');
        },
    },
    components: {
        'PVSButton': Button,
        'PVSMenu': Menu
    }
}
</script>

<style scoped>
.p-splitbutton {
    display: inline-flex;
    position: relative;
}

.p-splitbutton .p-splitbutton-defaultbutton {
    flex: 1 1 auto;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0 none;
}

.p-splitbutton-menubutton {
    display: flex;
    align-items: center;
    justify-content: center;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.p-splitbutton .p-menu {
    min-width: 100%;
}

.p-fluid .p-splitbutton  {
    display: flex;
}
</style>
