<template>
    <Loading v-if="loading" />
    <template v-else>
        <template v-if="vibbsList.length > 0">
            <VibbItem v-for="vibb in vibbsList" :key="vibb.id" :item="vibb" />
            <InfiniteLoading @infinite="handleLoadInfinite">
                <template #spinner>
                    <Loading />
                </template>
                <template #complete><span></span></template>
            </InfiniteLoading>
        </template>
    </template>
</template>

<script>
import VibbItem from '@/components/vibb/VibbItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { VibbItem, InfiniteLoading, Loading },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        vibbsList: {
            type: Array,
            default: () => []
        }
    },
    methods:{
        handleLoadInfinite(state){
            this.$emit('load-more', state)
        }
    },
    emits: ['load-more']
}
</script>
