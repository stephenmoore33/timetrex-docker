<template>
    <div id="contentContainer" class="content-container">
        <div class="test-view">
            <Card>
                <template #title>
                    Vue Testing View
                </template>
                <template #subtitle>
                    This is a test view. The test div below has id: #tt-edit-view-test
                </template>
                <template #content>
                    <div id="tt-edit-view-test">
                        <a class="p-link" @click="initEditTest">Click here to load edit-fields component dynamically</a>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script>
// #VUETEST
import Card from 'primevue/card';
import { createApp } from 'vue';
import TTEditView from '@/components/TTEditView';
import PrimeVue from 'primevue/config';

export default {
    components: {
        Card
    },
    methods: {
        initEditTest() {
            let tt_test = createApp( TTEditView ); // Can pass an object in here too for proper JS only code, and allow data in without eventBus..
            tt_test.use( PrimeVue, { ripple: true, inputStyle: 'filled' }); // From: AppConfig.vue this.$primevue.config.inputStyle value is filled/outlined as we dont use AppConfig in TT.
            tt_test.mount( '#tt-edit-view-test' );

            // End of Vue initialization
        }
    }
};
</script>

<style scoped>
.test-view {
    padding: 20px;
    width: calc( 100% - 230px )
}
#tt-edit-view-test {
    padding: 10px;
    border: 1px solid #979797;
    margin-top: 10px;
}

</style>