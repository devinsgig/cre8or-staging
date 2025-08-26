<template>
    <SkeletonPagesList v-if="loading" />
    <template v-else>
        <template v-if="pagesListData.length">
            <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">     
                <div v-for="page in pagesListData" :key="page.id" class="grid-item rounded-lg border border-divider dark:border-white/10 h-full">
                    <router-link :to="{name: 'profile', params: {user_name: page.user_name}}" class="block pb-[35%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${page.cover})`}"></router-link>
                    <div class="p-base-2">
                        <div class="flex gap-4">
                            <Avatar :user="page" :size="50"/>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap gap-2 items-center mb-1"> 
                                    <UserName :user="page" class="grid-item-title" />
                                </div>
                                <div v-if="page.categories.length" class="grid-item-sub text-xs mb-1 truncate text-sub-color dark:text-slate-400">
                                    <span v-for="(category, index) in page.categories" :key="category.id">
                                        <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                                        {{ (index === page.categories.length - 1) ? '' : ' · '}}
                                    </span>
                                </div>
                                <div v-if="page.show_follower" class="grid-item-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                            </div>
                        </div>
                        <div v-if="page.can_follow" class="mt-base-2">
                            <BaseButton v-if="page.is_followed" @click="unFollowPage(page)" type="outlined" class="w-full">{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followPage(page)" type="outlined" class="w-full">{{$t('Follow')}}</BaseButton>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="autoLoadMore">
                <InfiniteLoading v-if="hasLoadMore" @infinite="handleLoadInfinite">				
                    <template #spinner>
                        <SkeletonPagesList />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <template v-else>
                <div v-if="hasLoadMore" class="text-center mt-4">
                    <BaseButton @click="handleLoadmore">{{ $t('View more') }}</BaseButton>
                </div>
            </template>
        </template>
        <div v-else class="text-center">
            <slot name="empty">
                <div>{{ $t('No Pages') }}</div>
            </slot>
        </div>
    </template>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import SkeletonPagesList from '@/components/skeletons/SkeletonPagesList.vue'
import InfiniteLoading from 'v3-infinite-loading'

export default {
    components: { Avatar, UserName, BaseButton, SkeletonPagesList, InfiniteLoading },
    props: {
        pagesList: {
            type: Array,
            default: null
        },
        loading: {
            type: Boolean,
            default: true
        },
        hasLoadMore: {
            type: Boolean,
            default: true
        },
        autoLoadMore: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return{
            pagesListData:  window._.clone(this.pagesList)
        }
    },
    computed: {
        ...mapState(useAuthStore, ["authenticated"]),
        ...mapState(useActionStore, ['userAction'])
    },
    watch: {
        userAction(){
            this.pagesListData.map(page => {
                if(page.id === this.userAction.pageId){
                    if(this.userAction.status === 'follow'){
                        page.is_followed = true
                    }else if(this.userAction.status === 'unfollow'){
                        page.is_followed = false
                    }
                }
                return page
            })
        },
        pagesList(){
            this.pagesListData = window._.clone(this.pagesList)
        }
    },
    methods: {
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followPage(page) {
            if(this.authenticated){
                try {
                    await toggleFollowUser({
                        id: page.id,
                        action: "follow"
                    });
                    this.pagesListData.map(pageItem => {
                        if (pageItem.id === page.id) {
                            pageItem.is_followed = true;
                        }
                        return pageItem;
                    });
                    this.updateFollowStatus({pageId: page.id, status: 'follow'})
                    this.$emit('follow_page', page)
                }
                catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unFollowPage(page) {
            try {
                await toggleFollowUser({
                    id: page.id,
                    action: "unfollow"
                });
                this.pagesListData.map(pageItem => {
                    if (pageItem.id === page.id) {
                        pageItem.is_followed = false;
                    }
                    return pageItem;
                });
                this.updateFollowStatus({pageId: page.id, status: 'unfollow'})
                this.$emit('unfollow_page', page)
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        handleLoadmore(){
            if(this.hasLoadMore){
                this.$emit('load-more')
            }
        },
        handleLoadInfinite(state){
            if(this.hasLoadMore && this.autoLoadMore){
                this.$emit('load-more', state)
            }
        }
    },
    emits: ['follow_page', 'unfollow_page', 'load-more']
} 
</script>