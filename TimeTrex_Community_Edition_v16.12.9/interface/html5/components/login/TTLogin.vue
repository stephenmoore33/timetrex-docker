<template>
    <div class="login-view login-view-bg">
        <div class="login-view-main-content">
            <div class="logo-container box-edge-spacing">
                <img class="company-logo" id="companyLogo" :src="company_logo" @click="onAppTypeClick" alt="Workforce Management Software"/>
                <img class="app-type" id="appTypeLogo" :src="app_logo" @click="onAppTypeClick" alt="Time and Attendance Software"/>
            </div>
            <div class="form-container" id="login-form">
                <hr class="hr-form-top">
                <table id="form" class="form box-edge-spacing">
                    <colgroup>
                        <col width="48%">
                        <col width="4%">
                        <col width="48%">
                    </colgroup>
                    <tr>
                        <td colspan="3" class="error-info">{{ error_message }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="secure-login" id="secure-login-text" style ="font-size: 1.5rem; font-weight: bold;">{{ labels.sign_in }}</td>
                    </tr>
                    <tr v-show="form_step === 'user_name'">
                        <td :colspan="user_name_colspan" style="padding: 25px 5px 0px 0px; font-size: 1rem;">
                            <label for="user_name" style="margin-top: 8px;">{{ labels.user_name }}</label>
                            <input tabindex="1" @keyup.enter="onFormSubmit" v-model="user_name" class="form-input" type="text" :placeholder="labels.user_name" :title="labels.user_name" id="user_name" name="username" autocomplete="username"/>
                        </td>
                        <td v-show="show_language_selector" colspan="1" style="padding: 25px 5px 0px 0px; font-size: 1rem;">
                            <label for="language-selector" style="margin-top: 8px;">{{ labels.language }}</label>
                            <select class="form-selector language-selector" @change="onLanguageChange" style="margin-left: 5px;" id="language-selector" :title="labels.language">
                            </select>
                        </td>
                    </tr>
                    <tr v-show="form_step === 'password'" style="padding: 25px 5px 0px 0px; font-size: 1rem;">
                        <td colspan="3">
                            <div style="margin-bottom: 8px;">
                                <a href="javascript:void(0)" id="change_user_name" @click="onChangeUserNameClick">{{ this.change_user_name_label }}</a>
                            </div>
                            <label for="password">{{ labels.password }}</label>
                            <Password id="password-input" tabindex="3" @keyup.enter="onFormSubmit" :placeholder="labels.password" v-model="user_password" ref="password_input" :feedback="false" toggleMask></Password>
                        </td>
                    </tr>
                    <tr v-if="form_step === 'password'">
                        <td colspan="3">
                            <span id="forgot_password" class="forgot-password" @click="onForgotPasswordClick">{{ labels.forgot_your_password }}?</span>
                        </td>
                    </tr>
                    <tr v-if="form_step === 'user_name'">
                        <td colspan="3">
                            <span id="forgot_user_name" class="forgot-password" @click="onForgotPasswordClick">{{ labels.forgot_user_name }}?</span>
                        </td>
                    </tr>
                </table>
                <hr>
                <table id="form2" class="form button-form box-edge-spacing">
                    <colgroup>
                        <col width="48%">
                        <col width="4%">
                        <col width="48%">
                    </colgroup>
                    <tr>
                        <td v-if="form_step === 'user_name'" class="login-button-container">
                            <button type="submit" @click="onFormSubmit" tabindex="2" id="next_btn" class="login-button">
                                {{ labels.next }}
                            </button>
                        </td>
                        <td v-if="form_step === 'password'" class="login-button-container">
                            <button type="submit" @click="onFormSubmit" tabindex="4" id="login_btn" class="login-button">
                                {{ labels.login }}
                            </button>
                        </td>
                        <td>&nbsp;</td>
                        <td v-if="product_edition_id > 10" class="quick-punch-button-container">
                            <button type="button" @click="onQuickPunchClick" id="quick_punch" class="quick-punch-button">
                                {{ labels.quick_punch }}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="versionNumber" class="version-label"> {{ labels.version }}</div>
        </div>
        <div id="login-footer">
            <div id="social_div" class="social-div">
                <a href="https://www.timetrex.com/r?id=451" target="_blank"><img class="facebook-img" border="0"></a>
                <a href="https://www.timetrex.com/r?id=455" target="_blank"><img class="twitter-img" border="0"></a>
            </div>
            <div class="logo_container_powered_by"><img id="powered_by" alt="Workforce Management Software"/></div>
            <div class="logo_container_copyright">
                <span id="login_copy_right_info" class="copy-right-info-1 notranslate"></span></div>
        </div>
    </div>
</template>
<script>
import InputText from 'primevue/inputtext';
import { Global } from '@/global/Global';
import Password from 'primevue/password';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );

        this.event_bus.on( 'global', 'reset_vue_data', this.resetData );
        this.event_bus.on( 'tt_login', 'user_closed_mfa_modal', () => this.doing_login = false );
        this.event_bus.on( 'tt_login', 'step_changed', () => {
            let step = LocalCacheData.getAllURLArgs().step;
            if ( !this.login_complete && step !== this.form_step ) {
                if ( step ) {
                    this.setFormStep( step );
                } else {
                    this.setFormStep( 'user_name' );
                }
            }
        } );
    },
    mounted() {
        Global.showPoweredBy();
        Global.setURLToBrowser( Global.getBaseURL() + '#!m=Login&step=' + this.form_step );
        this.onFormStepChange();

        if ( this.show_language_selector ) {
            this.setLanguageSelector();
        }

        //NOTE: Usernameless login without a pre-emptive click (user action) causes logout to immediately try and login again.
        //This device has used or enrolled in passkey passwordless login before, and we can automatically start that process again instead of asking for their username.
        // if ( getCookie( 'PasskeyUsername' ) !== '' ) {
        //     TTWebauthn.loginUserNameLess( this.authenticate_callback );
        // }

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
        default_error_message: {
            type: String,
            default: null
        },
        default_user_name: {
            type: String,
            default: null
        },
        default_user_password: {
            type: String,
            default: null
        },
        default_form_step: {
            type: String,
            default: 'user_name'
        },
        show_language_selector: {
            type: Boolean,
            default: false
        },
        authenticate_callback: {
            type: Function,
            default: null
        }
    },
    data() {
        return {
            form_step: this.default_form_step || 'user_name',
            product_edition_id: LocalCacheData.productEditionId,
            user_name: this.default_user_name,
            user_password: this.default_user_password,
            error_message: this.default_error_message,
            authentication_api: TTAPI.APIAuthentication,
            session_type: '',
            mfa_type_id: 0,
            doing_login: false,
            login_complete: false,
            labels: {
                sign_in: $.i18n._( 'Sign in' ),
                user_name: $.i18n._( 'User Name' ),
                password: $.i18n._( 'Password' ),
                forgot_your_password: $.i18n._( 'Forgot Your Password' ),
                forgot_user_name: $.i18n._( 'Forgot Your User Name' ),
                quick_punch: $.i18n._( 'Quick Punch' ),
                login: $.i18n._( 'Sign in' ),
                next: $.i18n._( 'Next' ),
                language: $.i18n._( 'Language' ),
                version: 'v' + APIGlobal.pre_login_data.application_build
            }
        };
    },
    computed: {
        company_logo() {
            return ServiceCaller.getURLByObjectType( 'primary_company_logo' );
        },
        app_logo() {
            let url = 'theme/' + Global.theme;

            if ( Global.url_offset ) {
                url = Global.url_offset + url;
            }

            url += '/css/views/login/images/';

            if ( LocalCacheData.productEditionId > 10 && LocalCacheData.deployment_on_demand === true ) {
                url += 'od.png';
            } else if ( LocalCacheData.productEditionId === 15 ) {
                url += 'beo.png';
            } else if ( LocalCacheData.productEditionId === 20 ) {
                url += 'peo.png';
            } else if ( LocalCacheData.productEditionId === 25 ) {
                url += 'eeo.png';
            } else {
                url += 'seo.png';
            }

            url += '?v=' + APIGlobal.pre_login_data.application_build; //Helps with cache busting.

            return url;
        },
        user_name_colspan() {
            return this.show_language_selector ? '2' : '3';
        },
        change_user_name_label() {
            return this.user_name + ' ' + $.i18n._( '(Switch User Name?)' );
        },
    },
    methods: {
        resetData() {
            Object.assign( this.$data, this.$options.data() );
        },
        onFormSubmit( event ) {
            event.preventDefault();
            this.handleFormStep();
        },
        handleFormStep() {
            //Remove error message as user action has taken place and may no longer be accurate.
            this.error_message = null;

            switch ( this.form_step ) {
                case 'user_name':
                    if ( this.user_name.length > 0 ) {
                        Global.getSessionTypeForLogin( this.user_name, ( result ) => {
                            this.session_type = result.session_type;
                            this.mfa_type_id = result.mfa_type_id;
                            if ( this.mfa_type_id == 1000 ) { //SAML
                                TTSAML.loginUser( this.user_name, result.redirect_url );
                            } else if ( this.mfa_type_id == 100 ) { //Webauthn
                                TTWebauthn.loginUser( result.user_id, this.user_name, this.authenticate_callback );
                            } else {
                                this.setFormStep( 'password' );
                            }
                        } );
                    } else {
                        this.error_message = $.i18n._( 'Please enter a valid user name.' );
                    }
                    break;
                case 'password':
                    if ( this.user_password.length > 0 ) {
                        if ( this.session_type === '' ) {
                            Global.getSessionTypeForLogin( this.user_name, ( result ) => {
                                this.session_type = result.session_type;
                                this.mfa_type_id = result.mfa_type_id;
                            this.login();
                        } );
                    } else {
                        this.login();}
                    } else {
                        this.error_message = $.i18n._( 'Please enter a password.' );
                    }
                    break;
                default:
                    break;
            }
        },
        setFormStep( form_step ) {
            //Remove error message anytime form step is set. Such as when going from username to password field.
            this.error_message = null;

            //Help ensure we do not set an invalid form step.
            if ( !form_step ) {
                form_step = 'user_name';
            }

            Global.setURLToBrowser( Global.getBaseURL() + '#!m=Login&step=' + form_step );
            this.form_step = form_step;
            setTimeout( () => {
                //Timeout so that Vue triggers and udates form layout before we interact with the form.
                this.onFormStepChange();
            }, 100 );
        },
        onFormStepChange() {
            switch ( this.form_step ) {
                case 'user_name':
                    let user_name_input = document.querySelector( '#user_name' );
                    if ( user_name_input ) {
                        user_name_input.focus();
                        //Issue #3373 - Checking element is visible to prevent: Error: Uncaught InvalidStateError: Failed to execute 'setSelectionRange' on 'HTMLInputElement': The input element's type ('hidden') does not support selection
                        if ( user_name_input.offsetParent ) {
                            user_name_input.setSelectionRange(0, 0);
                        }
                    }
                    break;
                case 'password':
                    let password_input = document.querySelector( '#password-input input' );
                    if ( password_input ) {
                        password_input.focus();
                        //Issue #3373 - Checking element is visible to prevent: Error: Uncaught InvalidStateError: Failed to execute 'setSelectionRange' on 'HTMLInputElement': The input element's type ('hidden') does not support selection
                        if ( password_input.offsetParent ) {
                            password_input.setSelectionRange(0, 0);
                        }
                    }
                    break;
            }
        },
        onAppTypeClick() {
            window.open( 'https://' + LocalCacheData.loginData.organization_url );
        },
        onQuickPunchClick() {
            window.open( ServiceCaller.root_url + LocalCacheData.loginData.base_url + 'html5/quick_punch/' );
        },
        onForgotPasswordClick() {
            IndexViewController.openWizard( 'ForgotPasswordWizard', null, function() {
                TAlertManager.showAlert( $.i18n._( 'An email has been sent to you with instructions on how to reset your password.' ) );
            } );
        },
        onChangeUserNameClick() {
            this.setFormStep( 'user_name' );
        },
        onLanguageChange( event ) {
            Global.setLanguageCookie( event.target.value );
            LocalCacheData.setI18nDic( null );

            ProgressBar.showProgressBar( TTUUID.generateUUID() );
            ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

            setTimeout( function() {
                window.location.reload();
            }, 2000 );
        },
        setLanguageSelector() {
            this.lan_selector = $( '.language-selector' );
            this.lan_selector.TComboBox();
            var array = Global.buildRecordArray( ( LocalCacheData.getLoginData().language_options ) );

            this.lan_selector.setSourceData( array );
            this.lan_selector.setValue( LocalCacheData.getLoginData().language );
        },
        login() {
            if ( this.doing_login == true ) {
                return;
            }

            this.doing_login = true;

            Global.login( this.user_name, this.user_password, this.session_type, false, ( result ) => {
                this.doing_login = false;
                this.login_complete = true;
                Global.setURLToBrowser( Global.getBaseURL() + '#!m=Login' ); //Remove step as it would cause login to fail sometimes until page eas refreshed.
                this.authenticate_callback( result );
            } );
        },
    },
    components: {
        InputText,
        Password
    }
};
</script>
<style scoped>
.company-logo {
    max-height: 78px;
    max-width: 286px;
}

::v-deep(.p-password) {
    display: block !important;
    margin: 0.25rem 0 !important;
}

input, select {
    margin: 0.25rem 0 !important;
}

#secure-login-text {
    text-align: center;
}
</style>