<template>
    <template v-if="item">
        <div class="vibb-wrapper min-h-full py-0 md:py-2" :id="[ `vibbItem-${item.id}`]" :style="{ width: screen.md ? '100%' : vibbItemWidth}">
            <div ref="vibbItemRef" class="h-full relative rounded-md mx-auto" :style="{ backgroundColor: vibbItem.subject.thumb.params.dominant_color }">
                <ContentWarningWrapper :content-warning-list="item.content_warning_categories" :post="item" class="h-full md:rounded-md" :button-offset-y="screen.md ? 54 : 0">
                    <VideoPlayerShort ref="vibbVideoRef" :video="vibbItem.subject" :autoPlay="true" :preload="true" :allow-pip="false" :is-content-warning="isContentWarning" :action-offset-y="screen.md ? 64 : 12" @in-viewport="handleSetCurrentVibb" class="w-full h-full" />
                    <div class="bg-footer-linear ps-3 py-3 pe-20 lg:pe-3 absolute start-0 bottom-0 end-0 text-white z-20 max-h-1/2 overflow-y-auto scrollbar-hidden rounded-b-none md:rounded-b-md">
                        <div class="flex gap-base-2 items-center">
                            <Avatar :user="item.user" :border="false" :activePopover="false" tab="vibbs" />
                            <UserName :user="item.user" :activePopover="false" tab="vibbs" class="min-w-0" />
                        </div>
                        <ContentHtml :content="item.content" :mentions="item.mentions" :limit="100" class="mt-base-1" />
                        <span v-if="songItem" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 mt-base-1">
                            <BaseIcon name="music_note" size="16" />
                            {{ songItem.subject.name }}
                        </span>
                    </div>
                    <div class="vibb-main-action absolute flex flex-col gap-base-2 md:gap-5 z-20" :class="screen.lg ? 'bottom-4 end-4' : 'bottom-0 -end-16'">
                        <div class="vibb-main-action-item flex flex-col items-center gap-2">
                            <button 
                                ref="likeButton"
                                :class="['vibb-main-action-like', item.is_liked ? 'text-base-red is-liked' : '', buttonActionStyle]"
                                @click.stop="item.is_liked ? unLikePost(item.id) : likePost(item.id)"
                            >
                                <BaseIcon :name="item.is_liked ? 'heart_fill' : 'heart'" />
                            </button>
                            <button @click.stop="openLikersModal('posts', item.id)" class="vibb-main-action-item-text text-base-xs font-semibold text-white leading-none">{{ item.like_count }}</button>
                        </div>
                        <div class="vibb-main-action-item flex flex-col items-center gap-2">
                            <button @click="handleToggleComment()" :class="buttonActionStyle">
                                <BaseIcon name="message"/>
                            </button>
                            <span class="vibb-main-action-item-text text-base-xs font-semibold text-white leading-none">{{ item.comment_count }}</span>
                        </div>
                        <div class="vibb-main-action-item">
                            <button class="flex flex-col items-center gap-2" @click="handleOpenPostAnalytics">
                                <div :class="buttonActionStyle">
                                    <BaseIcon name="eye"/>
                                </div>
                                <span class="vibb-main-action-item-text text-base-xs font-semibold text-white leading-none">{{ item.view_count }}</span>
                            </button>
                        </div>
                        <div class="vibb-main-action-item">
                            <button
                                ref="bookmarkButton" 
                                :class="['vibb-main-action-bookmark', item.is_bookmarked ? 'text-primary-color dark:text-dark-primary-color is-bookmarked' : '', buttonActionStyle]"
                                @click.stop="item.is_bookmarked ? unBookmarkPost(item.id) : bookmarkPost(item.id)"
                            >
                                <BaseIcon :name="item.is_bookmarked ? 'bookmark_fill' : 'bookmarks'" />
                            </button>
                        </div>
                        <div class="vibb-main-action-item">
                            <button @click="openShareModal('posts', item)" :class="buttonActionStyle">
                                <BaseIcon name="share"/>
                            </button>
                        </div>
                        <div class="vibb-main-action-item">
                            <button v-if="authenticated" @click="openDropdownMenu()" :class="buttonActionStyle">
                                <BaseIcon name="more_horiz_outlined"/>
                            </button>
                        </div>
                        <Avatar v-if="!screen.lg" :user="item.user" :activePopover="false" :border="false" :size="44" tab="vibbs" />
                    </div>
                </ContentWarningWrapper>
            </div>
        </div>
    </template>
    <div v-else class="vibb-wrapper min-h-full w-full py-0 md:py-2">
        <div ref="vibbItemRef" class="h-full relative mx-auto bg-web-wash rounded-md dark:bg-dark-web-wash md:rounded-md" :style="{ width: screen.md ? `100%` : vibbItemWidth }"></div>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { usePostStore } from '@/store/post'
import { useAppStore } from "@/store/app"
import { useVibbStore } from "@/store/vibb"
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import LikersModal from '@/components/modals/LikersModal.vue'
import PostAnalyticsModal from '@/components/modals/PostAnalyticsModal.vue'

export default {
    components: { VideoPlayerShort, BaseIcon, Avatar, UserName, ContentHtml, ContentWarningWrapper },
    props: {
        item: {
            type: Object,
            default: null
        }
    },
    data(){
        return{
            isContentWarning: Boolean(this.item?.content_warning_categories.length && this.item?.showContentWarning),
            vibbItemWidth: 1,
            loadingLike: false,
            loadingUnlike: false,
            loadingBookmark: false,
            loadingUnbookmark: false
        }
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useAppStore, ['screen', 'openedModalCount', 'setOpenedModalCount']),
        ...mapState(useVibbStore, ['showVibbComment']),
        vibbItem(){
            return this.item.items[0]
        },
        songItem(){
            return this.item.items[1]
        },
        buttonActionStyle(){
            return 'vibb-main-action-item-icon w-11 h-11 p-base-2 bg-white rounded-full dark:bg-dark-web-wash'
        }
	},
    watch:{
        item: {
            handler: function() {
                this.isContentWarning = Boolean(this.item.content_warning_categories.length && this.item.showContentWarning)
            },
            deep: true
        },
        openedModalCount(newVal, oldVal){
            if(oldVal === 2 && newVal === 1){
                this.$refs.vibbVideoRef.handlePlayContinueVisibleItem()
            }
        }
    },
    mounted(){
        this.calculateVibbItemWidth();
        window.addEventListener('resize', this.calculateVibbItemWidth);
    },
    unmounted(){
        window.removeEventListener('resize', this.calculateVibbItemWidth);
        this.setCurrentVibb(null)
    },
    methods: {
        ...mapActions(usePostStore, ['toggleLikePostItem', 'toggleBookmarkPostItem']),
        ...mapActions(useVibbStore, ['setCurrentVibb', 'setShowVibbComment']),
        async likePost(postId){
            if(this.authenticated){
                if(this.loadingLike){
                    return;
                }
                this.loadingLike = true
                try {
                    await this.toggleLikePostItem({
                        subject_type: 'posts',
                        subject_id: postId,
                        action: 'add'
                    })
                    this.$refs.likeButton.classList.add('active');
                } catch (error) {
                    this.showError(error.error)
                } finally {
                    this.loadingLike = false
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unLikePost(postId){
            if(this.loadingUnlike){
                return;
            }
            this.loadingUnlike = true
            try {
                await this.toggleLikePostItem({
                    subject_type: 'posts',
                    subject_id: postId,
                    action: 'remove'
                })
                this.$refs.likeButton.classList.remove('active');
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingUnlike = false
            }
        },
        handleToggleComment(){
            this.setShowVibbComment(!this.showVibbComment)
        },
        async bookmarkPost(postId){
            if(this.authenticated){
                if(this.loadingBookmark){
                    return;
                }
                this.loadingBookmark = true
                try {
                    await this.toggleBookmarkPostItem({
                        subject_type: 'posts',
                        subject_id: postId,
                        action: 'add'
                    })
                    this.$refs.bookmarkButton.classList.add('active');
                } catch (error) {
                    this.showError(error.error)
                } finally {
                    this.loadingBookmark = false
                }
            }else{
                this.showRequireLogin()
            }       
        },
        async unBookmarkPost(postId){
            if(this.loadingUnbookmark){
                return;
            }
            this.loadingUnbookmark = true
            try {
                await this.toggleBookmarkPostItem({
                    subject_type: 'posts',
                    subject_id: postId,
                    action: 'remove'
                })
                this.$refs.bookmarkButton.classList.remove('active');
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingUnbookmark = false
            }
        },
        openShareModal(type, subject){
            if(this.authenticated){
                this.setOpenedModalCount()
                this.$dialog.open(ShareOptionsMenu, {
                    data: {
                        subjectType: type,
                        subject: subject
                    },
                    props:{
                        showHeader: false,
                        class: 'dropdown-menu-modal',
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    onClose: () => {
                        checkPopupBodyClass();
                        this.setOpenedModalCount(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
        },
        openDropdownMenu(){
            this.setOpenedModalCount()
            this.$dialog.open(PostOptionsMenu, {
                data: {
                    post: this.item
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            });
        },
        openLikersModal(type, id){
            this.setOpenedModalCount()
            this.$dialog.open(LikersModal, {
                data: {
                    itemType: type,
                    itemId: id
                },
                props:{
                    header: this.$t('Likes'),
                    class: 'likers-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            })
        },
        handleOpenPostAnalytics(){
            if(this.authenticated){
                this.setOpenedModalCount()
                this.$dialog.open(PostAnalyticsModal, {
                    data: {
                        post: this.item
                    },
                    props:{
                        header: this.$t('Post Analytics'),
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    onClose: () => {
                        checkPopupBodyClass();
                        this.setOpenedModalCount(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
        },
        calculateVibbItemWidth() {
            this.$nextTick(() => {
                const el = this.$refs.vibbItemRef;
                if (el && el.offsetHeight) {
                    const newWidth = el.offsetHeight * (9 / 16);
                    this.vibbItemWidth = `${newWidth}px`;
                }
            });
        },
        handleSetCurrentVibb(){
            this.setCurrentVibb(this.item)
            let vibbUrl = this.$router.resolve({
                name: 'vibb',
                query: { 'id': this.item.id}
            });
            changeUrl(vibbUrl.fullPath)
        }
    }
}
</script>