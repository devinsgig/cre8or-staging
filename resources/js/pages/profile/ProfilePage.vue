<template>
	<div v-if="userInfo">
		<template v-if="userInfo.check_privacy">
			<TabsMenu :menus="profileMenus" @select="changeTab" />
			<Component :is="profileComponent" :userInfo="userInfo" @change_tab="changeTab" @update_user_info="updateUserInfo"></Component>
		</template>
		<template v-else>
			<div class="main-content-section bg-white text-center p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
				<template v-if="isPrivacyFollower(userInfo.privacy)">
					<div class="text-base-lg font-bold mb-1">{{ userInfo.is_page ? $t('This Page is Private') : $t('This Account is Private') }}</div>
					<div class="text-base-sm">{{ $t('Follow to see their posts.') }}</div>
				</template>
				<template v-if="isPrivacyOnlyme(userInfo.privacy)">
					<div class="text-base-lg font-bold">{{ userInfo.is_page ? $t('This Page is Private') : $t('This Account is Private') }}</div>
				</template>
			</div>
		</template>	
	</div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { changeUrl } from '@/utility'
import Constant from '@/utility/constant'
import UserFeeds from '@/pages/profile/UserFeeds.vue'
import UserInfo from '@/pages/profile/UserInfo.vue'
import UserPageInfo from '@/pages/profile/UserPageInfo.vue'
import UserMedia from '@/pages/profile/UserMedia.vue'
import UserPageReview from '@/pages/profile/UserPageReview.vue'
import UserVibbs from '@/pages/profile/UserVibbs.vue'
import UserPaidPost from '@/pages/profile/UserPaidPost.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import { useAppStore } from '@/store/app'
import { useProfileStore } from '@/store/profile'

export default {
	components: {
		TabsMenu
	},
	props: ['data', 'params', 'position'],
	data() {
		return {
			currentTab: this.params.tab ? this.params.tab : ''
		}
	},
	mounted(){
		this.getUserInfo(this.params.user_name)
	},
	unmounted() {
		this.setUserInfo()
	},
	computed: {
		...mapState(useProfileStore, ['userInfo']),
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config']),
		isOwnerPage(){
            return this.userInfo.id === this.user.id
        },
		profileComponent() {
			switch(this.currentTab){
				case 'info':
					return UserInfo;
				case 'page_info':
					return UserPageInfo;
				case 'media':
					return UserMedia;
				case 'vibbs':
					return UserVibbs;
				case 'review':
					return UserPageReview
				case 'paid_post':
					return UserPaidPost
				default: 
					return UserFeeds;
			}
		},
		profileMenus(){
			return [
				{ icon: 'feeds', name: this.$t('Feeds'), isActive: this.currentTab === '', tab: ''},
				{ icon: 'coin', name: this.$t('Paid Media'), isShow: this.userInfo.show_paid_post, isActive: this.currentTab === 'paid_post', tab: 'paid_post'},
				{ icon: 'info', name: this.$t('Info'), isShow: this.userInfo.is_page, isActive: this.currentTab === 'page_info', tab: 'page_info'},
				{ icon: 'info', name: this.$t('Info'), isShow: !this.userInfo.is_page, isActive: this.currentTab === 'info', tab: 'info'},
				{ icon: 'media', name: this.$t('Media'), isActive: this.currentTab === 'media', tab: 'media'},
				{ icon: 'vibb', name: this.$t('Vibbs'), isShow: this.config.vibb.enable, isActive: this.currentTab === 'vibbs', tab: 'vibbs'},
				{ icon: 'star', name: this.$t('Reviews'), isShow: this.userInfo.is_page && (this.isOwnerPage || this.userInfo.review_enable), isActive: this.currentTab === 'review', tab: 'review'},
			]
		}
	},
	methods: {
		...mapActions(useActionStore, ['updateFollowStatus']),
		...mapActions(useAppStore, ['setErrorLayout']),
		...mapActions(useProfileStore, ['getUserInfo', 'setUserInfo']),
		changeTab(name) {
			this.currentTab = name
			let userUrl = this.$router.resolve({
				name: 'profile',
				params: { 'user_name': this.userInfo.user_name }
			});
			changeUrl(userUrl.fullPath + (name != '' ? '/' + name : ''))
		},
		updateUserInfo(value){
			this.userInfo[Object.keys(value)] = value[Object.keys(value)]
		},
		isPrivacyFollower(privacy){
			return privacy == Constant.USER_PRIVACY_FOLLOWER
		},
		isPrivacyOnlyme(privacy){
			return privacy == Constant.USER_PRIVACY_ONLYME
		}
	}
}
</script>