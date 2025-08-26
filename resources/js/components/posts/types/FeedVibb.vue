<template>
    <div v-if="post" class="relative pb-[100%] cursor-pointer" @click="handleOpenVibbModal">
        <div class="absolute inset-0" :id="`vibbItem-${post.id}`">
            <ContentWarningWrapper :content-warning-list="post.content_warning_categories" :post="post" class="h-full rounded-md overflow-hidden">
                <VideoPlayerShort ref="feedVibbRef" :video="vibbItem.subject" :autoPlay="autoPlay" :allow-toggle-play="false" :preload="true" :allow-pip="false" :show-progress-bar="false" :is-content-warning="isContentWarning" class="w-full h-full rounded-none overflow-hidden" />
            </ContentWarningWrapper>
        </div>
    </div>
</template>

<script>
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue'

export default {
    components: { VideoPlayerShort, ContentWarningWrapper },
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
    data(){
        return{
            isContentWarning: Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
        }
    },
    computed: {
        vibbItem(){
            return this.post.items[0]
        }
    },
    watch:{
        post: {
            handler: function() {
                this.isContentWarning = Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
            },
            deep: true
        }
    },
    methods:{
        handleOpenVibbModal(){
            this.openVibb({
                vibb: this.post
            })
        }
    }
}
</script>