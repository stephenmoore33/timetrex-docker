<template>
    <div class="ai-modal" ref="modal" v-show="is_visible" @mouseleave="hideAnnotation">
        <span @click="hideModal" class="ai-modal-close">×</span>
        <div
            class="ai-modal-resize-handle"
            @mousedown="startResizing"
        ></div>
        <h2 class="ai-modal-title">{{ labels.title }}</h2>
        <div class="annotation-overlay" ref="annotation_overlay" @mouseleave="hideAnnotation"></div>
        <div class="ai-modal-body" ref="ai-body" v-if="!is_community_edition">
            <div class="conversation-history" v-for="item in completions" @mouseover="showAnnotation" @mouseleave="hideAnnotation">
                <div v-if="item.type === 'choice'" :id="'ai-msg-'+item.id" class="conversation-message card border-info mb-3">
                    <p v-html="item.result"></p>
                    <ul class="ai-choice-list">
                        <li class="ai-choice" v-for="choice in item.choices" @click="selectedChoice(choice)">{{ choice.message }}</li>
                    </ul>
                </div>
                <div v-else-if="item.role === 'assistant'" :id="'ai-msg-'+item.id" class="conversation-message card border-primary mb-3" v-html="item.result"></div>
                <div v-else-if="item.role === 'user'" :id="'ai-msg-'+item.id" class="conversation-message card border-secondary mb-3" v-html="item.result"></div>
                <div v-else-if="item.role === 'system'" :id="'ai-msg-'+item.id" class="conversation-message card border-warning mb-3" v-html="item.result"></div>
            </div>
        </div>
        <div v-if="is_community_edition" class="ai-modal-body">
            <div class="conversation-history">
                <div class="conversation-message card border-warning mb-3">
                    <p v-html="this.labels.upgrade_message"></p>
                </div>
            </div>
        </div>
        <div class="spinner-holder">
            <ProgressSpinner v-if="show_spinner" style="width:25px;height:25px" strokeWidth="5" animationDuration="2s"/>
        </div>
        <div class="ai-modal-footer" v-if="!is_community_edition">
            <form @submit="userSubmitPrompt" class="form-container ai-form">
                <button type="reset" id="clear-button" class="clear-button" @click="clearContext">
                    <span class="ttassistant-icon">
                        <i class="tticon tticon-new_chat_black_24dp p-mr-4 p-text-secondary" v-tooltip.top="labels.new_chat"></i>
                    </span>
                </button>
                <div class="input-wrapper">
                    <InputText class="ai-input" v-model="user_prompt" ref="user_prompt_input" :disabled="input_disabled" autocomplete="false" toggleMask></InputText>
                    <div class="send-icon" @click="userSubmitPrompt">
                        <i class="tticon tticon-send_black_24dp p-mr-4 p-text-secondary" ></i>
                    </div>
                </div>
            </form>
        </div>
        <div class="ai-disclaimer" v-if="!is_community_edition">
            <small>{{ labels.ai_disclaimer }}</small>
        </div>
    </div>
</template>
<script>
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import ProgressSpinner from 'primevue/progressspinner';
import { Global } from '@/global/Global';
import TTVueUtils from '@/services/TTVueUtils';
import DOMPurify from 'dompurify';
import { marked } from 'marked';

export default {
    created() {
        this.event_bus = new TTEventBus( {
            component_id: this.component_id,
        } );
        this.event_bus.on( 'tt_assistant', 'show_tt_assistant', () => {
            this.showModal();
        } );
        this.event_bus.on( 'global', 'reset_vue_data', this.resetData );

        this.createStartingMessage();
    },
    mounted() {
        document.querySelector( '.layout-content' ).style.marginRight = '400px';
        this.dispatchResizeEvent();
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
    },
    data() {
        return {
            user_prompt: '',
            tt_assistant_api: TTAPI.APITTAssistant,
            completions: [],
            show_spinner: false,
            thread_id: null,
            run_id: null,
            input_disabled: false,
            is_resizing: false,
            last_x: 0,
            is_visible: true,
            starting_prompt_choices: (() => {
                const prompts = [
                    { message: $.i18n._( 'How do I fix a missing punch?' ) },
                    { message: $.i18n._( 'How do I give an employee a $100 bonus?' ) },
                    { message: $.i18n._( 'How do I create an overtime policy?' ) },
                    { message: $.i18n._( 'How do I process payroll?' ) },
                    { message: $.i18n._( 'Can I change the date and time format?' ) },
                ];

                if ( LocalCacheData && LocalCacheData.getLoginUser() ) {
                    const country = LocalCacheData.getLoginUser()?.country;
                    const province = LocalCacheData.getLoginUser()?.province;
                    if ( country === 'US' && province != '' ) {
                        prompts.push( { message: $.i18n._( 'Explain overtime laws in ' + province + ', United States.' ) } );
                        prompts.push( { message: $.i18n._( 'Explain US federal overtime laws.' ) } );
                    } else if ( country === 'CA' && province != '' ) {
                        prompts.push( { message: $.i18n._( 'Explain overtime laws in ' + province + ', Canada.' ) } );
                        prompts.push( { message: $.i18n._( 'Explain vacation pay laws in ' + province + ', Canada.' ) } );
                    }
                }

                return prompts;
            })(),
            labels: {
                title: $.i18n._( 'AI Assistant' ) + ' (' + $.i18n._( 'Beta' ) + ')',
                new_chat: $.i18n._( 'New Chat' ),
                ai_disclaimer: $.i18n._( 'Inaccurate information may be provided, double-check all responses.' ),
                upgrade_message: Global.getUpgradeMessage(),
            }
        };
    },
    computed: {
        is_community_edition() {
            return ( Global.getProductEdition() == 10 );
        }
    },
    methods: {
        resetData() {
            Object.assign( this.$data, this.$options.data() );
            this.hideModal();
        },
        startResizing( event ) {
            this.is_resizing = true;
            this.last_x = event.clientX;

            document.addEventListener( 'mousemove', this.onMouseMove );
            document.addEventListener( 'mouseup', this.onMouseUp );
        },
        onMouseMove( event ) {
            if ( !this.is_resizing ) {
                return;
            }

            const delta_x = event.clientX - this.last_x;
            const max_width = window.innerWidth * 0.5; // 50% of the window width
            const current_width = this.$refs.modal.getBoundingClientRect().width - delta_x;
            const new_width = Math.min( Math.max( 400, current_width ), max_width );

            this.$refs.modal.style.width = `${new_width}px`;
            this.last_x = event.clientX;
        },
        onMouseUp() {
            this.is_resizing = false;

            document.removeEventListener( 'mousemove', this.onMouseMove );
            document.removeEventListener( 'mouseup', this.onMouseUp );
        },
        scrollToBottom() {
            setTimeout( () => {
                const ai_body = this.$refs['ai-body'];
                if ( ai_body ) {
                    ai_body.scrollTop = ai_body.scrollHeight;
                }
            }, 300 );
        },
        clearContext() {
            this.cancelTTAssistant();
            this.thread_id = null;
            this.run_id = null;
            this.show_spinner = false;
            this.input_disabled = false;
            this.completions = [];
            this.user_prompt = '';

            this.createStartingMessage();
        },
        createStartingMessage() {
            let starting_choices = {
                role: 'system',
                type: 'choice',
                result: $.i18n._( `Hello ${LocalCacheData.getLoginUser().first_name},<br><br>I'm your AI assistant, ready to help with your questions. The more <a href="https://help.timetrex.com/latest/enterprise/Introduction/How-to-use-AI-Assistant.htm" target="_blank">detailed your questions</a>, the better assistance I can provide.<br><br>How can I help you today?` ),
                choices: this.starting_prompt_choices.sort( () => 0.5 - Math.random() ).slice( 0, 4 )
            };

            this.completions.push( starting_choices );
        },
        selectedChoice( choice ) {
            this.user_prompt = choice.message;
            this.userSubmitPrompt(null);
        },
        sendPrompt( user_prompt, user_input = {} ) {
            if ( user_prompt != '' ) {

                user_prompt = user_prompt.trim();
                this.addUserMessageToConversationHistory( user_prompt );
                this.getCompletion( user_prompt, user_input );
                this.user_prompt = '';
            }
        },
        getCompletion( user_prompt, user_input ) {
            user_input = this.addContext( user_prompt, user_input );
            this.input_disabled = true;
            this.show_spinner = true;
            this.tt_assistant_api.getCompletion( user_input, {
                onResult: ( res ) => {
                    this.show_spinner = false;
                    this.input_disabled = false;

                    let completion = res.getResult();

                    if ( typeof completion !== 'object' ) {
                        completion = {
                            role: 'assistant',
                            result: $.i18n._( 'Sorry, I encountered an error. Please try again later.' ),
                            message_id: this.tt_assistant_api.getMessageId()
                        };

                        this.tt_assistant_api.setMessageId( null ); //Reset message id, if we do not do this we can update the wrong message in UI conversation history when hitting server error.
                    }

                    completion['result'] = DOMPurify.sanitize( marked.parse( completion['result'] ) );
                    completion = this.parseAnnotations( completion );

                    this.thread_id = completion.thread_id;
                    this.run_id = completion.run_id;

                    // completion.type = this.categorizeCompletion( completion );
                    this.addCompletionToConversationHistory( completion );
                    this.scrollToBottom();
                    this.processAction( completion );
                }
            } );

            ProgressBar.removeProgressBar( this.tt_assistant_api.getMessageId() );
            this.addInProgressMessageToConversationHistory( this.tt_assistant_api.getMessageId() );
            this.scrollToBottom();
            setTimeout( () => {
                this.trackProgress( this.tt_assistant_api.getMessageId() );
            }, 2000 );
        },
        parseAnnotations( completion ) {
            if ( Array.isArray( completion.annotations ) && completion.annotations.length > 0 ) {
                let annotations = completion.annotations;
                let result = completion.result;

                for ( let i = 0; i < annotations.length; i++ ) {
                    let annotation = annotations[i];

                    if ( annotation.type === 'file_path' ) {
                        // From the OpenAI Documentation: File path annotations are created by the code_interpreter tool and contain references to the files generated by the tool.
                        // We do not use this type of annotation. Replace the annotation text with nothing.
                        result = result.replace( annotation.text, '' );
                    } else if ( annotation.type === 'file_citation' ) {
                        // From the OpenAI Documentation: File citations are created by the retrieval tool and define references to a specific quote in a specific file that was uploaded and used by the Assistant to generate the response.
                        let quote = annotation.file_citation.quote ?? null;

                        if ( !quote ) {
                            result = result.replace( annotation.text, '' );
                        } else {
                            let annotated_text = `<div class="annotated-text" data-anotation-id="${completion.id}"> [Source]<span class="annotation-content">${quote}</span></div>`;
                            result = result.replace( annotation.text, annotated_text );
                        }
                    }
                }

                completion.result = result;
            }

            //AI will sometimes reference the TimeTrex admin (the docx file assistant has) guide in ways like this: "These steps will guide you to where you can find the email within the TimeTrex software【0†timetrex_admin_guide_ai.docx】."
            //This is NOT marked as an annotation by OpenAI API, but we still want to replace it with a link to the documentation.
            let guide_link = '';

            if ( PermissionManager.HelpMenuValidateAdmin() ) {
                guide_link = 'https://www.timetrex.com/h?id=admin_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
            } else if ( PermissionManager.HelpMenuValidateSupervisor() ) {
                guide_link = 'https://www.timetrex.com/h?id=supervisor_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
            } else {
                guide_link = 'https://www.timetrex.com/h?id=employee_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
            }

            completion.result = completion.result.replace(/【.*?\.docx】/g, ' <a href="' + guide_link + '" target="_blank">' + $.i18n._( 'View TimeTrex Documentation' ) + '</a>' );

            return completion;
        },
        showAnnotation(event) {
            let annotated_element = event.target.closest('.annotated-text');

            if ( !annotated_element ) {
                return;
            }

            let annotation_content = annotated_element.querySelector('.annotation-content');

            if ( !annotation_content ) {
                return;
            }

            let overlay = this.$refs.annotation_overlay;
            if ( !overlay ) {
                return;
            }

            overlay.innerHTML = annotation_content.innerHTML;
            overlay.style.visibility = 'visible';
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';

            // Wait for the next DOM update cycle to ensure the overlay's dimensions are updated
            this.$nextTick(() => {
                let rect = annotated_element.getBoundingClientRect();
                let overlayWidth = overlay.offsetWidth;
                let overlayHeight = overlay.offsetHeight;

                let left_position = Math.max(rect.right - overlayWidth - 20, 0); // Ensure it doesn't go off-screen to the left
                let top_position = rect.top + window.scrollY - overlayHeight;

                overlay.style.top = `${top_position}px`;
                overlay.style.left = `${left_position}px`;
            });
        },
        hideAnnotation( event ) {
            let overlay = this.$refs.annotation_overlay;
            if ( !overlay ) {
                return;
            }

            // Determine if the mouse is moving to an element within the ai-modal
            let moving_within_ai_modal = event.relatedTarget && event.relatedTarget.closest( '.ai-modal' );

            // Only hide the overlay if we're not moving to another element within the ai-modal
            if ( !moving_within_ai_modal ) {
                overlay.style.visibility = 'hidden';
                overlay.style.opacity = '0';
                overlay.innerHTML = '';
            }
        },
        trackProgress( message_id ) {
            TTAPI.APIProgressBar.getProgressBar( message_id, {
                onResult: ( result ) => {
                    let res_data = result.getResult();
                    //Means error in progress bar
                    if ( res_data.hasOwnProperty( 'status_id' ) && res_data.status_id === 9999 ) {
                        return; //Progress updates have stopped
                    } else {
                        if ( res_data === true ||
                            ( Array.isArray( res_data ) && res_data.length === 0 ) || !res_data.total_iterations ||
                            typeof res_data.total_iterations !== 'number' ) {
                            return; //Progress updates have stopped
                        } else {
                            let in_progress_message = this.getCompletionByMessageId( message_id );
                            if ( in_progress_message ) {
                                if ( in_progress_message.result !== res_data.message && !in_progress_message.id ) {
                                    in_progress_message = Object.assign( in_progress_message, { result: res_data.message } );
                                }
                                setTimeout( () => {
                                    if ( this.show_spinner ) { //Only track progress if still waiting for response
                                        this.trackProgress( message_id );
                                    }
                                }, 2000 );
                            } else {
                                Debug.Text( 'No message found.', 'TTAssistant.vue', 'TTAssistant', 'trackProgress', 11 );
                                return; //Progress updates have stopped
                            }
                        }
                    }
                }
            } );
        },
        addInProgressMessageToConversationHistory( message_id ) {
            let in_progress_message = {
                role: 'assistant',
                result: $.i18n._( 'Typing...' ),
                id: null,
                message_id: message_id
            };

            this.completions.push( in_progress_message );
        },
        addCompletionToConversationHistory( completion ) {
            let in_progress_message = this.getCompletionByMessageId( completion.message_id );
            if ( in_progress_message ) {
                in_progress_message = Object.assign( in_progress_message, completion );
            }

            //If there is no in_progress_message then the user cleared the conversation history but this message came in right after.
            //Just ignore it, as user no longer wants to see it if they cleared the history. Otherwise, it can be odd to see a message appear after clearing the history.

            // else {
            //     this.completions.push( completion );
            // }
        },
        addUserMessageToConversationHistory( user_prompt ) {
            let user_message = {
                role: 'user',
                result: user_prompt
            };

            this.completions.push( user_message );
        },
        processAction( completion ) {
            if ( completion.action === 'edit_record' || completion.action === 'add_record' || completion.action === 'view_report' ) {
                LocalCacheData.setAutoFillData( completion.values );
            }

            if ( completion.redirect ) {
                Global.closeEditViews( () => {
                    if ( completion.action === 'view_report' ) {
                        IndexViewController.openReport( LocalCacheData.current_open_primary_controller, completion.section );
                    } else {
                        //Global.setURLToBrowser fails if already on the list view, therefore we just open the edit view directly.
                        if ( LocalCacheData.current_open_primary_controller.viewId === completion.section ) {
                            if ( completion.action === 'add_record' ) {
                                LocalCacheData.current_open_primary_controller.onAddClick();
                            } else {
                                LocalCacheData.current_open_primary_controller.onEditClick( completion.record_id );
                            }
                        } else {
                            Global.setURLToBrowser( completion.redirect );
                        }
                    }
                } );
            }
        },
        addContext( user_prompt, user_input ) {
            // let last_assistant_completion = this.getLastCompletionByRole( 'assistant' );

            user_input.prompt = user_prompt;

            //Without storing messages in database, we need to pass the context back (if required) to the API with each prompt.
            user_input.thread_id = this.thread_id;
            user_input.run_id = this.run_id;

            return user_input;
        },
        getCompletionById( id ) {
            return this.completions.find( ( completion ) => {
                return completion.id === id;
            } );
        },
        getCompletionByMessageId( message_id ) {
            return this.completions.find( ( completion ) => {
                return completion.message_id === message_id;
            } );
        },
        getLastCompletionByRole( role ) {
            let role_completions = this.completions.filter( ( completion ) => {
                return completion.role === role;
            } );

            return role_completions[role_completions.length - 1] || null;
        },
        getPromptSettings() {
            let prompt_settings = {};

            return prompt_settings;
        },
        userSubmitPrompt( e ) {
            e?.preventDefault();
            this.sendPrompt( this.user_prompt, this.getPromptSettings() );
        },
        cancelTTAssistant() {
            if ( Array.isArray( $.xhrPool ) ) {
                for ( let i = 0; i < $.xhrPool.length; i++ ) {
                    if ( $.xhrPool[i].url.includes( 'Class=APITTAssistant&Method=getCompletion' ) ) {
                        debugger;
                        $.xhrPool[i].jqXHR.abort();
                    }
                }
            }
        },
        dispatchResizeEvent() {
            setTimeout( () => {
                window.dispatchEvent( new Event( 'resize' ) );
            }, 250 );
        },
        hideModal() {
            this.is_visible = false;
            document.querySelector( '.layout-content' ).style.marginRight = null;
            this.dispatchResizeEvent();
        },
        showModal() {
            this.is_visible = true;
            document.querySelector( '.layout-content' ).style.marginRight = '400px';
            this.dispatchResizeEvent();
        },
    },
    components: {
        InputText,
        Checkbox,
        ProgressSpinner,
    }
};
</script>
<style scoped>
.ai-modal {
    background-color: #f8f8f8;
    box-shadow: 5px 5px 8px 0px rgba(0, 0, 0, 0.3), 0 0 60px 5px rgba(0, 0, 0, 0.38);
    position: fixed;
    top: 50px;
    right: 0;
    width: 400px;
    height: calc(100vh - 50px);
    z-index: 100;
    display: flex;
    flex-direction: column;
    user-select: text;
}

.ai-modal-resize-handle {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 10px;
    cursor: ew-resize;
    z-index: 10;
}

.ai-modal-body {
    padding: 1rem;
    flex-grow: 1;
    overflow-y: auto; /* Adds scrolling if the content is too long */
}

.spinner-holder {
    height: 25px;
}

.ai-modal-footer {
    padding: 1rem;
    height: auto;
}

.ai-input {
    margin-left: 0.5rem;
    flex-grow: 1;
    width: 100%;
    padding-right: 40px; /* Prevent text from being hidden under the send icon */
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.send-icon {
    margin-left: 0.5rem;
    cursor: pointer;
}

.ai-modal-close {
    font-size: 2rem;
    position: absolute;
    right: 1rem;
    cursor: pointer
}

.ai-modal-title {
    text-align: center;
    font-weight: 1000;
}

.conversation-history {
    margin: 1rem;
}

.conversation-message {
    padding: 1rem;
    border-radius: 3px;
}

.clear-button {
    background: #426d9d;
    border: 0px solid #a1a3a6;
    color: #ffffff;
    width: 3rem;
    font-size: 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 1000;
    padding: 3px;
}

.ttassistant-icon .tticon {
    color: #ffffff;
    font-size: 1.75rem !important;
}

.send-icon i {
    color: #426d9d;
}

.form-container.ai-form {
    display: flex;
    align-items: center;
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
    width: 25px;
    height: 25px;
}

::v-deep(.p-progress-spinner) {
    position: relative;
    left: 44%;
}

.ai-choice-list {
    list-style-type: none;
    padding: 0;
}

.ai-choice {
    padding: 0.5rem;
    margin-top: 0.5rem;
    cursor: pointer;
    background: #5382b7;
    border-radius: 3px;
    color: #fff;
}

.ai-choice:hover {
    background-color: #426d9d;
}

.ai-disclaimer {
    text-align: center;
    margin-bottom: 5px;
}

::v-deep(.annotated-text) {
    position: relative;
    display: inline-block;
    color: #007bff;
    cursor: pointer;
}

::v-deep(.annotation-content) {
    display: none;
}

.annotation-overlay {
    position: fixed;
    z-index: 1000;
    background-color: #fff;
    border: 1px solid #000;
    border-radius: 2px;
    padding: 7px;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    width: 500px;
    height: 300px;
    overflow-y: auto;
}

</style>