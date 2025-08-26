<template>
    <div v-if="groupsList.length" class="widget-box bg-white border border-white rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
			<h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
		</div>
        <WidgetGroupsList :groups-list="groupsList"/>
    </div>
</template>

<script>
import WidgetGroupsList from '@/components/lists/WidgetGroupsList.vue';

export default {
    props: ['data', 'params', 'position'],
    components: { WidgetGroupsList },
    data(){
        return{
            groupsList: this.data.data
        }
    }
}
</script>