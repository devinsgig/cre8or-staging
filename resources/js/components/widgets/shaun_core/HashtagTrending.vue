<template>
	<div v-if="data.data.length > 0" class="widget-box bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
		<div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
			<h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
			<router-link :to="{name: 'hashtags', params: {tab: 'trending'}}" class="widget-box-header-link text-xs font-bold">
				{{$t('View All')}}
			</router-link>
		</div>
		<HashtagsList :hashtagsList="data.data"/>
	</div>
</template>

<script>
import HashtagsList from '@/components/lists/HashtagsList.vue';

export default {
    components: { HashtagsList },
	props: ['data', 'params', 'position']
}
</script>