<template>
    <div class="pb-5">
        <SkeletonPostsList v-if="loading" />
        <template v-else>
            <template v-if="postsList.length > 0">
                <TransitionGroup name="fade">
                    <PostListsItem v-for="post in postsList" :key="post.id" :post="post" :shadow="shadow" />
                </TransitionGroup>
                <InfiniteLoading v-if="loadMore" @infinite="handleLoadInfinite">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <template v-else >
                <slot name="empty">
                    <div class="main-content-section bg-white border border-white text-main-color p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                        <div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
                        <div class="text-base-sm">{{ $t('Start following people and tags to see updated posts') }}</div>
                    </div>
                </slot>
            </template>
        </template>
    </div>
</template>

<script>
import SkeletonPostsList from '@/components/skeletons/SkeletonPostsList.vue'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { SkeletonPostsList, PostListsItem, InfiniteLoading, Loading },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        postsList: {
            type: Array,
            default: () => []
        },
        loadMore: {
            type: Boolean,
            default: true
        },
        shadow: {
            type: Boolean,
            default: false
        }
    },
    methods:{
        handleLoadInfinite(state){
            if(this.loadMore){
                this.$emit('load-more', state)
            }
        }
    },
    emits: ['load-more']
}
</script>