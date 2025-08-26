<template>
    <Loading v-if="loadingPages"/>
    <template v-else>
        <div v-if="pagesList.length">
            <div v-for="page in pagesList" :key="page.id" class="flex items-center gap-base-2 mb-base-2 cursor-pointer" @click="handleClickSwitchPage(page)">
                <PageSwitchItem :page="page"/>
                <div class="flex-1 font-bold">{{ page.name }}</div>
            </div>
            <router-link v-if="hasNextPage" :to="{ name: 'list_page' }">{{ $t('See more') }}</router-link>
        </div>
        <div v-else>{{ $t('Sorry, there is no page yet! Please create new page.') }}</div>
    </template>
    <BaseButton @click="createPage" class="w-full mt-base-2">{{ $t('Create new page') }}</BaseButton>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { switchPage } from '@/api/page'
import Loading from '@/components/utilities/Loading.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PageSwitchItem from '@/components/user_page/PageSwitchItem.vue'
import { usePagesStore } from '@/store/page'
import { useAuthStore } from '@/store/auth'
import { useUtilitiesStore } from '@/store/utilities'

export default {
    components: { Loading, BaseButton, PageSwitchItem },
    data() {
		return {
			currentPage: 1,
            hasNextPage: false
		}
	},
    inject: ['dialogRef'],
    computed: {
        ...mapState(useAuthStore, ['user']),
		...mapState(usePagesStore, ['loadingPages', 'pagesList'])
    },
    mounted(){
        this.getMyPagesList(this.currentPage).then((response) => {
            this.hasNextPage = response.has_next_page
        })	
    },
    unmounted(){
        this.unsetPagesList()
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        ...mapActions(usePagesStore, ['getMyPagesList', 'unsetPagesList']),
        handleClickSwitchPage(page){
            this.setSelectedPage(page)
            setTimeout(async() => {
                try {  
                    await switchPage(page.id)
                    window.location.href = page.href
                } catch (error) {
                    this.showError(error.error)
                    this.setSelectedPage(null)
                    this.dialogRef.close()
                }
            }, 1500);
		},
        createPage() {
            if (this.user) {
				let permission = 'user_page.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'user_pages_create' })
                }
			}
        }
    }
}
</script>