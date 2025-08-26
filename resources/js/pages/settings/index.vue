<template>
	<div v-if="isMobile" class="flex main-content-section bg-white rounded-none md:rounded-lg dark:bg-slate-800">
		<div v-if="!currentTab" class="flex-1 py-base-2 lg:border-r border-divider dark:border-white/10">
			<div class="settings-list flex flex-col">
				<router-link
                    v-for="item in visibleSettingsList"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
                    :to="{ name: item.name }"
                >
                    {{ item.label }}
                    <BaseIcon name="caret_right" size="16" />
                </router-link>
				<div v-if="user.can_delete" class="px-base-2 mt-6">
					<BaseButton type="danger-outlined" class="w-full" @click="deleteUser">{{$t('Delete Account')}}</BaseButton>
				</div>
			</div>
		</div>
		<div v-if="currentTab" class="flex-2 min-w-0 ps-5 py-5 lg:p-10" :class="currentTab === 'profile' ? 'pe-10' : 'pe-5'">
			<div class="flex items-center gap-3 mb-5">
				<router-link :to="{ name: 'setting_index'}" class="text-inherit">
					<BaseIcon name="arrow_left" class="align-middle" />
				</router-link>
				<h2 class="text-2xl capitalize font-bold font-workSans">{{ settingTitle }}</h2>
			</div>
			<router-view></router-view>
		</div>
	</div>
	<div v-else class="flex main-content-section bg-white border border-white text-main-color dark:bg-slate-800 dark:border-slate-800 dark:text-white rounded-base-lg mb-base-2">
		<div class="flex-1 py-base-7 md:border-e border-divider dark:border-white/10 rounded-s-base-lg">
			<div class="settings-list flex flex-col">
				<router-link
                    v-for="item in visibleSettingsList"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
					:class="{'router-link-exact-active': isActive(item.name)}"
                    :to="{ name: item.name }"
                >
                    {{ item.label }}
                </router-link>
				<div v-if="user.can_delete" class="px-base-2 mt-6">
					<BaseButton type="danger-outlined" class="btn-block" @click="deleteUser">{{ user.is_page ? $t('Delete Page') : $t('Delete Account')}}</BaseButton>
				</div>
			</div>
		</div>
		<div class="flex-2 min-w-0 p-5 lg:p-10">
			<router-view></router-view>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia';
import { deleteUserAccount } from '@/api/user'
import { deletePageAccount } from '@/api/page'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';


export default {
    components: { BaseIcon, BaseButton },
	props: ["tab"],
	data(){
		return{
			currentTab: this.$route.path.split("/")[2],
			error: {
				password: null
			}
		}
	},
	computed: {
		...mapState(useAppStore, ['isMobile']),
		...mapState(useAuthStore, ['user']),
		settingsList() {
            const baseSettings = [
                {
                    name: this.isMobile ? 'setting_profile' : 'setting_index',
                    label: this.user.is_page ? this.$t('General Settings') : this.$t('Profile'),
                },
                { name: 'setting_subscriptions', label: this.$t('Subscriptions') },
				{ name: 'setting_account', label: this.$t('Account'), isShow: !this.user.is_page && this.user.has_email },
				{ name: 'setting_creator_dashboard', label: this.$t('Creator Dashboard'), isShow: this.user.can_show_creator_dashboard },
				{ name: 'setting_password', label: this.$t('Change password'), isShow: !this.user.is_page && this.user.has_email },
                { name: 'setting_add_email_password', label: this.$t('Add email and password'), isShow: !this.user.is_page && !this.user.has_email},
				{ name: 'setting_privacy', label: this.$t('Privacy') },
				{ name: 'setting_email', label: this.$t('Email Notifications'), isShow: !this.user.is_page},
                { name: 'setting_notifications', label: this.$t('Push Notifications') },
                { name: 'setting_display', label: this.$t('Display') },
                { name: 'setting_download', label: this.$t('Download your Data') },
            ];
            return baseSettings;
        },
		visibleSettingsList() {
            return this.settingsList.filter(setting => setting.isShow || typeof(setting.isShow) == 'undefined');
        },
		settingTitle(){
			const titles = {
                profile: this.user.is_page ? this.$t('General Settings') : this.$t('Profile'),
                account: this.$t('Account'),
                password: this.$t('Change password'),
                privacy: this.$t('Privacy'),
                email: this.$t('Email'),
                notifications: this.$t('Notifications'),
                display: this.$t('Display'),
                download: this.$t('Download'),
                add_email_password: this.$t('Add email and password'),
                subscriptions: this.$t('Subscriptions'),
				creator_dashboard: this.$t('Creator Dashboard')
            };
            return titles[this.currentTab] || this.$t('Profile');
		}
	},
	methods:{
		async deleteUser(){
			const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
				emits: {
                    onConfirm: async (data) => {
                        if (data.password) {
                            try {
								if (this.user.is_page) {
									await deletePageAccount({
										password: data.password
									})
								} else {
									await deleteUserAccount({
										password: data.password
									})
									useAuthStore().remove()
								}							
								window.location.href = window.siteConfig.siteUrl;
								passwordDialog.close()
							} catch (error) {
								this.handleApiErrors(this.error, error)
								passwordDialog.close()
							}
                        }
                    }
                }
			})
		},
		isActive(routeName){
            return ['setting_creator_dashboard', 'setting_subscriber_detail'].includes(this.$router.currentRoute.value.name) && routeName === 'setting_creator_dashboard' || 
			['setting_subscription_detail'].includes(this.$router.currentRoute.value.name) && routeName === 'setting_subscriptions'
        }
	}
}
</script>