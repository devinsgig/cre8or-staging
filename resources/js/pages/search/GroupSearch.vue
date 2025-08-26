<template>
	<div class="bg-white p-base-2 rounded-none md:rounded-base-lg mb-base-2 dark:bg-slate-800">
        <GroupsList :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadmoreGroups">
            <template #empty>
                <div class="text-center">{{$t('No groups are found')}}</div>
            </template>
        </GroupsList>
	</div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useGroupStore } from '@/store/group'
import GroupsList from '@/components/group/GroupsList.vue';

export default {
	props: ["search_type", "type", "query"],
	components: { GroupsList },
	data(){
        return{
            queryData: this.query,
            currentPage: 1
        }
    },
    computed:{
        ...mapState(useGroupStore, ['loadingGroupsList', 'groupsList']),
    },
    mounted(){
        this.getSearchGroupsList(this.search_type, this.queryData, this.type, this.currentPage)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchGroupsList(this.search_type, this.queryData, this.type, this.currentPage)
            }
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['getSearchGroupsList', 'unsetGroupsList']),
        loadmoreGroups($state) {
			this.getSearchGroupsList(this.search_type, this.queryData, this.type, ++this.currentPage).then((response) => {
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