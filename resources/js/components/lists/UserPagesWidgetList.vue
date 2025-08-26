<template>
    <div v-if="pagesListData.length" :class="`user_pages_list_${positionWidget}`">
        <div v-if="listStyle === 'list'">
            <div v-for="page in pagesListData" :key="page.id" class="users-list-item flex items-center gap-base-2 mb-base-2 last:mb-0">
                <Avatar :user="page" :target="target" :activePopover="showPopover"/>
                <div class="flex-1 min-w-0">
                    <UserName :user="page" :activePopover="showPopover" :target="target" class="list_items_title_text_color" />
                    <div v-if="page.categories.length" class="text-xs mb-1 truncate text-sub-color dark:text-slate-400">
                        <span v-for="(category, index) in page.categories" :key="category.id">
                            <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                            {{ (index === page.categories.length - 1) ? '' : ' · '}}
                        </span>
                    </div>
                    <div v-if="page.show_follower" class="text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                </div>
                <div v-if="(user.id !== page.id) && page.can_follow">
                    <button v-if="page.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowPage(page)">{{$t('Unfollow')}}</button>
                    <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followPage(page)">{{$t('Follow')}}</button>
                </div>
            </div>
        </div>
        <VueperSlides v-else class="no-shadow relative" :slide-ratio="slideRatio" :visible-slides="screen.md ? 1 : 2" slide-multiple :gap="1" :autoplay="true" :duration="6000" :bullets="false" :touchable="false" :rtl="user.rtl ? true : false">
            <VueperSlide v-for="(page, index) in pagesListData" :key="page.id" >
                <template #content>
                    <div :ref="`slideContent${index}`">
                        <div class="grid-item rounded-lg border border-divider dark:border-white/10 h-full">
                            <router-link :to="{name: 'profile', params: {user_name: page.user_name}}" class="block pb-[35%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${page.cover})`}"></router-link>
                            <div class="p-base-2">
                                <div class="flex gap-4">
                                    <Avatar :user="page" :activePopover="false" :size="50"/>
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
                                <div v-else class="h-base-9 mt-base-2"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </VueperSlide>
            <template #arrow-left>
                <div class="flex items-center justify-center w-10 h-10 bg-white shadow-md rounded-full text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-600 z-10"><BaseIcon name="caret_left"/></div>
            </template>
            <template #arrow-right>
                <div class="flex items-center justify-center w-10 h-10 bg-white shadow-md rounded-full text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-600 z-10"><BaseIcon name="caret_right"/></div>
            </template>
        </VueperSlides>
    </div>
    <div v-else class="text-center">{{$t('No Pages')}}</div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { VueperSlides, VueperSlide } from 'vueperslides'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import { useAppStore } from '@/store/app'

export default {
    components: { Avatar, UserName, BaseButton, VueperSlides, VueperSlide, BaseIcon},
    props: {
        pagesList: {
            type: Array,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        showPopover: {
            type: Boolean,
            default: true
        },
        listStyle:{
            type: String,
            default: 'list'
        },
        positionWidget: {
            type: String,
            default: ''
        }
    },
    data(){
        return{
            pagesListData:  window._.clone(this.pagesList),
            slideRatio: 0
        }
    },
    mounted() {
        this.setSlideRatio();
        addEventListener("resize", () => {
            this.setSlideRatio();
        });
    },
    computed: {
        ...mapState(useAppStore, ['screen']),
        ...mapState(useAuthStore, ["user", "authenticated"]),
        ...mapState(useActionStore, ['userAction'])
    },
    watch: {
        userAction(){
            this.pagesList.map(page => {
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
        setSlideRatio() {
            if(this.listStyle !== 'list'){
                this.$nextTick(() => {
                    const ratios = this.pagesListData.map((page, index) => {
                        const content = this.$refs[`slideContent${index}`];
                        const contentHeight = content ? content[0]?.offsetHeight : 0;
                        return  contentHeight / (content[0]?.offsetWidth * ( this.screen.md ? 1 : 2));
                    });
                    this.slideRatio = Math.max(...ratios);
                });
            }
        }
    },
    emits: ['follow_page', 'unfollow_page']
} 
</script>