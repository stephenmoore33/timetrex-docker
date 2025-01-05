<template>
    <div class="mfa-modal">
        <div class="mfa-modal-content">
            <span @click="closeModal" class="mfa-modal-close">×</span><img class="mfa-modal-image" :src="bell_img">
            <h2 class="mfa-modal-title">{{ labels.title }}</h2>
            <p v-show="this.step != 'password'" class="mfa-modal-body">{{ labels.body }}</p>
            <ProgressSpinner v-if="show_spinner" style="width:50px;height:50px" strokeWidth="5" animationDuration="2s"/>
            <form @submit="onFormSubmit" class="form-container" id="login-form">
                <div v-show="this.step === 'password'" class="p-float-label password-container">
                    <h5 class="password-label">{{ labels.current_password }}</h5>
                    <Password id="password-input" :placeholder="labels.password" v-model="user_password" ref="password_input" :feedback="false" toggleMask></Password>
                </div>
                <div v-show="show_trusted_device_checkbox" class="checkbox-container">
                    <Checkbox inputId="remember-me" @change="toggleTrustedDevice" v-model="enable_trusted_device" :binary="true"/>
                    <label class="checkbox-center" for="remember-me">{{ labels.remember_me }}</label>
                </div>
                <button type="submit" id="mfa-submit-button" class="mfa-modal-button" :class="{ 'resend-button': this.step === 'start_listen' }" @click="onFormSubmit">{{ button_text }}</button>
            </form>
        </div>
    </div>
</template>
<script>
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import { Global } from '@/global/Global';
import TTVueUtils from '@/services/TTVueUtils';
import ProgressSpinner from 'primevue/progressspinner';
import Password from 'primevue/password';
import bell_img from "@/theme/default/images/bell_permissions.svg";

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
    },
    mounted() {
        this.onMFAStepChange();
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
        mfa_data: {
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
            doing_login: false,
            show_spinner: false,
            resending_notification: false,
            step: this.mfa_data.step,
            type_id: this.mfa_data.type_id,
            user_password: '',
            authentication_api: TTAPI.APIAuthentication,
            enable_trusted_device: getCookie( 'TrustedDevice' ) !== '', //If cookie is set, default checkbox to true.
            bell_img: bell_img,
            labels: {
                title: $.i18n._( 'Authentication Required' ),
                body: this.mfa_data.user_action_message,
                password: $.i18n._( 'Password' ),
                current_password: $.i18n._( 'Current Password' ),
                remember_me: $.i18n._( 'This is a trusted device. Remember Me' ),
            }
        };
    },
    computed: {
        button_text() {
            if ( this.resending_notification === true ) {
                return $.i18n._( 'Resending...' );
            } else if ( this.step === 'password' ) {
                return $.i18n._( 'Confirm Password' );
            } else if ( this.step === 'start_listen' ) {
                return $.i18n._( 'Resend' );
            } else if ( this.step === 'saml' ) {
                return $.i18n._( 'Verify' );
            }
        },
        show_trusted_device_checkbox() {
            //If cookie is set, don't show checkbox. Also only show during login and not re-authentication.
            return getCookie( 'TrustedDevice' ) === '' && ( this.step === 'password' || this.step === 'start_listen' ) && this.is_reauthentication === false;
        }
    },
    methods: {
        onMFAStepChange() {
            switch ( this.step ) {
                case 'saml':
                case 'password':
                    break;
                case 'start_listen':
                    this.doMultiFactorAuthentication();
                    break;
                case 'login':
                    this.login();
                    break;
            }
        },
        setMFAStep( step ) {
            this.step = step;
            setTimeout( () => {
                //Timeout so that Vue triggers and udates form layout before we interact with the form.
                this.onMFAStepChange();
            }, 100 );
        },
        onFormSubmit( event ) {
            event.preventDefault();

            switch ( this.step ) {
                case 'saml':
                    TTSAML.loginUser( this.user_name, this.mfa_data.redirect_url, true );
                    this.startSAMLListen();
                    break;
                case 'password':
                    this.login();
                    break;
                case 'start_listen': //Resend
                    this.sendMultiFactorNotification();
                    let submit_button = document.querySelector( '#mfa-submit-button' );
                    submit_button.disabled = true;
                    this.resending_notification = true;
                    setTimeout( () => {
                        submit_button.disabled = false;
                        this.resending_notification = false;
                    }, 3000 );
                    break;
            }
        },
        toggleTrustedDevice() {
            //Cancels the server listen loop and immediately restarts with $enable_trusted_device set to the users choice.
            //This simplifies the process of notifying the server that the user wants to use a trusted device during an otherwise long running loop.
            this.authentication_api.validateMultiFactor( false, LocalCacheData.getSessionID(), '', { restart_listen: true }, {
                onResult: ( res ) => {
                    //We are not doing anything with this result.
                }
            } );
        },
        sendMultiFactorNotification() {
            this.authentication_api.sendMultiFactorNotification( {
                onResult: ( res ) => {
                    //We are not doing anything with this result.
                }
            } );
        },
        startMultiFactorListen( repeat_count ) {
            let api_endpoint = 'listenForMultiFactorAuthentication';

            if ( this.type_id >= 1000 ) {
                api_endpoint = 'listenForReauthentication';
            }
            this.authentication_api[api_endpoint]( this.enable_trusted_device, {
                onResult: ( response ) => {
                    if ( response.isValid() ) {
                        let result = response.getResult();
                        if ( result.status === 'completed' ) {
                            this.closeModal();
                            this.authenticate_callback( true );
                        } else if ( result.status === 'restart_listen' ) {
                            this.restartListen( repeat_count );
                        } else if ( result.status === 'cancelled' ) {
                            this.closeModal();
                            TAlertManager.showAlert( $.i18n._( 'Multifactor authentication has been cancelled. Please try again.', $.i18n._( 'Multifactor Authentication Cancelled' ) ) );
                        }
                    } else {
                        this.closeModal();
                    }
                },
                onError: ( err ) => {
                    this.closeModal();
                }
            } );
        },
        restartListen( repeat_count ) {
            if ( repeat_count < 12 ) {
                this.startMultiFactorListen( repeat_count + 1 );
            } else {
                this.closeModal();
                TAlertManager.showAlert( $.i18n._( 'Multifactor authentication timed out. Please try again.', $.i18n._( 'Multifactor Authentication Timed Out' ) ) );
            }
        },
        doMultiFactorAuthentication() {
            this.show_spinner = true;
            this.sendMultiFactorNotification();
            this.startMultiFactorListen( 1 );

            ProgressBar.removeProgressBar();
        },
        startSAMLListen() {
            this.show_spinner = true;
            this.startMultiFactorListen( 1 );

            ProgressBar.removeProgressBar();
        },
        cancelMultiFactorListen() {
            if ( Array.isArray( $.xhrPool ) ) {
                for ( let i = 0; i < $.xhrPool.length; i++ ) {
                    if ( $.xhrPool[i].url.includes( 'listenForMultiFactorAuthentication' ) ) {
                        $.xhrPool[i].jqXHR.abort();
                    }
                }
            }
        },
        closeModal() {
            this.event_bus.emit( 'tt_login', 'user_closed_mfa_modal', {
                status: true
            } );
            this.show_spinner = false;
            this.cancelMultiFactorListen();
            TTVueUtils.unmountComponent( this.component_id );
        },
        login() {
            if ( this.doing_login == true ) {
                return;
            }

            this.doing_login = true;

            let login_session_type = this.session_type;
            //Force session type MFA. This is required for initial MFA setup and upgrading normal sessions to MFA sessions.
            if ( this.session_type != 'user_name_multifactor' && this.type_id > 0 ) {
                login_session_type = 'user_name_multifactor';
            }

            Global.login( this.user_name, this.user_password, login_session_type, this.is_reauthentication, ( res ) => {
                this.doing_login = false;

                if ( res.isValid() ) {
                    let result = res.getResult();

                    if ( this.type_id == 0 ) { //0 = Password (Not MFA)
                        this.doing_login = false;
                        this.authenticate_callback( true );
                    } else if ( this.type_id == 10 && result.mfa.step == 'start_listen' ) { // 10 = MFA App
                        this.setMFAStep( 'start_listen' );
                    }
                }
            } );
        },
    },
    components: {
        InputText,
        Checkbox,
        ProgressSpinner,
        Password
    }
};
</script>
<style scoped>
.checkbox-container {
    display: table;
    margin-top: 0.25rem;
}

.checkbox-container .checkbox-center {
    display: table-cell;
    vertical-align: middle;
}

.mfa-modal {
    background: rgba(0, 0, 0, .6);
    position: fixed;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.mfa-modal-content {
    background-color: #f8f8f8;
    height: 30rem;
    width: 26rem;
    position: fixed;
    top: 50%;
    left: 50%;
    margin-top: -15rem;
    margin-left: -13rem;
    box-shadow: 5px 5px 8px 0px rgba(0, 0, 0, 0.3), 0 0 60px 5px rgba(0, 0, 0, 0.38);
}

.mfa-modal-close {
    font-size: 1.5rem;
    position: absolute;
    right: 0;
    margin: .75rem;
    cursor: pointer
}

.mfa-modal-button {
    background: #426d9d;
    border: 0px solid #a1a3a6;
    color: #ffffff;
    width: 17rem;
    font-size: 1.15rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    height: 3.25rem;
    position: absolute;
    left: 4.5rem;
    bottom: .5rem;
    text-decoration: none;
    font-weight: 1000;
}

.mfa-modal-image {
    display: block;
    margin: 25px auto 15px auto;
    width: 80px;
    height: 80px;
}

.mfa-modal-title {
    text-align: center;
    font-weight: 1000;
}

.mfa-modal-body {
    text-align: center;
    height: 5rem;
    display: -webkit-flex;
    display: flex;
    align-items: center;
    padding-left: 3rem;
    padding-right: 3rem;
    font-size: 1.2rem;
}

.form-container .password-container {
    position: absolute;
    top: 55%;
    width: 100%;
    left: 4.5rem;
    width: 17rem;
}

.form-container .checkbox-container {
    position: absolute;
    width: 100%;
    bottom: 6.5rem;
    left: 4.5rem;
}

.resend-button {
    background: #ffffff;
    color: #000000;
    border: 1px solid #000000;
}

.password-label {
    margin: 0 0 1px 0;
    font-size: 1rem;
    font-weight: lighter;
}

::v-deep(.mfa-modal-content .p-password input) {
    width: 17rem;
}

::v-deep(.p-progress-spinner-circle) {
    animation: p-progress-spinner-dash 1.5s ease-in-out infinite, custom-progress-spinner-color 6s ease-in-out infinite;
}

@keyframes custom-progress-spinner-color {
    100%,
    0% {
        stroke: #000000;
    }
    40% {
        stroke: #000000;
    }
    66% {
        stroke: #000000;
    }
    80%,
    90% {
        stroke: #000000;
    }
}

::v-deep(.p-progress-spinner-svg) {
    width: 50px;
    height: 50px;
    margin-bottom: 80px;
}

::v-deep(.p-progress-spinner) {
    position: relative;
    top: 16%;
    left: 44%;
}
</style>