<template>
    <div v-if="pagesList.length" class="widget-box bg-white border border-white rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
        </div>
        <UserPagesWidgetList :pagesList="pagesList" :positionWidget="position" :listStyle="(['left', 'right'].includes(position) ? 'list' : 'slider')"/>
    </div>
</template>

<script>
import UserPagesWidgetList from '@/components/lists/UserPagesWidgetList.vue';

export default {
    props: ['data', 'params', 'position'],
    components: { UserPagesWidgetList },
    data(){
        return{
            pagesList: this.data.data
        }
    }
}
</script>