<template>
    <div v-if="isMobile" class="flex main-content-section bg-white rounded-none md:rounded-lg dark:bg-slate-800">
		<div v-if="!currentTab" class="flex-1 py-base-2 lg:border-r border-divider dark:border-white/10">
			<div class="settings-list flex flex-col">
				<router-link
                    v-for="item in visibleListsMenu"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
                    :to="{ name: item.name }"
                >
                    {{ item.label }}
                    <BaseIcon name="caret_right" size="16" />
                </router-link>
			</div>
		</div>
		<div v-if="currentTab" class="flex-2 min-w-0 p-5 lg:p-10">
			<router-view></router-view>
		</div>
	</div>
    <div v-else class="flex main-content-section bg-white border border-white text-main-color dark:bg-slate-800 dark:border-slate-800 dark:text-white rounded-base-lg mb-base-2">
		<div class="flex-1 py-base-7 md:border-e border-divider dark:border-white/10 rounded-s-base-lg">
			<div class="settings-list flex flex-col">
				<router-link
                    v-for="item in visibleListsMenu"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
                    :to="{ name: item.name }"
                    :class="{'router-link-exact-active': isActive(item.name)}"
                >
                    {{ item.label }}
                </router-link>
			</div>
		</div>
		<div class="flex-2 min-w-0 p-5 lg:p-10">
			<router-view></router-view>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components:{
        BaseIcon
    },
    data(){
        return{
            currentTab: this.$route.path.split("/")[2],
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config', 'isMobile']),
        listMenu(){
            return [
                { label: this.$t('Lists'), name: this.isMobile ? 'list_index' : 'list_lists' },
                { label: this.$t('Tags'), name: 'list_tag' },
                { label: this.$t('Stories'), name: 'list_stories' },
                { label: this.$t('Pages'), name: 'list_page', isShow: this.config.user_page.enable && !this.user.is_page },
                { label: this.$t('Vibbs'), name: 'list_vibbs', isShow: this.config.vibb.enable },
                { label: this.$t('Groups'), name: 'list_groups', isShow: this.config.group.enable },
                { label: this.$t('Unblocked Posts'), name: 'list_unblocked_posts', isShow: this.config.paid_content.enable },
            ]
        },
        visibleListsMenu() {
            return this.listMenu.filter(item => item.isShow || typeof(item.isShow) == 'undefined');
        },
        listTitle(){
			const titles = {
                follower: this.$t('Followers'),
                following: this.$t('Following'),
                block_member: this.$t('Blocked'),
                tag: this.$t('Tags'),
                stories: this.$t('Stories'),
                pages: this.$t('Pages'),
                vibbs: this.$t('Vibbs'),
				groups: this.$t('Groups')
            };
            return titles[this.currentTab] || this.$t('Lists');
		}
    },
    methods:{
        isActive(routeName){
            return ['list_following', 'list_follower', 'list_block_member'].includes(this.$router.currentRoute.value.name) && routeName === 'list_lists'
        }
    }
}
</script>