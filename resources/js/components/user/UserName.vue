<template>
    <div v-if="truncate" class="flex items-center gap-1 max-w-full font-semibold">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline-block min-w-0 max-w-full">
            <router-link v-if="router" :to="{name: 'profile', params: { user_name: user.user_name, tab: tab }}" :target="target" class="base-username flex text-inherit">
                <span class="truncate">{{user.name}} </span>
            </router-link>
            <div v-else class="base-username flex text-inherit">
                <span class="truncate">{{user.name}} </span>
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="w-4 h-4" :src="config.userVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Account'), showDelay: 2500} : null"/>
            <img v-if="user.is_page" class="w-4 h-4" :src="config.userPageVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Page'), showDelay: 2500} : null"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="w-4 h-4" :src="config.user_page.featureBadgeIcon" v-tooltip="!isMobile ? {value: $t('Featured Page'), showDelay: 2500} : null"/>
        <span v-if="user.badge" class="inline-block text-xs font-normal leading-none align-middle max-w-[200px] truncate px-base-1 py-1 border rounded-1000" 
            :style="{'background-color': user.badge.background_color, 'border-color': user.badge.border_color, 'color': user.badge.text_color}">
            {{ user.badge.name }}
        </span>
    </div>
    <div v-else class="inline break-word font-semibold">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline">
            <router-link v-if="router" :to="{name: 'profile', params: { user_name: user.user_name, tab:tab }}" :target="target" class="base-username inline text-inherit">
                {{user.name}}
            </router-link>
            <div v-else class="base-username inline text-inherit">
                {{user.name}}
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="inline w-4 h-4 ms-1" :src="config.userVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Account'), showDelay: 2500} : null"/>
            <img v-if="user.is_page" class="inline w-4 h-4 ms-1" :src="config.userPageVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Page'), showDelay: 2500} : null"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="inline w-4 h-4 ms-1" :src="config.user_page.featureBadgeIcon" v-tooltip="!isMobile ? {value: $t('Featured Page'), showDelay: 2500} : null"/>
        <span v-if="user.badge" class="inline-block text-xs font-normal leading-none align-middle max-w-[200px] truncate px-base-1 py-1 border rounded-1000 ms-1 mb-[2px]" 
            :style="{'background-color': user.badge.background_color, 'border-color': user.badge.border_color, 'color': user.badge.text_color}">
            {{ user.badge.name }}
        </span>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import { defineAsyncComponent } from 'vue';
const UserInfoPopover = defineAsyncComponent(() => import('@/components/user/UserInfoPopover.vue'));

export default {
    components: { UserInfoPopover },
    computed:{
		...mapState(useAppStore, ['config', 'isMobile'])
	},
    props: {
        user: {
            type: Object,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        router: {
            type: Boolean,
            default: true
        },
        truncate: {
            type: Boolean,
            default: true
        },
        tab: {
            type: String,
            default: ''
        }
    }
}
</script>