<template>
    <div>
        <form @submit.prevent="handleSelectKeyword(searchResultsList['text'], 'keyword')" class="flex flex-col gap-base-2">
            <label for="">{{ $t('Keyword / Hashtags') }}</label>
            <div class="relative">
                <BaseInputText v-model="searchKeyword" @input="onEnterSearch()" autofocus />
                <div ref="showButton" @click="handleShowResultsBox"></div>
                <div ref="hideButton" @click="handleCloseResultsBox"></div>
                <OverlayPanel ref="searchDropdownResult" class="max-w-[464px] w-full">
                    <div v-if="searchKeyword">
                        <div class="text-sm p-base-2 cursor-pointer" @click="handleSelectKeyword(searchResultsList['text'], 'keyword')">
                            <div v-html="matchingSearch(searchResultsList['text'])"></div>
                        </div>							
                        <div v-for="(hashtag, index) in searchResultsList.hashtags" :key="index" @click="handleSelectKeyword(hashtag.name, 'hashtag')" class="text-sm p-base-2 cursor-pointer">
                            <div v-html="hashtagChar + matchingSearch(hashtag.name)"></div>
                        </div>
                    </div>
                </OverlayPanel>
            </div>
            <BaseButton>{{ $t('Search') }}</BaseButton>
        </form>
    </div>
</template>

<script>
import { getSearchSuggest } from '@/api/search'
import { mapActions } from 'pinia'
import { useGroupStore } from '@/store/group'
import { useAppStore } from '@/store/app'
import { changeUrl } from '@/utility'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import Constant from '@/utility/constant'
import OverlayPanel from 'primevue/overlaypanel';

export default {
    components: { BaseInputText, BaseButton, OverlayPanel },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            searchKeyword: '',
			searchResultsList: {},
            hashtagChar: Constant.HASHTAG,
            item: this.dialogRef.data.item,
            type: this.dialogRef.data.type
        }
    },
    methods: {
        ...mapActions(useGroupStore, ['setSearchKeyword', 'setSearchType']),
        ...mapActions(useAppStore, ['setCurrentRouter']),
        handleShowResultsBox(e){
            this.$refs.searchDropdownResult.show(e);
		},
		handleCloseResultsBox(e){
            this.$refs.searchDropdownResult.hide(e);
		},
        openSearchBox(){
            this.$refs.showButton?.click()
        },
        closeSearchBox(){
            this.$refs.hideButton?.click()
        },
        debouncedSearch: window._.debounce(function() {
            this.getSearchSuggestsList(this.searchKeyword)
        }, 500),
		onEnterSearch(){
			this.debouncedSearch();
		},
        async getSearchSuggestsList(keyword){
            if(keyword){
                try {
                    this.searchResultsList['text'] = keyword
                    this.searchResultsList['hashtags'] = null
                    const response = await getSearchSuggest(keyword)
                    this.openSearchBox()
                    if(response.hashtags.length){
                        this.searchResultsList['hashtags'] = response.hashtags
                    }
                } catch (error) {
                    console.log(error)
                }
            }
		},
		matchingSearch(keyword){
			let words = this.searchKeyword
			keyword = window._.replace(window._.lowerCase(keyword), window._.lowerCase(words), '<span class="font-bold">'+words+'</span>')
			return keyword
		},
        handleSelectKeyword(keyword, type){
            this.setSearchKeyword(keyword)
            this.setSearchType(type)
            this.dialogRef.close()
            let url;
            switch (this.type) {
                case 'group':
                    url = this.$router.resolve({
                        name: 'group_profile',
                        params: { id: this.item.id, slug: this.item.slug }
                    });
                    break;
                default:
                    break;
                }
                if(url){
                    changeUrl(url.fullPath);
                    this.setCurrentRouter({ name: url.name, params: url.params })
                }
        }
    }
}
</script>