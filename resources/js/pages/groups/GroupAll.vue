<template>
    <div class="p-base-2 md:p-0">
        <form class="flex flex-wrap gap-base-2 mb-base-2" @submit.prevent="handleSearchGroups">
            <BaseInputText v-model="searchKeyword" :placeholder="$t('Keyword')" class="flex-1 min-w-[150px] max-w-[240px]"/>
            <TreeSelect class="flex-1 min-w-[150px] max-w-[240px]" v-model="selectedCategory" :options="categoriesList" optionLabel="key" optionValue="label" :placeholder="$t('Category')" />
            <BaseButton>{{$t('Search')}}</BaseButton>
        </form>
        <GroupsList :key="key" :loading="loadingGroupsList" :groups-list="groupsList" @load-more="loadmoreGroups" />
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { changeUrl } from '@/utility/index'
import { getGroupCategories } from '@/api/group'
import { useGroupStore } from '@/store/group'
import GroupsList from '@/components/group/GroupsList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import TreeSelect from 'primevue/treeselect';
import { uuidv4 } from '@/utility';

export default {
    props: ['categoryId'],
    components: { GroupsList, BaseButton, BaseInputText, TreeSelect },
    data(){
        return {
            searchPage: 1,
            searchKeyword: '',
            selectedCategory: window._.invert({true: this.categoryId ? this.categoryId : ""}),
            categoriesList: [],
            key: uuidv4()
        }
    },
    mounted() {
        this.handleGetGroupCategories()
        this.getAllGroupsList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
    },
    unmounted(){
        this.unsetGroupsList()
    },
    computed: {
        ...mapState(useGroupStore, ['loadingGroupsList', 'groupsList']),
        formatedSelectedCategory(){
            return Object.keys(this.selectedCategory)[0]
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['getAllGroupsList', 'unsetGroupsList']),
        async handleGetGroupCategories(){  
            try {
                const response = await getGroupCategories()
                this.categoriesList = window._.map(response, function(key) {
                    return { key: key.id, label: key.name, children: window._.map(key.childs, function(childKey){
                        return { key: childKey.id, label: childKey.name }
                    }) }
                });
                this.categoriesList = [{ key: "", label: this.$t('All')}, ...this.categoriesList]
            } catch (error) {
                console.log(error)
            }
        },
        handleSearchGroups(){
            this.searchPage = 1;
            this.key = uuidv4()
            this.getAllGroupsList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
            let groupUrl = this.$router.resolve({
                name: 'groups',
                params: { tab: 'all' },
                query: { 'category_id': this.formatedSelectedCategory }
            });
            changeUrl(groupUrl.fullPath)
        },
        loadmoreGroups($state) {
			this.getAllGroupsList(++this.searchPage, this.searchKeyword, this.formatedSelectedCategory).then((response) => {
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