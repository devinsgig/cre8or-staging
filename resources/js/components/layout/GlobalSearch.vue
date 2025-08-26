<template>
    <div class="global-search-header relative w-full max-w-md">
        <form autocomplete="off" @submit.prevent="searchText()">
            <BaseInputText class="global-search-header-input" :placeholder="$t('Search')" :autofocus="autofocus" left_icon="search" v-model="searchModal" @input="onEnterSearch()" @focus="onEnterSearch()" autocomplete="off"/>
        </form>
        <div v-if="isShownSearchBox" class="dropdown-menu-box max-h-96 overflow-y-auto absolute left-0 right-0 top-10 z-[9000] bg-white shadow-md scrollbar-thin rounded-base-lg dark:bg-slate-800 dark:shadow-slate-600" v-click-outside="closeSearchBox">     					                   
            <div class="global-search-suggestion-box p-5" @click="closeSearchBox()">
                <div v-if="searchContent">
                    <router-link :to="{name: 'search', params: {search_type: 'text', type: 'post'}, query:{q: searchContent}}" class="flex items-center mb-4 text-sm last:mb-0 text-inherit">				
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="search"/></div>
                        <div class="global-search-suggestion-box-text flex-1 mx-base-2">
                            <div v-html="matchingSearch(searchResultsList['text'])"></div>
                        </div>
                    </router-link>							
                    <router-link v-for="(searchHashtagResultItem, index) in searchResultsList.hashtags" :key="index" :to="{name: 'search', params: {search_type: 'hashtag', type: type}, query: {q: searchHashtagResultItem.name}}" class="flex items-center mb-4 text-sm last:mb-0 text-inherit">
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="search"/></div>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <div v-html="hashtagChar + matchingSearch(searchHashtagResultItem.name)"></div>
                            <div class="global-search-suggestion-box-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(searchHashtagResultItem.post_count, $t('[number] Post'), $t('[number] Posts')) }}</div>
                        </div>
                    </router-link>
                    <router-link v-for="searchUserResultItem in searchResultsList.users" :key="searchUserResultItem.id" :to="{name: 'profile', params: {user_name: searchUserResultItem.user_name}}" class="flex items-center mb-4 last:mb-0 text-inherit">
                        <Avatar :user="searchUserResultItem" :size="48" :activePopover="false"/>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <UserName :user="searchUserResultItem" :activePopover="false"/>
                            <p class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ mentionChar + searchUserResultItem.user_name }}</p>
                        </div>
                    </router-link>
                    <router-link v-for="searchPageResultItem in searchResultsList.pages" :key="searchPageResultItem.id" :to="{name: 'profile', params: {user_name: searchPageResultItem.user_name}}" class="flex items-center mb-4 last:mb-0 text-inherit">
                        <Avatar :user="searchPageResultItem" :size="48" :activePopover="false"/>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <UserName :user="searchPageResultItem" :activePopover="false"/>
                            <p v-if="searchPageResultItem.show_follower" class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(searchPageResultItem.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p>
                        </div>
                    </router-link>
                    <router-link v-for="groupItem in searchResultsList.groups" :key="groupItem.id" :to="{name: 'group_profile', params: {id: groupItem.id, slug: groupItem.slug}}" class="flex items-center mb-4 last:mb-0 text-inherit">
                        <div class="w-12 h-12 rounded-full mx-auto max-w-full border border-divider dark:border-slate-700">
                            <img :src="groupItem.cover" :alt="groupItem.name" class="rounded-full w-full h-full object-cover">
                        </div>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0 truncate">
                            <GroupName :group="groupItem" :activePopover="false" />
                            <p class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(groupItem.member_count, $t('[number] member'), $t('[number] members')) }}</p>
                        </div>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { getSearchSuggest } from '@/api/search'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import GroupName from '@/components/group/GroupName.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
var typingTimer = null

export default {
    components:{ BaseIcon, Avatar, UserName, GroupName, BaseInputText },
    props: {
        autofocus: {
            type: Boolean,
			default: false
        }
    },
    data(){
        return{
            type: null,
            isShownSearchBox: false,
			searchContent: null,
			searchResultsList: {},
            mentionChar: Constant.MENTION,
			hashtagChar: Constant.HASHTAG,
            searchModal: this.$route.query.q ? this.$route.query.q : null
        }
    },
    mounted(){
        this.type = !window._.isNil(this.$route.params.type) ? this.$route.params.type : 'post'
    },
    watch: {
        '$route'() {
            if(this.$route.name != 'search'){
                this.searchModal = null
            }else{
                this.searchModal = this.$route.query.q ? this.$route.query.q : null
            }
        },
        searchModal() {
            this.searchContent = this.searchModal ? this.searchModal.replace('#','') : ''
        }
    },
    methods:{
        openSearchBox(){
			if (!this.isShownSearchBox) this.isShownSearchBox = true
		},
		closeSearchBox(){
			if (this.isShownSearchBox) this.isShownSearchBox = false
		},
		onEnterSearch(){
			if(this.searchContent){
				clearTimeout(typingTimer);
				typingTimer = setTimeout(() => 
					this.getSearchSuggestsList(this.searchContent)
				, 400);
			}else{
				this.closeSearchBox()
			}
		},
        searchText(){
            if(this.searchContent){
                this.$router.push({name: 'search', params: {search_type: 'text', type: 'post'}, query:{q: this.searchContent}})
                this.closeSearchBox()
            }
        },
		async getSearchSuggestsList(keyword){
            if(keyword){
                try {
                    this.searchResultsList = {
                        text: keyword,
                        hashtags: null,
                        users: null,
                        pages: null,
                        groups: null
                    };
                    const response = await getSearchSuggest(keyword)
                    this.openSearchBox()
                    if (response.hashtags.length || response.users.length || response.pages.length || response.groups.length) {
                        this.searchResultsList.hashtags = response.hashtags;
                        this.searchResultsList.users = response.users;
                        this.searchResultsList.pages = response.pages;
                        this.searchResultsList.groups = response.groups;
                    }
                } catch (error) {
                    console.log(error)
                }
            }
		},
		matchingSearch(keyword){
			let words = this.searchContent
			keyword = window._.replace(window._.lowerCase(keyword), window._.lowerCase(words), '<span class="font-bold">'+words+'</span>')
			return keyword
		}
    }
}
</script>