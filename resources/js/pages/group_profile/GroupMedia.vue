<template>
    <div class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Media') }}</h3>
        <MasonryPostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreMedias" />
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { usePostStore } from '@/store/post'
import MasonryPostsList from '@/components/posts/MasonryPostsList.vue'

export default {
    props: ['groupInfo'],
    components: { MasonryPostsList },
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		if (!this.groupInfo.canView) {
            return this.$router.push({ 'name': 'permission' })
        }
		this.getGroupMediasList(this.groupInfo.id, this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getGroupMediasList', 'unsetPostsList']),
		...mapActions(useAppStore, ['setErrorLayout']),
		loadMoreMedias($state) {
			this.getGroupMediasList(this.groupInfo.id, ++this.currentPage).then((response) => {
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