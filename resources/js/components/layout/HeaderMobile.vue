<template>
    <div class="header-mobile flex items-center justify-between gap-base-2 lg:hidden fixed inset-x-0 top-0 bg-white px-4 py-3 z-[999] dark:bg-slate-800">
		<Avatar :user="user" :activePopover="false" :router="false" :size="28" @click="setIsOpenSidebar(true)"/>
		<LogoMobile :className="'max-w-[200px] max-h-6'" />
		<button @click="openSearchForm">
			<BaseIcon name="search" class="header-mobile-icon text-main-color dark:text-white" />
		</button>   
        <Teleport to="body">
			<Transition name="fade">
				<div v-if="showSearchForm" class="global-search-header-mobile lg:hidden"> 
					<BaseIcon name="close" id="closeMobileSearchBtn" @click="closeSearchForm"/>
					<GlobalSearch :autofocus="true"/>      
				</div>
			</Transition>
			<div class="backdrop-modal" :class="{'show': isOpenSidebar}" @click="setIsOpenSidebar(false)"></div>
        </Teleport>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import GlobalSearch from '@/components/layout/GlobalSearch.vue';
import LogoMobile from '@/components/utilities/LogoMobile.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    name: "HeaderMobile",
    components: { BaseIcon, GlobalSearch, LogoMobile, Avatar },
	data(){
		return{
			showSearchForm: false
		}
	},
	computed:{
        ...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['isOpenSidebar'])
    },
	watch: {
        '$route'() {
            this.showSearchForm = false
        }
    },
	methods:{
		...mapActions(useAppStore, ['setIsOpenSidebar']),
		openSearchForm(){
			this.showSearchForm = true
		},
		closeSearchForm(){
			this.showSearchForm = false
		}
	}
}
</script>