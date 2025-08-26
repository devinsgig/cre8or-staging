<template>
	<template v-if="config != null && user != null && locale_loaded">
		<AuthenticatedLayout v-if="authenticated" />
		<NonAuthenticatedLayout v-else />
	</template>
	<LoadingPage v-else></LoadingPage>
	<ConfirmDialog class="confirm-dialog-social" :draggable="false"/>
	<DynamicDialog />
	<Toast :position="user?.rtl ? 'top-left' : 'top-right'">
		<template #message="toast">
			<div class="p-toast-message-icon">
				<BaseIcon v-if="toast.message.severity === 'success'" name="success" class="text-base-green" />
				<BaseIcon v-else-if="toast.message.severity === 'error'" name="error" />
			</div>
			<div class="p-toast-message-text">
				<span class="p-toast-summary">{{ toast.message.summary }}</span>
				<div class="p-toast-detail" v-html="toast.message.detail" />
			</div>
		</template>
	</Toast>
	<CookiesWarning />
	<AppSuggest />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { markSeenNotification } from '@/api/notification';
import { checkAdvancedUpload } from '@/utility/index'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useLangStore } from '@/store/lang'
import { useChatStore } from '@/store/chat'
import { useUtilitiesStore } from '@/store/utilities'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import LoadingPage from '@/pages/LoadingPage.vue';
import Constant from '@/utility/constant'
import localData from '@/utility/localData';
import Echo from 'laravel-echo';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import DynamicDialog from 'primevue/dynamicdialog';
import CookiesWarning from '@/components/layout/CookiesWarning.vue';
import AppSuggest from '@/components/layout/AppSuggest.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import NonAuthenticatedLayout from '@/layouts/NonAuthenticatedLayout.vue';
var dragTimer = null

export default {
	components: {
		BaseIcon,
		LoadingPage,
		Toast,
		ConfirmDialog,
		DynamicDialog,
		CookiesWarning,
		AppSuggest,
		AuthenticatedLayout,
		NonAuthenticatedLayout
	},
	data() {
		return {
			first: true
		};
	},
	mounted() {
        this.initializeApp();
    },
	beforeUnmount() {
		this.cleanupEventListeners();
	},
	watch: {
		'$route': 'checkPermission',
		async user(user) {
            await this.handleUserChange(user);
        },
		darkmode: 'applyDarkMode',
        systemMode: 'applyDarkMode',
		locale_loaded: 'handleLocaleLoaded'
	},
	computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode']),
		...mapState(useLangStore, ['locale_loaded'])
	},
	methods: {
		...mapActions(useUtilitiesStore, ['pingNotification', 'setChatCount', 'setEventDragDrop']),
		...mapActions(useChatStore, ['setChatMessageSentEvent', 'setChatRoomSeenSelfEvent', 'setRoomSeenEvent', 'setRoomUnreadEvent', 'setRoomAcceptEvent', 'setChatMessageUnsentEvent']),
		...mapActions(useAppStore, ['detectMobile', 'setDarkmode', 'updateSystemMode', 'updateScreenSize']),
		...mapActions(useAuthStore, ['me']),
		checkPermission(){
			if (this.user && this.$router.currentRoute.value.meta.permission) {
				let permission = this.$router.currentRoute.value.meta.permission;
				if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
					this.$router.push({ 'name': 'permission' })
				}
			}
		},
		initializeApp() {
            this.detectMobile();
            this.updateScreenSize();
			this.updateSystemMode();
            if (checkAdvancedUpload) {
                this.setupDragAndDrop();
            }
            document.addEventListener('scroll', this.handleScroll);
            document.addEventListener('visibilitychange', this.handleVisibilityChange);
            window.addEventListener('resize', this.updateScreenSize);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateSystemMode);
        },
		cleanupEventListeners() {
            if (this.authenticated) {
                clearInterval(this.interval);
            }
			document.removeEventListener('scroll', this.handleScroll);
            document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            window.removeEventListener('resize', this.updateScreenSize);
            window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateSystemMode);
        },
		handleScroll() {
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            if (viewportWidth > 769) {
                const scrollTop = window.pageYOffset;
                document.body.classList.toggle('documentScrolling', scrollTop > 24);
            }
        },
        handleVisibilityChange() {
            if (!document.hidden) {
                this.me();
            }
        },
        setupDragAndDrop() {
			var self = this
            const dragEvents = ['dragover', 'dragenter', 'dragleave', 'drop'];
            dragEvents.forEach((event) => {
                window.addEventListener(event, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragover', 'drop'].forEach(function (event) {
				window.addEventListener(event, function (e) {
					self.setEventDragDrop(e);
					window.clearTimeout(dragTimer);
				});
			});

			window.addEventListener('dragleave', function (e) {		
				dragTimer = window.setTimeout(function() {
					self.setEventDragDrop(e)
				}, 25);
			});
        },
		async handleUserChange(user) {
            if (user.id > 0 && this.authenticated) {
                this.handleAuthenticatedUser(user);
            } else if (user.id === 0 && this.authenticated) {
                await useAuthStore().logout();
                window.location.href = window.siteConfig.siteUrl;
            }

            document.body.dir = user.rtl ? 'rtl' : 'ltr';
            this.checkPermission();
        },
		handleAuthenticatedUser(user) {
            if (this.config.emailVerify && user.email_verified === 0) {
                if (this.$router.currentRoute.value.name !== 'email_confirm') {
                    this.$router.push({ name: 'email_confirm' });
                }
            } else if (this.config.phoneVerify && !user.phone_verified){
				if (this.$router.currentRoute.value.name != 'phone_confirm') {
					this.$router.push({ 'name': 'phone_confirm' })
				}	
			} else if (! user.already_setup_login) {
                this.$router.push({ name: 'first_login' });
            }

            this.setDarkmode(user.darkmode);

            if (this.first) {
                this.setupBroadcast(user);
                this.startNotificationPing();
                this.markNotificationAsRead();
                this.first = false;
            }
        },
		setupBroadcast(user) {
            if (this.config.broadcastEnable) {
				window.Echo = new Echo({
					broadcaster: 'pusher',
					key: this.config.broadcastKey,
					cluster: this.config.broadcastCluster,
					wsHost: this.config.broadcastHost,
					wsPort: this.config.broadcastPort,
					wssPort: this.config.broadcastPort,
					forceTLS: this.config.broadcastForceTLS,
					enabledTransports: ['ws', 'wss'],
					authEndpoint: window.siteConfig.siteUrl + '/broadcasting/auth'
				});
				window.Echo.private(Constant.CHANNEL_USER + user.id).
					listen('.Chat.MessageSentEvent', (data) => {
						this.setChatMessageSentEvent(data)
						this.setChatCount(data.chat_count)
					}).listen('.Chat.RoomSeenSelfEvent', (data) => {
						this.setChatCount(data.chat_count)
						this.setChatRoomSeenSelfEvent(data)
					}).listen('.Chat.RoomSeenEvent', (data) => {
						this.setRoomSeenEvent(data)
					}).listen('.Chat.RoomUnreadEvent', (data) => {
						this.setChatCount(data.chat_count)
						this.setRoomUnreadEvent(data)
					}).listen('.Chat.RoomAcceptEvent', (data) => {
						this.setRoomAcceptEvent(data)
					}).listen('.Chat.MessageUnsentEvent', (data) => {
						this.setChatMessageUnsentEvent(data)
					}
				);
			}
        },
		startNotificationPing() {
            this.pingNotification();
            this.interval = setInterval(this.pingNotification, this.config.notificationInterval);
        },
        markNotificationAsRead() {
            const urlParams = new URLSearchParams(window.location.search);
            const notifyId = urlParams.get('notify_id');
            if (notifyId) {
                markSeenNotification({ id: notifyId });
            }
        },
		applyDarkMode() {
            const isDarkMode = this.darkmode === 'on' || (this.darkmode === 'auto' && this.systemMode === 'dark');
            document.documentElement.classList.toggle('dark', isDarkMode);
        },
		handleLocaleLoaded() {
            if (this.locale_loaded && localData.get('inactive')) {
                localData.remove('inactive');
                this.showError(this.$t('Your account is pending approval.'));
            }
        }
	}
};
</script>
