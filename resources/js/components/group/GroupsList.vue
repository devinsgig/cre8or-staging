<template>
    <SkeletonGroupsList v-if="loading" />
    <template v-else>
        <div v-if="groupsList.length" class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">
            <TransitionGroup name="fade">
                <GroupListItem v-for="group in groupsList" :key="group.id" :item="group" :show-button="showButton" :show-badge="showBadge" />
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
                <div class="bg-white rounded-none md:rounded-base-lg p-5 text-center dark:bg-slate-800">{{ $t('No groups are found') }}</div>
            </slot>
        </template>
    </template>
</template>

<script>
import SkeletonGroupsList from '@/components/skeletons/SkeletonGroupsList.vue'
import GroupListItem from '@/components/group/GroupListItem.vue';
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        groupsList: {
            type: Array,
            default: () => []
        },
        loadMore: {
            type: Boolean,
            default: true
        },
        showButton: {
            type: Boolean,
            default: true
        },
        showBadge: {
            type: Boolean,
            default: false
        }
    },
    components: { SkeletonGroupsList, InfiniteLoading, GroupListItem, Loading },
    methods: {
        handleLoadInfinite(state){
            if(this.loadMore){
                this.$emit('load-more', state)
            }
        }
    },
    emits: ['load-more']
} 
</script>