<template>
    <div class="custom-agreement-modal">
        <div class="custom-agreement-modal-content">
            <span v-if="!this.require_input" @click="closeModal" class="custom-agreement-modal-close">×</span><img class="custom-agreement-modal-image" :src="bell_img">
            <h2 class="custom-agreement-modal-title">{{ labels.title }}</h2>
            <form @submit="onFormSubmit" class="form-container" id="agreement-form">
                <div class="p-float-label agreement-container">
                    <div id="agreement-body" v-html="labels.body"></div>
                    <h5 v-if="this.require_input" class="agreement-label">{{ agreement_phrase_hint }}</h5>
                    <InputText v-if="this.require_input" id="passwor`d-input" autocomplete="off" :placeholder="labels.agreement_phrase" v-model="user_input_text" ref="agreement_input" :feedback="false" toggleMask></InputText>
                </div>
                <button type="submit" id="custom-agreement-submit-button" ref="accept_button" class="custom-agreement-modal-button" @click="onFormSubmit">{{ button_text }}</button>
            </form>
        </div>
    </div>
</template>
<script>
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';
import { Global } from '@/global/Global';
import TTVueUtils from '@/services/TTVueUtils';
import ProgressSpinner from 'primevue/progressspinner';
import bell_img from "@/theme/default/images/bell_permissions.svg";

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
    },
    mounted() {
        this.onVerifyInput();
    },
    props: { // passed in via root props when component is mounted
        view_id: {
            type: String,
            default: null
        },
        component_id: { /* Note: This is passed in via TTVueUtils.mountComponent param, and auto added to root_props. */
            type: String,
            default: null
        },
        session_type: {
            type: String,
            default: 'user_name'
        },
        user_name: {
            type: String,
            default: ''
        },
        custom_agreement_data: {
            type: Object,
            default: {}
        },
        authenticate_callback: {
            type: Function,
            default: null
        },
        is_reauthentication: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            step: this.custom_agreement_data.step,
            type_id: this.custom_agreement_data.type_id,
            require_input: this.custom_agreement_data.require_input,
            user_input_text: '',
            bell_img: bell_img,
            labels: {
                title: this.custom_agreement_data.title,
                body: this.custom_agreement_data.body ,
                agreement_phrase : this.custom_agreement_data.agreement_phrase,
            }
        };
    },
    computed: {
        button_text() {
            if ( this.step === 'accept_terms' ) {
                return $.i18n._( 'Continue' );
            }

            return $.i18n._( 'I agree' );
        },
        user_accepted() {
            return !this.require_input || this.user_input_text === this.labels.agreement_phrase;
        },
        agreement_phrase_hint() {
            return $.i18n.printf( 'Type "%s" below to continue.', [ this.labels.agreement_phrase ] );
        }

    },
    watch: {
        user_input_text() {
            this.onVerifyInput();
        }
    },
    methods: {
        onVerifyInput() {
            if ( this.user_accepted ) {
                if ( this.require_input ) {
                    this.$refs.agreement_input.$el.classList.remove( 'p-invalid' );
                }
                this.$refs.accept_button.disabled = false;
                this.$refs.accept_button.style.backgroundColor = '#426d9d';
                return true;
            } else {
                if ( this.require_input ) {
                    this.$refs.agreement_input.$el.classList.add( 'p-invalid' );
                }
                this.$refs.accept_button.disabled = true;
                this.$refs.accept_button.style.backgroundColor = '#a1a3a6';
                return false;
            }
        },
        onFormSubmit( event ) {
            event.preventDefault();

            switch ( this.step ) {
                case 'accept_terms':
                    if ( this.user_accepted ) {
                        if ( this.authenticate_callback ) {
                            this.authenticate_callback( true );
                        }

                        this.closeModal();
                    }
                    break;
            }
        },
        closeModal() {
            TTVueUtils.unmountComponent( this.component_id );
        },
    },
    components: {
        InputText,
        Textarea,
        Checkbox,
        ProgressSpinner
    }
};
</script>
<style scoped>
.custom-agreement-modal {
    background: rgba(0, 0, 0, .6);
    position: fixed;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.custom-agreement-modal-content {
    background-color: #f8f8f8;
    height: auto;
    width: 26rem;
    position: fixed;
    top: 50%;
    left: 50%;
    margin-top: -15rem;
    margin-left: -13rem;
    box-shadow: 5px 5px 8px 0px rgba(0, 0, 0, 0.3), 0 0 60px 5px rgba(0, 0, 0, 0.38);
}

.custom-agreement-modal-close {
    font-size: 1.5rem;
    position: absolute;
    right: 0;
    margin: .75rem;
    cursor: pointer
}

.custom-agreement-modal-button {
    background: #426d9d;
    border: 0px solid #a1a3a6;
    color: #ffffff;
    width: 17rem;
    font-size: 1.15rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    height: 3.25rem;
    margin-left: 4.5rem;
    margin-top: 2rem;
    text-decoration: none;
    font-weight: 1000;
}

.custom-agreement-modal-image {
    display: block;
    margin: 25px auto 15px auto;
    width: 80px;
    height: 80px;
}

.custom-agreement-modal-title {
    text-align: center;
    font-weight: 1000;
}

.custom-agreement-modal-body {
    text-align: center;
    height: 5rem;
    display: -webkit-flex;
    display: flex;
    align-items: center;
    padding-left: 3rem;
    padding-right: 3rem;
    font-size: 1.2rem;
}

.form-container .agreement-container {
    height: auto;
    width: 100%;
    left: 4.5rem;
    width: 17rem;
}

.agreement-label {
    margin: 1rem 0 1px 0;
    font-size: 0.90rem;
    font-weight: lighter;
}

::v-deep(.p-inputtextarea), ::v-deep(.p-inputtext) {
    width: 100%;
}
</style>