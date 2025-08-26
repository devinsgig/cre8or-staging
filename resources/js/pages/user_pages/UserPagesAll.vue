<template>
    <div>
        <form class="flex flex-wrap gap-base-2 mb-base-2" @submit.prevent="searchPagesList">
            <BaseInputText v-model="searchKeyword" :placeholder="$t('Keyword')" class="flex-1 min-w-[150px] max-w-[240px]"/>
            <TreeSelect class="flex-1 min-w-[150px] max-w-[240px]" v-model="selectedCategory" :options="categoriesList" optionLabel="key" optionValue="label" :placeholder="$t('Category')" />
            <BaseButton>{{$t('Search')}}</BaseButton>
        </form>
        <UserPagesList :loading="loadingPagesList" :pagesList="pagesList" :has-load-more="loadMoreStatus" @load-more="getPagesList(searchPage, searchKeyword, formatedSelectedCategory)" />
    </div>
</template>

<script>
import { changeUrl } from '@/utility/index'
import UserPagesList from '@/components/lists/UserPagesList.vue';
import { getPageCategories, getAllUserPages } from '@/api/page'
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import TreeSelect from 'primevue/treeselect';

export default {
    props: ['categoryId'],
    components: { UserPagesList, BaseButton, BaseInputText, TreeSelect },
    data(){
        return {
            searchPage: 1,
            searchKeyword: '',
            selectedCategory: window._.invert({true: this.categoryId ? this.categoryId : ""}),
            categoriesList: [],
            loadingPagesList: true,
            loadMoreStatus: false,
            pagesList: []
        }
    },
    mounted() {
        this.fetchPageCategories()
        this.getPagesList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
    },
    computed: {
        formatedSelectedCategory(){
            return Object.keys(this.selectedCategory)[0]
        }
    },
    methods: {
        async fetchPageCategories(){  
            try {
                const response = await getPageCategories()
                this.categoriesList = window._.map(response, function(key) {
                    return { key: key.id, label: key.name, children: window._.map(key.childs, function(childKey){
                        return { key: childKey.id, label: childKey.name }
                    }) }
                });
                this.categoriesList = [{ key: "", label: 'All'}, ...this.categoriesList]
            } catch (error) {
                console.log(error)
            }
        },
        async getPagesList(page, keyword, category){
            try {
                const response = await getAllUserPages(page, keyword, category)
                if(page === 1){
                    this.pagesList = []
                }
                this.pagesList = window._.concat(this.pagesList, response.items);
                if(response.has_next_page){
                    this.loadMoreStatus = true
                    this.searchPage++;
                }else{
                    this.loadMoreStatus = false
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingPagesList = false
            }
        },
        searchPagesList()
        {
            this.searchPage = 1;
            this.getPagesList(this.searchPage, this.searchKeyword, this.formatedSelectedCategory)
            let pageUrl = this.$router.resolve({
                name: 'user_pages',
                query: { 'category_id': this.formatedSelectedCategory }
            });
            changeUrl(pageUrl.fullPath)
        }
    }
}
</script>