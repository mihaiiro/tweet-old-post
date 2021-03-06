<template>
    <div>
        <div class="columns panel-header">
            <div class="column header-logo vertical-align" v-if="is_preloading_over > 0">
                <div>
                    <img :src="plugin_logo" class="plugin-logo avatar avatar-lg"/>
                    <h1 class="plugin-title d-inline-block">Revive Old Posts</h1><span class="powered d-inline-block"> {{labels.by}} <a
                        href="https://revive.social" target="_blank"><b>Revive.Social</b></a></span>
                </div>
            </div>
            <toast/>
            <div v-if=" is_rest_api_error " class="toast toast-error rop-api-not-available" v-html="labels.api_not_available">
            </div>
            <div v-if=" is_fb_domain_notice " class="toast toast-primary">
                <button class="btn btn-clear float-right" @click="close_fb_domain_notice()"></button>
                <div v-html="labels.rop_facebook_domain_toast"></div>
            </div>
            <div class="sidebar sidebar-top card rop-container-start">

                <!-- Next post count down -->
                <countdown :current_time="current_time"/>
                <!--  -->

                <button class="btn btn-sm" :class="btn_class"
                        :data-tooltip="labels.active_account_warning"
                        @click="togglePosting()" :disabled="!haveAccountsActive">
                    <i class="fa fa-play" v-if="!is_loading && !start_status"></i>
                    <i class="fa fa-stop" v-else-if="!is_loading && start_status"></i>
                    <i class="fa fa-spinner fa-spin" v-else></i>
                    {{( start_status ? labels.stop : labels.start )}} {{labels.sharing}}
                </button>
            </div>
        </div>

        <div class="columns">
            <div class="panel column col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="panel-nav" style="padding: 8px;" v-if="is_preloading_over > 0">
                    <ul class="tab ">
                        <li class="tab-item c-hand" v-for="tab in displayTabs"
                            :class="{ active: tab.isActive }" v-bind:id="tab.name.replace(' ', '').toLowerCase()">
                            <a :class=" ( tab.slug === 'logs' && logs_no > 0  )  ? ' badge-logs badge' : '' "
                               :data-badge="logs_no"
                               @click="switchTab( tab.slug )">{{ tab.name }}</a>
                        </li>
                    </ul>
                </div>
                <component :is="page.template" :type="page.view"></component>
            </div>

            <div class="sidebar column col-3 col-xs-12 col-sm-12  col-md-12 col-lg-12"
                 :class="'rop-license-plan-'+license" v-if="is_preloading_over > 0">

                <div class="card rop-container-start">
                    <button id="rop_start_stop_btn" class="btn" :class="btn_class"
                            :data-tooltip="labels.active_account_warning"
                            @click="togglePosting()" :disabled="!haveAccountsActive">
                        <i class="fa fa-play" v-if="!is_loading && !start_status"></i>
                        <i class="fa fa-stop" v-else-if="!is_loading && start_status"></i>
                        <i class="fa fa-spinner fa-spin" v-else></i>
                        {{labels.click}} {{labels.to}} {{( start_status ? labels.stop : labels.start )}} {{labels.sharing}}
                    </button>

                    <div class="sharing-box" :class="status_color_class">{{ status_label_display }}</div>

                    <countdown :current_time="current_time"/>

                    <div id="staging-status" v-if="staging">
                        {{labels.staging_status}}
                    </div>
                    <div v-if="!haveAccounts" class="rop-spacer"></div>
                    <div v-if="haveAccounts">
                        <upsell-sidebar></upsell-sidebar>
                    </div>
                    <a v-if="license  >= 1" href="https://revive.social/pro-support/" target="_blank" class="btn rop-sidebar-action-btns">{{labels.rop_support}}</a>
                    <a v-if="license  < 1" href="https://revive.social/support/" target="_blank" class="btn rop-sidebar-action-btns">{{labels.rop_support}}</a>
                    <a v-if="haveAccounts" href="https://docs.revive.social/" target="_blank"
                       class="btn rop-sidebar-action-btns">{{labels.rop_docs}}</a>
                    <a v-if="haveAccounts"
                       href="https://wordpress.org/support/plugin/tweet-old-post/reviews/?rate=5#new-post"
                       target="_blank" class="btn rop-sidebar-action-btns">{{labels.review_it}}</a>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    /* global ROP_ASSETS_URL */
    import AccountsTab from './accounts-tab-panel.vue'
    import SettingsTab from './settings-tab-panel.vue'
    import AccountsSelectorTab from './accounts-selector-panel.vue'
    import QueueTab from './queue-tab-panel.vue'
    import LogsTab from './logs-tab-panel.vue'
    import Toast from './reusables/toast.vue'
    import CountDown from './reusables/countdown.vue'
    import moment from 'moment'
    import upsellSidebar from './upsell-sidebar.vue'

    module.exports = {
        name: 'main-page-panel',
        computed: {
            is_preloading_over: function(){
                return this.$store.state.hide_preloading;
            },
            /**
             * Display the clicked tab.
             *
             * @returns {module.exports.computed.displayTabs|*[]}
             */
            displayTabs: function () {
                return this.$store.state.displayTabs
            },
            /**
             * Get active page.
             */
            page: function () {
                return this.$store.state.page
            },
            /**
             * Check if rest api is available.
             */
            is_rest_api_error: function () {
                return this.$store.state.api_not_available
            },
            /**
             * Check if rest api is available.
             */
            is_fb_domain_notice: function () {
                return this.$store.state.fb_exception_toast
            },
            current_time: {
                get: function () {
                    return this.$store.state.cron_status.current_time;
                },
                set: function (value) {
                    this.$store.state.cron_status.current_time = value;
                }
            },
            date_format: function () {

                return this.$store.state.cron_status.date_format;
            },
            logs_no: function () {

                return this.$store.state.cron_status.logs_number;
            },
            /**
             * Get btn start class.
             */
            btn_class: function () {
                let btn_class = ('btn-' + (this.start_status ? 'danger' : 'success'));
                if (!this.haveAccountsActive) {
                    btn_class += ' tooltip button-disabled ';
                }
                return btn_class;
            },
            /**
             * Status label.
             */
            status_color_class: function () {
                let status_color_class = ('sharing-status-' + (this.start_status ? 'sharing' : 'notsharing'));
                if (!this.haveAccountsActive) {
                    status_color_class = ' sharing-status-notsharing  ';
                }
                if (this.status_is_error_display) {
                    return ' sharing-status-error ';
                }
                return status_color_class;
            },
            status_label_display: function () {
                let labels = this.$store.state.labels.general;
                let status_label_display = (this.start_status ? labels.sharing_to_account : labels.sharing_not_started);
                if (!this.haveAccountsActive) {
                    status_label_display = labels.sharing_not_started;
                }

                if (this.status_is_error_display) {
                    return labels.status + ': ' + labels.error_check_log;
                }
                return labels.status + ': ' + status_label_display;
            },
            status_is_error_display: function () {
                return this.status_is_error_display;
            },
            /**
             * Check if we have accounts connected.
             *
             * @returns {boolean}
             */
            haveAccounts: function () {
                return (Object.keys(this.$store.state.authenticatedServices).length > 0);
            },
            /**
             * Check if we have accounts active.
             *
             * @returns {boolean}
             */
            haveAccountsActive: function () {
                return (Object.keys(this.$store.state.activeAccounts).length > 0);
            },
            /*
            * Check if the sharing is started.
            */
            start_status: function () {
                return this.$store.state.cron_status.current_status
            },

            /**
             * Get general settings.
             * @returns {module.exports.computed.generalSettings|Array|*}
             */
            generalSettings: function () {
                return this.$store.state.generalSettings
            },
        },
        mounted: function () {
            setInterval(() => {
                //this.$log.info('counting');
                if (this.current_time > 0) {
                    this.current_time += 1;
                }
            }, 1000);

            this.get_toast_message(false);
        },
        created() {

        },
        data: function () {
            return {
                to_pro_upsell: ROP_ASSETS_URL + 'img/to_pro.png',
                to_business_upsell: ROP_ASSETS_URL + 'img/to_business.png',
                plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png',
                license: this.$store.state.licence,
                labels: this.$store.state.labels.general,
                upsell_link: ropApiSettings.upsell_link,
                staging: ropApiSettings.staging,
                is_loading: false,
                is_loading_logs: false,
                status_is_error_display: false
            }
        },
        methods: {
            /**
             *
             * */
            close_fb_domain_notice() {
                if (this.is_loading) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }

                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'fb_exception_toast',
                    data: {action: 'hide'}
                }).then(response => {
                    this.$log.info('Succesfully closed facebook domain toast.');
                    this.is_loading = false;
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            },
            /**
             * Toggle sharing.
             */
            togglePosting() {
                if (this.is_loading) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }
                this.is_loading = true;
                let new_status = this.start_status === false ? 'start' : 'stop';
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'manage_cron',
                    data: {
                        'action': new_status
                    }
                }).then(response => {
                    this.is_loading = false;
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            },
            /**
             * Toggle tab active.
             * @param slug
             */
            switchTab(slug) {
                this.$store.commit('setTabView', slug)
            },

            get_toast_message(force) {
                if (this.is_loading_logs) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }
                this.is_loading_logs = true;
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'get_toast',
                    data: {force: force}
                }).then(response => {
                    this.$log.info('Succesfully fetched toast log.');
                    this.is_loading_logs = false;
                    this.$store.dispatch('fetchAJAX', {req: 'manage_cron', data: {action: 'status'}});

                    // Toast message code start
                    if (response.length) {
                        for (let index_error in response) {
                            if ('error' === response[index_error].type) {
                                let toast = {
                                    type: response[index_error].type,
                                    show: true,
                                    title: 'Error encountered',
                                    message: response[index_error].message
                                };
                                this.$store.commit('updateState', {stateData: toast, requestName: 'update_toast'});
                            } else if ('status_error' === response[index_error].type) {
                                this.$log.warn('Status is error check logs, global admin notice will be displayed');
                                this.status_is_error_display = true;
                            }
                        }
                    }
                    // Toast message code end

                }, error => {
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                    this.is_loading_logs = false;
                })
            }
        },
        components: {
            'accounts': AccountsTab,
            'settings': SettingsTab,
            'accounts-selector': AccountsSelectorTab,
            'queue': QueueTab,
            'logs': LogsTab,
            'upsell-sidebar': upsellSidebar,
            'toast': Toast,
            'countdown': CountDown
        }
    }
</script>

<style>
    #rop_core .badge[data-badge]::after {
        position: absolute;
        bottom: -16px;
        right: 0px;
    }

    #rop_core .rop-api-not-available {
        margin: 10px 0px 10px 0px;
    }

    #rop_core .badge.badge-logs::after {
        right: auto;
        top: 0px;
    }

    #rop_core .badge.badge-logs {
        padding-right: 10px;
    }
</style>
