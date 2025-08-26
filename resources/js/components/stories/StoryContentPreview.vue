<template>
    <div class="flex items-center absolute inset-0 bg-gray-300 w-full h-full lg:rounded-base-lg overflow-hidden">
        <template v-if="story.type === 'text'">
            <img class="h-full w-full" :src="story.background.photo_url">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center font-medium w-[80%]" :style="{ 'color': story.content_color }">
                <div class="story_content truncate-4">{{story.content}}</div>
            </div>
        </template>
        <img v-if="story.type === 'photo'" class="mx-auto max-h-full" :src="story.photo_url">
        <div class="bg-story-preview-linear absolute inset-0"></div>
    </div>
</template>

<script>

export default {
    props: ['story']
}
</script>