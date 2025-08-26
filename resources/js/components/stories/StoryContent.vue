<template>
    <div class="flex items-center absolute inset-0 bg-gray-300 w-full h-full lg:rounded-base-lg overflow-hidden">
        <div class="h-48 bg-story-detail-linear absolute top-0 left-0 right-0"></div>
        <template v-if="story.type === 'text'">
            <img class="h-full w-full" :src="story.background.photo_url">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center font-medium w-[80%] max-h-[65%] overflow-x-hidden overflow-y-auto story-content-box" :style="{ 'color': story.content_color }">
                <ContentHtml class="story_content mb-base-2" :content="story.content" :limit="limit" :contentKey="story.id" @click_read_more="clickReadmore"/>
            </div>
        </template>
        <img v-if="story.type === 'photo'" class="mx-auto max-h-full" :src="story.photo_url">
        <div class="h-36 bg-footer-linear absolute bottom-0 left-0 right-0"></div>
    </div>
</template>

<script>
import ContentHtml from '@/components/utilities/ContentHtml.vue'

export default {
    components: { ContentHtml },
    props: {
        story: {
            type: Object,
            default: null
        },
        limit: {
            type: Number,
            default: 50
        }
    },
    methods: {
        clickReadmore(){
            this.$emit('click_read_more')
        }
    },
    emits: ['click_read_more']
}
</script>