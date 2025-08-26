<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Pages') }}</h3>
            <BaseButton v-if="! user.is_page" @click="createPage">{{ $t('Create new page') }}</BaseButton>
        </div>
        <TabsMenu :menus="pageMenus" type="secondary" @select="changeTab" class="mb-4"/>
        <Component :is="pageComponent" :categoryId="$route.query.category_id"/>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { changeUrl } from '@/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import UserPagesAll from '@/pages/user_pages/UserPagesAll.vue'
import UserPagesTrending from '@/pages/user_pages/UserPagesTrending.vue'
import UserPagesSuggest from '@/pages/user_pages/UserPagesSuggest.vue'
import UserPagesFollowing from '@/pages/user_pages/UserPagesFollowing.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue';

export default {
    props: ['data', 'params', 'position'],
    components: { BaseButton, TabsMenu },
    data(){
        return{
            currentTab: this.params.tab ? this.params.tab : ''
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        pageMenus(){
            return [
                { name: this.$t('All'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Trending'), tab: 'trending', isActive: this.currentTab === 'trending' },
                { name: this.$t('For you'), tab: 'suggest', isActive: this.currentTab === 'suggest' },
                { name: this.$t('Following'), tab: 'following', isActive: this.currentTab === 'following' }
            ]
        },
        pageComponent() {
			switch(this.currentTab){
				case 'trending':
					return UserPagesTrending;
				case 'suggest':
					return UserPagesSuggest
				case 'following':
					return UserPagesFollowing
				default: 
					return UserPagesAll;
			}
		}
    },
    methods:{
        changeTab(name) {
			this.currentTab = name
			let pageUrl = this.$router.resolve({
				name: 'user_pages'
			});
			changeUrl(pageUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        createPage() {
            if (this.user) {
				let permission = 'user_page.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'user_pages_create' })
                }
			}
        }
    }
}
</script>