<template>
    <div class="main-content-section bg-white border border-white text-main-color p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white mb-base-2">
        <div class="main-content-menu flex justify-center border-b border-divider dark:border-white/10">
            <div class="main-content-menu-item flex items-center px-base-2 -mb-[1px] py-1 cursor-pointer border-b-2" :class="(currentTab === ''?'active border-primary-color text-primary-color dark:border-dark-primary-color dark:text-dark-primary-color font-semibold':'border-transparent')" @click="changeTab('')">
                {{$t('Invite')}}
            </div>
            <div class="main-content-menu-item flex items-center px-base-2 -mb-[1px] py-1 cursor-pointer border-b-2" :class="(currentTab === 'your_referrals'?' active border-primary-color text-primary-color dark:border-dark-primary-color dark:text-dark-primary-color font-semibold':'border-transparent')" @click="changeTab('your_referrals')">
                {{$t('Your referrals')}}
            </div>
        </div>
        <Component :is="inviteComponent"/>
    </div>
</template>
<script>
import { changeUrl } from '@/utility';
import Invite from './Invite.vue'
import YourReferrals from './YourReferrals.vue'

export default {
    props: ['tab'],
    data() {
        return {
            currentTab: this.tab ? this.tab : ''
        }
    },
    computed: {
        inviteComponent() {
            switch (this.currentTab) {
                case "invite":
                    return Invite;
                case "your_referrals":
                    return YourReferrals;
                default:
                    return Invite;
            }
        }
    },
    methods: {
        changeTab(name) {
            this.currentTab = name
            let userUrl = this.$router.resolve({
                name: 'invites',
                params: { 'tab': this.currentTab }
            });
            changeUrl(userUrl.fullPath)
        }
    }
}
</script>