<template>
    <div class="footer-mobile flex items-center justify-between lg:hidden fixed inset-x-0 bottom-0 bg-white px-5 py-4 z-[998] dark:bg-slate-800">
        <div class="footer-mobile-item">
            <router-link :to="{ name: 'home' }" class="text-main-color dark:text-white">
                <BaseIcon name="home" />               
            </router-link> 
        </div>
         <div class="footer-mobile-item">
            <router-link :to="{ name: 'discovery' }" class="text-main-color dark:text-white">
                <BaseIcon name="compass" />               
            </router-link>
        </div>
        <div class="footer-mobile-item">
            <button @click="openStatusBox()">
                <BaseIcon name="new_post_mobile" size="32" class="create-post-btn-mobile text-base-red"/>
            </button>
        </div>
        <div class="footer-mobile-item">
            <button @click="clickChat" class="text-main-color dark:text-white relative" :class="{'router-link-exact-active': $route.name === 'chat'}">
                <BaseIcon name="message" />
                <span v-if="pingChatCount > 0" class="footer-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
            </button> 
        </div>
        <div class="footer-mobile-item">
            <router-link :to="{ name: 'notifications' }" class="text-main-color dark:text-white relative">
                <BaseIcon name="notification" />
                <span v-if="pingNotificationCount > 0" class="footer-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white">{{pingNotificationCount > 9 ? '9+' : pingNotificationCount}}</span>
            </router-link>  
        </div> 
    </div>
</template>
<script>
import { mapState } from 'pinia';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { useUtilitiesStore } from '../../store/utilities';

export default {
    name: "FooterMobile",
    components: { BaseIcon },
    computed:{
		...mapState(useUtilitiesStore, ['pingNotificationCount', 'pingChatCount']),
	},
    methods:{
        openStatusBox(){
            this.showPostStatusBox()
		},
        clickChat() {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'chat', force: true})
			}
		}
    }
}
</script>