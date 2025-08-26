<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreUserFeeds">
		<template #empty>
			<div class="main-content-section bg-white border border-white text-main-color p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
				{{ $t('Nothing to see here yet') }}
			</div>
		</template>
	</PostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import { useAuthStore } from '@/store/auth'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: { PostsList },
	props: ['userInfo'],
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getUserPostsList(this.userInfo.id, this.currentPage);
		if(this.userInfo.user_name === this.user.user_name){
			this.setCurrentPostPage('profile')
		}
    },
	unmounted(){
		this.unsetPostsList()
		this.setCurrentPostPage()
	},
    methods: {
		...mapActions(usePostStore, ['getUserPostsList', 'unsetPostsList', 'setCurrentPostPage']),
		loadMoreUserFeeds($state) {
			this.getUserPostsList(this.userInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	},
	emits: ['change_tab', 'update_user_info']
}
</script>