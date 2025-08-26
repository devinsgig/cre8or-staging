<template>
    <div class="px-base-2 md:px-4">
        <div class="feed-share-content bg-primary-box-color rounded-base-lg p-3 dark:bg-dark-web-wash">
            <div class="feed-main-info">
                <div class="activity_feed_header flex mb-base-1">
                    <div class="whitespace-normal">
                        <UserName :user="post.parent.user" :truncate="false" /> 
                        {{$t('on')}}
                        <router-link :to="{name: 'post', params: {id: post.parent.id}}" class="text-inherit">{{post.parent.created_at}}</router-link>
                    </div>
                </div>        
                <div class="activity_feed_content">
                    <ContentHtml class="activity_feed_content_message" :content="post.parent.content" :mentions="post.parent.mentions" />
                    <div class="activity_feed_content_item mt-base-2">
                        <template v-if="['video', 'vibb'].includes(post.parent.type)">
                            <Component v-if="postTypeComponent" :is="postTypeComponent" :post="post.parent" :parent-post="post" :autoPlay="autoPlay"/>
                        </template>
                        <div v-else class="-mx-base-2">
                            <Component v-if="postTypeComponent" :is="postTypeComponent" :post="post.parent" :parent-post="post" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import UserName from '@/components/user/UserName.vue'
import FeedPhotos from '@/components/posts/types/FeedPhotos.vue'
import FeedShareLink from '@/components/posts/types/FeedShareLink.vue'
import FeedVideo from '@/components/posts/types/FeedVideo.vue'
import FeedFiles from '@/components/posts/types/FeedFiles.vue'
import FeedPolls from '@/components/posts/types/FeedPolls.vue'
import FeedVibb from '@/components/posts/types/FeedVibb.vue'

export default {
    components: { ContentHtml, UserName },
    props: {
        post: {
            type: Object,
            default: null
        },
        autoPlay:{
            type: Boolean,
            default: false
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    computed: {
        postTypeComponent(){
            switch(this.post.parent.type) {
                case 'photo':
                    return FeedPhotos
                case 'link':
                    return FeedShareLink
                case 'video':
                    return FeedVideo
                case 'file':
                    return FeedFiles
                case 'poll':
                    return FeedPolls
                case 'vibb':
                    return FeedVibb
            }  
            return null
        }
    },
}
</script>