<template>
	<h2 class="text-2xl font-bold mb-5">{{ $t('Pages') }}</h2>
	<UserPagesList :loading="loadingPages" :pagesList="pagesList" :auto-load-more="true" @load-more="loadMorePages" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import UserPagesList from '@/components/lists/UserPagesList.vue';
import { usePagesStore } from '@/store/page'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'

export default {
    components: { UserPagesList },
    data() {
		return {
			currentPage: 1
		}
	},
    computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(usePagesStore, ['loadingPages', 'pagesList'])
    },
    mounted(){
		if (this.user.is_page) {
            this.setErrorLayout(true)
        } else {
			this.getMyPagesList(this.currentPage)
        }
    },
    unmounted(){
        this.unsetPagesList()
    },
    methods: {
		...mapActions(useAppStore, ['setErrorLayout']),
        ...mapActions(usePagesStore, ['getMyPagesList', 'unsetPagesList']),
        loadMorePages($state) {
			this.getMyPagesList(++this.currentPage).then((response) => {
				if(response.has_next_page){
                    $state.loaded()
                }else{
                    $state.complete()
                }
			})	
		}
    }
}
</script>