<template>
    <div class="flex flex-col gap-base-2">
        <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                <h3 class="text-base-lg font-extrabold">{{ $t('Admin') }}</h3>
            </div>
            <GroupMembersList :loading="!groupInfo.owner" :members-list="[groupInfo.owner]" />
        </div>
        <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                <h3 class="text-base-lg font-extrabold">{{ $t('Moderators') + ' - ' + shortenNumber(groupInfo.admin_count) }}</h3>
            </div>
            <GroupMembersList :loading="loadingModeratorList" :members-list="moderatorsList" :has-load-more="loadmoreModeratorsList" @load-more="getAdminsList(groupInfo.id, currentModeratorPage)">
                <template #empty>{{ $t('No moderators') }}</template>
            </GroupMembersList>
        </div>
        <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
            <h3 class="text-base-lg font-extrabold mb-base-2">{{ $t('Members') + ' - ' + shortenNumber(groupInfo.member_without_admin) }}</h3>
            <BaseInputText v-model="searchMemberText" @input="handleSearchMembers" :placeholder="$t('Search members')" class="max-w-xs mb-base-2"/>
            <GroupMembersList :loading="loadingMembersList" :members-list="membersList" :has-load-more="loadmoreMembersList" @load-more="handleGetGroupMember(groupInfo.id, searchMemberText, currentMemberPage)" />
        </div>
    </div>
</template>

<script>
import { mapActions} from 'pinia'
import { useAppStore } from '@/store/app'
import { getGroupAdmin, getGroupMembers } from '@/api/group'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import GroupMembersList from '@/components/lists/GroupMembersList.vue'

export default {
    props: ['groupInfo'],
    components: { BaseInputText, GroupMembersList },
    data(){
        return{
            currentModeratorPage: 1,
            loadingModeratorList: true,
            moderatorsList: [],
            loadmoreModeratorsList: false,
            searchMemberText: '',
            currentMemberPage: 1,
            loadingMembersList: true,
            membersList: [],
            loadmoreMembersList: false
        }
    },
    mounted(){
        if (!this.groupInfo.canView) {
            return this.setErrorLayout(true)
        }
        this.getAdminsList(this.groupInfo.id, this.currentModeratorPage)
        this.handleGetGroupMember(this.groupInfo.id, this.searchMemberText, this.currentMemberPage)
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getAdminsList(groupId, page){
            try {
                const response = await getGroupAdmin(groupId, page)
                if (page == 1) {
                    this.moderatorsList = [];
                }
                this.moderatorsList = window._.concat(this.moderatorsList, response.items)
                if(response.has_next_page){
                    this.loadmoreModeratorsList = true
                    this.currentModeratorPage++;
                }else{
                    this.loadmoreModeratorsList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingModeratorList = false
            }
        },
        async handleGetGroupMember(groupId, query, page){
            try {
                const response = await getGroupMembers(groupId, query, page)
                if (page == 1) {
                    this.membersList = [];
                }
                this.membersList = window._.concat(this.membersList, response.items)
                if(response.has_next_page){
                    this.loadmoreMembersList = true
                    this.currentMemberPage++;
                }else{
                    this.loadmoreMembersList = false
                }
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingMembersList = false
            }
        },
        debouncedGetGroupMembersList: window._.debounce(function() {
            this.handleGetGroupMember(this.groupInfo.id, this.searchMemberText, this.currentMemberPage);
        }, 500),
        handleSearchMembers(){
            this.currentMemberPage = 1
            this.debouncedGetGroupMembersList();
        },
    }
}
</script>