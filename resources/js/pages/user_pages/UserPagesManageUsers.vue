<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-base-lg font-extrabold">{{ $t('Admin') }}</h3>
            <BaseButton v-if="user.is_owner_page" @click="handleClickTransferAdmin()">{{$t('Transfer Page')}}</BaseButton>
        </div>
        <div v-if="user.page_owner" class="users-list-item flex items-center gap-base-2 mb-base-2">
            <Avatar :user="user.page_owner"/>
            <div class="flex-1 min-w-0">
                <UserName :user="user.page_owner" class="list_items_title_text_color" />
                <p v-if="user.page_owner.show_follower" class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(user.page_owner.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
            </div>
        </div>
    </div>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-base-lg font-extrabold">{{ $t('Moderators') }}</h3>
            <BaseButton v-if="user.is_owner_page" @click="handleClickAddModerator()">{{$t('Add moderator')}}</BaseButton>
        </div>
        <div v-for="admin in adminsList" :key="admin.id" class="users-list-item flex items-center gap-base-2 mb-base-2 last:mb-0">
            <Avatar :user="admin"/>
            <div class="flex-1 min-w-0">
                <UserName :user="admin" class="list_items_title_text_color" />
                <p v-if="admin.show_follower" class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(admin.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
            </div>
            <button v-if="user.is_owner_page || user.parent.id === admin.id" @click="handleRemoveModerator(admin.id)" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color">{{$t('Remove')}}</button>
        </div>
        <div v-if="loadmoreAdminsList" class="text-center">
            <BaseButton @click="getAdminsList(currentPage)">{{$t('View more')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getAdminPage, removeAdminPage } from '@/api/page'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AddModeratorsModal from '@/components/modals/AddModeratorsModal.vue'
import TransferAdminModal from '@/components/modals/TransferAdminModal.vue'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'

export default {
    components: { Avatar, UserName, BaseButton },
    data(){
        return{
            currentPage: 1,
            loadmoreAdminsList: false,
            adminsList: []
        }
    },
    mounted(){
        if (! this.user.is_page) {
            this.setErrorLayout(true)
        } else {
            this.getAdminsList(this.currentPage)
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user"])
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getAdminsList(page){
            try {
                const response = await getAdminPage(page)
                if (page == 1) {
                    this.adminsList = [];
                }
                this.adminsList = window._.concat(this.adminsList, response.items)
                if(response.has_next_page){
                    this.loadmoreAdminsList = true
                    this.currentPage++;
                }else{
                    this.loadmoreAdminsList = false
                }
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleClickTransferAdmin(){
            this.$dialog.open(TransferAdminModal, {
                props: {
                    header: this.$t('Transfer Admin Account'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        },
        handleClickAddModerator(){
            this.$dialog.open(AddModeratorsModal, {
                props: {
                    header: this.$t('Add Moderator'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    if (options.data) {
                        this.adminsList.push(options.data.adminInfo)
                    }
                }
            })
        },
        async handleRemoveModerator(userId){
            this.$confirm.require({
                message: this.$t('Do you want to remove this user?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await removeAdminPage(userId)
                        this.showSuccess(this.$t('Remove Successfully'))
                        if(this.user.is_owner_page){
                            this.adminsList = this.adminsList.filter(admin => admin.id !== userId)
                        } else {
                            window.location.href = window.siteConfig.siteUrl
                        }
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        }
    }
}
</script>