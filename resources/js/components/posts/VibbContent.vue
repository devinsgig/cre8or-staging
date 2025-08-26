<template>
    <template v-if="item" >
        <ContentWarningWrapper :content-warning-list="item.content_warning_categories" :post="item" class="h-full rounded-none md:rounded-base-lg">
            <div class="relative pb-[100%] cursor-pointer" @click="handleOpenVibbModal">
                <div class="absolute inset-0" :id="`vibbItem-${item.id}`">
                    <VideoPlayerShort ref="vibbFeedRef" :video="vibbItem.subject" :autoPlay="autoPlay" :allow-pip="false" :allow-toggle-play="false" :show-progress-bar="false" :is-content-warning="isContentWarning" class="w-full h-full rounded-none md:rounded-base-lg overflow-hidden z-20" />
                    <div class="absolute start-3 bottom-3 end-16 text-white max-h-1/3 overflow-y-auto scrollbar-hidden z-20" @click.stop>
                        <div class="flex gap-base-2 items-center mb-base-1">
                            <Avatar :user="item.user" :border="false" :activePopover="false" tab="vibbs" />
                            <UserName :user="item.user" :activePopover="false" tab="vibbs" />
                        </div>
                        <ContentHtml :content="item.content" :mentions="item.mentions" :limit="100" />
                        <span v-if="songItem" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 mt-base-1">
                            <BaseIcon name="music_note" size="16" />
                            {{ songItem.subject.name }}
                        </span>
                    </div>
                    <div class="absolute bottom-3 end-3 hidden md:flex flex-col gap-5 z-20">
                        <div class="flex flex-col items-center gap-1">
                            <button 
                                ref="likeButton"
                                :class="['feed-main-action-like', item.is_liked ? 'text-base-red is-liked' : '', buttonActionStyle]"
                                @click.stop="item.is_liked ? unLikePost(item.id) : likePost(item.id)"
                            >
                                <BaseIcon :name="item.is_liked ? 'heart_fill' : 'heart'" />
                            </button>
                            <button @click.stop="openLikersModal('posts', item.id)" class="text-base-xs font-semibold text-white">{{ item.like_count }}</button>
                        </div>
                        <div class="flex flex-col items-center gap-1" @click.stop="handleCommentVibb()">
                            <button :class="buttonActionStyle">
                                <BaseIcon name="message"/>
                            </button>
                            <span class="text-base-xs font-semibold text-white">{{ item.comment_count }}</span>
                        </div>
                        <button class="flex flex-col items-center gap-1" @click.stop="handleOpenPostAnalytics">
                            <div :class="buttonActionStyle">
                                <BaseIcon name="eye"/>
                            </div>
                            <span class="text-base-xs font-semibold text-white">{{ item.view_count }}</span>
                        </button>
                        <button
                            ref="bookmarkButton" 
                            :class="['feed-main-action-bookmark', item.is_bookmarked ? 'text-primary-color dark:text-dark-primary-color is-bookmarked' : '', buttonActionStyle]"
                            @click.stop="item.is_bookmarked ? unBookmarkPost(item.id) : bookmarkPost(item.id)"
                        >
                            <BaseIcon :name="item.is_bookmarked ? 'bookmark_fill' : 'bookmarks'" />
                        </button>
                        <button @click.stop="openShareModal('posts', item)" :class="buttonActionStyle">
                            <BaseIcon name="share"/>
                        </button>
                        <button v-if="authenticated" @click.stop="openDropdownMenu()" :class="buttonActionStyle">
                            <BaseIcon name="more_horiz_outlined"/>
                        </button>
                    </div>
                </div>
            </div>
        </ContentWarningWrapper>
    </template>
    <Error v-else class="mb-0">{{$t('Vibb is not found')}}</Error>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import LikersModal from '@/components/modals/LikersModal.vue'
import Error from '@/components/utilities/Error.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import { useAuthStore } from '@/store/auth'
import { usePostStore } from '@/store/post'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { useVibbStore } from '@/store/vibb'
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostAnalyticsModal from '@/components/modals/PostAnalyticsModal.vue'

export default {
    components: { Error, VideoPlayerShort, ContentWarningWrapper, BaseIcon, Avatar, UserName, ContentHtml },
    props: {
        item: {
            type: Object,
            default: null
        },
        showMenuAction: {
            type: Boolean,
            default: true
        },
        autoPlay:{
            type: Boolean,
            default: false
        }
    },
    data(){
        return{
            isContentWarning: Boolean(this.item.content_warning_categories.length && this.item.showContentWarning),
            loadingLike: false,
            loadingUnlike: false,
            loadingBookmark: false,
            loadingUnbookmark: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated', 'user']),
        ...mapState(useAppStore, ['setOpenedModalCount']),
        ...mapState(useVibbStore, ['showVibbComment']),
        buttonActionStyle(){
            return 'w-11 h-11 p-base-2 bg-white rounded-full dark:bg-dark-web-wash'
        },
        songItem(){
            return this.item.items[1]
        },
        vibbItem(){
            return this.item.items[0]
        }
    },
    watch:{
        item: {
            handler: function() {
                this.isContentWarning = Boolean(this.item.content_warning_categories.length && this.item.showContentWarning)
            },
            deep: true
        }
    },
    methods: {
        ...mapActions(usePostStore, ['toggleLikePostItem', 'toggleBookmarkPostItem']),
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        ...mapActions(useVibbStore, ['setShowVibbComment']),
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
        handleOpenVibbModal(){
            this.openVibb({
                vibb: this.item
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
        handleCommentVibb(){
            this.handleOpenVibbModal()
            this.setShowVibbComment(!this.showVibbComment)
        }
    }
}
</script>