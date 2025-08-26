<template>
    <UserInfoPopover :userProps="user" :activePopover="activePopover" :style="{width: `${size}px`, height: `${size}px`}" class="flex-shrink-0">
       <div class="relative w-full h-full">
            <router-link v-if="router" :to="{name: 'profile', params: {user_name: user.user_name, tab: tab}}" :target="target">
                <img :src="user.avatar" class="rounded-full w-full" />
                <div v-if="border" class="absolute inset-0 rounded-full border border-media-inner dark:border-media-inner-dark"></div>
            </router-link>
            <div v-else>
                <img :src="user.avatar" class="rounded-full w-full" />
                <div v-if="border" class="absolute inset-0 rounded-full border border-media-inner dark:border-media-inner-dark"></div>
            </div>
       </div>
   </UserInfoPopover>
</template>
<script>
import { defineAsyncComponent } from 'vue';
const UserInfoPopover = defineAsyncComponent(() => import('@/components/user/UserInfoPopover.vue'));

export default {
    components: { UserInfoPopover },
    props: {
        user: {
            type: Object,
            default: null
        },
        size: {
            type: Number,
            default: 40
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        border: {
            type: Boolean,
            default: true
        },
        router: {
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