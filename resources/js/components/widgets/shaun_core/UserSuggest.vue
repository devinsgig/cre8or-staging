<template>
	<div v-if="data.data.length" class="widget-box bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
		<div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
			<h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
			<router-link :to="{name: 'users', params: {tab: 'suggest'}}" class="widget-box-header-link text-xs font-bold">
				{{$t('View All')}}
			</router-link>
		</div>
		<UsersList :loading="!data.data" :usersList="data.data" :positionWidget="position" :listStyle="(['left', 'right'].includes(position) ? 'list' : 'slider')"/>
	</div>
</template>

<script>
import UsersList from '@/components/lists/UsersList.vue'

export default {
    components: { UsersList },
	props: ['data', 'params', 'position']
}
</script>