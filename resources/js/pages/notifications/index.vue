<template>
	<div class="main-content-section bg-white p-5 rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800">	
		<NotificationsList />
	</div>
</template>
<script>
import NotificationsList from '@/components/notifications/NotificationsList.vue';

export default {
    components: { NotificationsList }
}
</script>