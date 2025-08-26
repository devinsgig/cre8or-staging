<template>
    <div class="flex flex-wrap gap-base-2 px-base-2 md:px-4">
        <a v-for="postItem in post.items" :key="postItem.id" :href="postItem.subject.url" :download="postItem.subject.name"  class="bg-white text-inherit border border-divider px-base-2 py-2 rounded-1000 relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
            <div class="flex items-center gap-2">
                <BaseIcon name="file" />
                <span class="truncate">{{ postItem.subject.name }}</span>
            </div>
        </a>
    </div>
</template>

<script>
import BaseIcon from "@/components/icons/BaseIcon.vue"

export default {
    props: {
        post: {
            type: Object,
            default: null
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    components: { BaseIcon }
}
</script>