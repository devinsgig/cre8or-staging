<template>
    <div 
        class="feed-item bg-white border border-white rounded-none md:rounded-base-lg mb-base-2 dark:bg-slate-800 dark:border-slate-800"
        :class="{'shadow-post-item dark:shadow-post-item-dark': shadow}">
        <div v-if="post.type === 'vibb'">
            <VibbContent :item="post" :auto-play="user.video_auto_play" />
        </div>
        <PostContent v-else :post="post" :auto-play="user.video_auto_play" @comment_click="showPostDetail" @comment_count_click="showPostDetail"/>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import PostDetailModal from '@/components/posts/PostDetailModal.vue'
import PostContent from '@/components/posts/PostContent.vue'
import VibbContent from '@/components/posts/VibbContent.vue'

export default {
    components: {  PostContent, VibbContent },
    props: {
        post: Object,
        shadow: {
            type: Boolean,
            default: false
        }
    },
    computed:{
        ...mapState(useAppStore, ['setOpenedModalCount']),
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        showPostDetail(){
            let postUrl = this.$router.resolve({
                name: 'post',
                params: { 'id': this.post.id }
            });
            changeUrl(postUrl.fullPath)
            this.setOpenedModalCount()
            this.$dialog.open(PostDetailModal, {
                data: {
                    post: this.post
                },
                props:{
                    class: 'post-detail-modal p-dialog-lg p-dialog-full-page',
                    modal: true,
                    dismissableMask: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                    this.setOpenedModalCount(false)
                }
            });
        }
    }
}
</script>