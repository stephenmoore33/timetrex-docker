<template>
    <div id="contentContainer" class="content-container">
        <div class="report-view edit-view audit-trail-report-view" style="opacity: 0">
            <TTContextMenu class="context-menu-bar hide-in-login" />
            <div class="edit-view-tab-bar" :id="viewId + '_view_tab_bar'">
                <div class="navigation-div" style="display: none">
                    <span class="navigation-label"></span>
                    <img class="left-click arrow"/>
                    <div class="navigation-widget-div"></div>
                    <img class="right-click arrow"/>
                </div>
                <span class="close-icon">x</span>
                <ul class="edit-view-tab-bar-label">
                    <li><a ref="tab_report" data-tab-ref="tab_report" href="#tab_report"></a></li>
                    <li><a ref="tab_setup" data-tab-ref="tab_setup" href="#tab_setup"></a></li>
                    <li><a ref="tab_chart" data-tab-ref="tab_chart" href="#tab_chart"></a></li>
                    <li><a ref="tab_custom_columns" data-tab-ref="tab_custom_columns" href="#tab_custom_columns"></a></li>
                    <li><a ref="tab_saved_reports" data-tab-ref="tab_saved_reports" href="#tab_saved_reports"></a></li>
                </ul>
                <div id="tab_report" class="edit-view-tab-outside">
                    <div class="edit-view-tab" id="tab_report_content_div">
                        <div class="first-column full-width-column"></div>
                    </div>
                </div>
                <div id="tab_setup" class="edit-view-tab-outside">
                    <div class="edit-view-tab" id="tab_setup_content_div">
                        <div class="first-column full-width-column"></div>
                    </div>
                </div>
                <div id="tab_chart" class="edit-view-tab-outside">
                    <div class="edit-view-tab" id="tab_chart_content_div">
                        <div class="first-column full-width-column"></div>
                        <div class="save-and-continue-div permission-defined-div">
                            <span class="message permission-message"></span>
                        </div>
                    </div>
                </div>
                <div id="tab_custom_columns" class="edit-view-tab-outside-sub-view">
                    <div class="edit-view-tab" id="tab_custom_columns_content_div">
                        <div class="first-column-sub-view"></div>
                        <div class="save-and-continue-div">
                            <span class="message"></span>
                            <div class="save-and-continue-button-div">
                                <div class="ribbon-button">
                                    <img class="icon">
                                    <span class="label"></span>
                                </div>
                            </div>
                        </div>
                        <div class="save-and-continue-div permission-defined-div">
                            <span class="message permission-message"></span>
                        </div>
                    </div>
                </div>
                <div id="tab_saved_reports" class="edit-view-tab-outside-sub-view">
                    <div class="edit-view-tab" id="tab_saved_reports_content_div">
                        <div class="first-column-sub-view"></div>
                        <div class="save-and-continue-div">
                            <span class="message"></span>
                            <div class="save-and-continue-button-div">
                                <div class="ribbon-button">
                                    <img class="icon">
                                    <span class="label"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TTContextMenu from '@/components/context_menu/TTContextMenu';
import { Global } from '@/global/Global';
// import('');// TimesheetSummaryReport

export default {
    name: "ReportView",
    props: {
        viewId: String
    },
    methods: {
    },
    components: {
        TTContextMenu
    }
};
</script>

<style scoped>
.context-menu-bar {
    background-color: #cacaca; /* To match the edit-view tab background color */
    margin: 5px auto 0; /* To match the edit-view tab */
    padding-top: 5px;
    width: 98%; /* To match the edit-view tab */
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    min-width: 900px; /* To match the edit-view tab */
    max-width: 1380px; /* To match the edit-view tab */
}
.edit-view-tab-bar {
    margin-top:0;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
</style>