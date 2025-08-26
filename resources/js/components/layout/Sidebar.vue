<template>
    <div class="sidebar-user-menu inline-flex flex-col px-5 py-base-7 bg-sidebar text-main-color max-w-[320px] sm:max-w-[350px] w-full overflow-auto scrollbar-thin lg:sticky top-0 start-0 bottom-0 lg:h-screen lg:self-start dark:bg-slate-800 dark:text-white" :class="{'show': isOpenSidebar}">
        <BaseIcon name="arrow_left" class="absolute top-8 end-5 rtl:rotate-180 block sm:hidden" @click="setIsOpenSidebar(false)" />
        <Logo class="mb-6 w-max" :className="'max-w-[140px]'" />
        <div class="sidebar-user-menu-box bg-light-gray border border-light-web-wash p-base-2 rounded-lg dark:bg-dark-web-wash dark:border-white/10">
            <div class="flex items-center gap-3">
                <Avatar :user="user" :activePopover="false" :size="56"/>
                <div class="flex-1 min-w-0">
                    <UserName :user="user" :activePopover="false" class="sidebar-user-menu-name" />
                    <router-link :to="{name: 'profile', params: {user_name: user.user_name}}" class="sidebar-user-menu-sub-text block text-xs text-sub-color dark:text-slate-400 truncate">{{mentionChar + user.user_name}}</router-link>  
                    <div v-if="config.wallet.enable" class="mt-1 text-xs"><router-link class="inline-flex flex-wrap gap-x-base-2 gap-y-1" :to="{name: 'wallet'}"><span class="sidebar-user-menu-sub-text text-sub-color dark:text-slate-400">{{ exchangeTokenCurrency(user.wallet_balance) }}</span><span class="sidebar-user-menu-link">{{ $t('Add fund') }}</span></router-link></div>
                    <a v-if="user.is_moderator" :href="user.admin_link" target="_blank" class="sidebar-user-menu-link text-xs">{{$t('Go to admin panel')}}</a>
                </div>
                <div>
                    <PageSwitchItem v-if="user.is_page" :page="user.parent" @click="handleClickLoginBack()" class="sidebar-user-menu-icon pulser switch-page-icon"/>
                    <button v-else @click="handleSwitchPage()">
                        <BaseIcon name="user_switch" size="32" class="sidebar-user-menu-icon"/>
                    </button>
                </div>
            </div>
            <div v-if="config.membership.enable && !user.is_moderator" class="mt-2">
                <div class="font-semibold mb-1">{{ user.membership_package_name ? $t('Current Package') + ': ' + user.membership_package_name : $t('Current Package: Free') }}</div>
                <BaseButton :to="{name: 'membership'}" size="sm">{{ $t('Upgrade Membership') }}</BaseButton>
            </div>
        </div>
        <div class="sidebar-main-menu flex-1 pt-3 pb-5">
            <SidebarMenu />
            <div class="mt-5 hidden lg:block">
                <BaseButton class="w-full" @click="openStatusBox()" id="addNewPostBtn">{{$t('Add New Post')}}</BaseButton>
            </div>
        </div>
        <FooterSite class="border-t border-divider"/>
    </div>
</template>

<script>
import { mapState, mapActions} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { loginBack } from '@/api/page'
import Constant from '@/utility/constant'
import FooterSite from '@/components/layout/FooterSite.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import Logo from '@/components/utilities/Logo.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import SwitchPagesModal from '@/components/modals/SwitchPagesModal.vue'
import PageSwitchItem from '@/components/user_page/PageSwitchItem.vue';
import SidebarMenu from '@/components/sidebar/index.vue'

export default {
    components: {
        BaseButton,
        FooterSite,
        UserName,
        Logo,
        BaseIcon,
        Avatar,
        PageSwitchItem,
        SidebarMenu
    },
    data(){
        return{
            mentionChar: Constant.MENTION
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config', 'isOpenSidebar', 'menus'])
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        ...mapActions(useAppStore, ['setIsOpenSidebar']),
        openStatusBox(){
			this.showPostStatusBox()
		},
        handleSwitchPage(){
            this.$dialog.open(SwitchPagesModal, {
                props:{
					header: this.$t('Switch Page'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            });
        },
        handleClickLoginBack(){
            try {
                this.setSelectedPage(this.user.parent)
                setTimeout(async() => {
                    await loginBack()
                    window.location.href = this.user.parent.href
                }, 1500);
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>