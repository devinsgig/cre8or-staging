<template>
    <div>
        <SkeletonMasonryPostsList v-if="loading" />
        <template v-else>
            <div v-if="postsList.length > 0" class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-1 md:gap-base-2">
                <TransitionGroup name="fade">
                    <MasonryPostListsItem v-for="post in postsList" :key="post.id" :post="post" />
                </TransitionGroup>
                <InfiniteLoading v-if="loadMore" @infinite="handleLoadInfinite">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </div>
            <template v-else>
                <slot name="empty">
                    <div class="main-content-section bg-white border border-white text-main-color p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                        <div class="text-base-lg font-bold">{{ $t('Nothing to see here yet') }}</div>
                    </div>
                </slot>
            </template>
        </template>
    </div>
</template>

<script>
import SkeletonMasonryPostsList from '@/components/skeletons/SkeletonMasonryPostsList.vue'
import MasonryPostListsItem from '@/components/posts/MasonryPostListsItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { SkeletonMasonryPostsList, MasonryPostListsItem, InfiniteLoading, Loading },
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