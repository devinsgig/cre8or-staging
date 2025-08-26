<template>
    <div class="post-status-box bg-white border border-white rounded-none md:rounded-base-lg p-4 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
		<div class="flex items-center gap-base-2">
			<Avatar :user="user" :activePopover="false" :router="false"/>
			<div @click="openStatusBox()" class="flex-1 post-status-box-placeholder text-sub-color dark:text-slate-400 pe-base-2 py-base-2" role="button">{{ $t('What do you want to talk about?') }}</div>
		</div>
		<div class="status-box-action flex gap-base-2 justify-between items-center border-t border-divider mt-base-2 pt-base-2 dark:border-white/10">
			<button @click="openStatusBox('photo')" class="status-box-action-button flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
				<BaseIcon name="photo" class="status-box-action-button-icon" />
				<span class="status-box-action-button-text">{{ $t('Photo') }}</span>
			</button>
			<template v-if="config.ffmegEnable">
				<button @click="openStatusBox('video')" class="status-box-action-button flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
					<BaseIcon name="youtube_logo" class="status-box-action-button-icon" />
					<span class="status-box-action-button-text">{{ $t('Video') }}</span>
				</button>
				<button v-if="config.vibb.enable" @click="handleCreateVibb()" class="status-box-action-button flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
					<BaseIcon name="vibb" class="status-box-action-button-icon" />
					<span class="status-box-action-button-text">{{ $t('Vibb') }}</span>
				</button>
			</template>
		</div>
	</div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import Avatar from '@/components/user/Avatar.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: {
        postFrom: {
            type: String,
            default: ''
        },
        subject: {
            type: Object,
            default: null
        }
    },
    components: { Avatar, BaseIcon },
    computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config'])
	},
    methods: {
        openStatusBox(type){
			const { canPostStatus } = this.subject || {};
			if(canPostStatus){
				switch (canPostStatus) {
					case 'login':
						return this.showRequireLogin()
					case 'member':
						return this.showNotificationModal(this.$t('Only members of group can post in this group.'))
					case 'admin':
						return this.showNotificationModal(this.$t('Only admins and moderators of group can post in this group.'))
					case 'ok':
						return this.showPostStatusBox(type, this.postFrom, this.subject)
				}
			}
			return this.showPostStatusBox(type, this.postFrom, this.subject)
		},
		handleCreateVibb() {
            if(!this.authenticated){
                this.$router.push({ 'name': 'login' })
                return
            }
            if (this.user) {
				let permission = 'vibb.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'vibb_create', force: true })
                }
			}
        }
    }
}
</script>