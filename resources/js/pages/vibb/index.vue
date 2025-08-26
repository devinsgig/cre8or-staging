<template>
    <div class="bg-black fixed inset-0 z-[999] px-4 pt-16 pb-6">
        <div class="absolute top-3 start-4 end-4 flex flex-wrap items-center justify-between gap-2 z-[1001]">
            <div class="flex-1">
                <button @click="handleBackToHome" class="vibb-action-button flex items-center justify-center bg-white shadow-md dark:bg-slate-800 dark:shadow-slate-600 w-10 h-10 rounded-full">
                    <BaseIcon name="caret_left" size="24"/>
                </button>
            </div>
            <TabsMenu :menus="vibbMenus" type="secondary" @select="changeTab" class="vibb-menu flex-3 flex justify-center text-white" />
            <div class="flex-1 text-end">
                <template v-if="show_upload_video">
                    <button v-if="screen.md" @click="handleCreateVibb" class="vibb-action-button flex items-center justify-center justify-self-end bg-white shadow-md dark:bg-slate-800 dark:shadow-slate-600 w-10 h-10 rounded-full ms-auto">
                        <BaseIcon name="plus_circle" size="24"/>
                    </button>
                    <BaseButton v-else @click="handleCreateVibb">{{ $t("Create new vibb") }}</BaseButton>
                </template>
            </div>
        </div>
        <div class="flex items-center justify-center h-full">
            <div ref="vibbContainer" class="flex items-center vibb-container w-full h-full scrollbar-hidden" 
            :class="{'fixed inset-0 z-10': screen.md}"
            :style="{ 'overflow-y': (showVibbComment && screen.md) ? 'hidden' : 'scroll' }"
            @scroll="onScroll">
                <template v-if="vibbId">
                    <VibbItem :item="vibbInfo" />
                </template>
                <VibbsList :key="currentTab" :loading="loadingVibbsList" :vibbs-list="filteredVibbs" @load-more="loadMoreVibbs" />
            </div>
            <VibbComment />
            <div class="hidden md:flex flex-col gap-4 items-center justify-center absolute top-1/2 -translate-y-1/2 end-4">
                <button class="vibb-action-button w-12 h-12 flex items-center justify-center bg-white shadow-md rounded-full relative hover:bg-hover dark:bg-slate-800 dark:text-white dark:shadow-slate-600 transition-all duration-300 ease-linear" :class="isAtTop ? 'opacity-0 top-[64px]' : 'opacity-100 top-0 z-10'" @click="handleScrollUp">
                    <BaseIcon name="arrow_up" />
                </button>
                <button class="vibb-action-button w-12 h-12 flex items-center justify-center bg-white shadow-md rounded-full relative hover:bg-hover dark:bg-slate-800 dark:text-white dark:shadow-slate-600 transition-all duration-300 ease-linear" :class="isAtBottom ? 'opacity-0 -top-[64px]' : 'opacity-100 top-0 z-10'" @click="handleScrollDown">
                    <BaseIcon name="arrow_down" />
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import { mapActions, mapState } from "pinia";
import { useAuthStore } from "@/store/auth";
import { usePostStore } from "@/store/post";
import { useAppStore } from "@/store/app";
import { useVibbStore } from "@/store/vibb";
import TabsMenu from '@/components/menu/TabsMenu.vue';
import VibbItem from '@/components/vibb/VibbItem.vue'
import VibbsList from "@/components/vibb/VibbsList.vue";
import BaseButton from "@/components/inputs/BaseButton.vue";
import BaseIcon from '@/components/icons/BaseIcon.vue'
import VibbComment from "@/components/vibb/VibbComment.vue";

export default {
    props: ['tab'],
    components: { TabsMenu, VibbItem, VibbsList, BaseButton, BaseIcon, VibbComment },
    data() {
        return {
            currentTab: this.tab ? this.tab : '',
            vibbId: this.$route.query.id ? this.$route.query.id : 0,
            currentPage: 1,
            isAtTop: true,
            isAtBottom: true,
            show_upload_video: false,
            loadingVibbDetail: true
        };
    },
    computed: {
        ...mapState(useAuthStore, ["user", "authenticated"]),
        ...mapState(usePostStore, ["loadingVibbsList", "vibbsList", "vibbInfo", "deletedPost"]),
        ...mapState(useAppStore, ['screen', 'config']),
        ...mapState(useVibbStore, ['showVibbComment']),
        vibbMenus(){
			return [
				{ name: this.$t('For you'), isShow: true, isActive: this.currentTab === '', tab: ''},
				{ name: this.$t('Following'), isShow: true, isActive: this.currentTab === 'following', tab: 'following'}
			]
		},
        filteredVibbs(){
            return this.vibbsList.filter(vibb => vibb.id != this.vibbId);
        }
    },
    mounted() {
        this.handleGetVibbs()
        if(this.vibbId){
            this.loadVibbInfo(this.vibbId)
        }
        this.show_upload_video = this.config.ffmegEnable;
    },
    unmounted() {
        this.unsetVibbsList();
        this.unsetVibbInfo();
    },
    watch:{
        vibbsList(){
            this.isAtBottom = this.filteredVibbs.length > 0 ? false : true
        },
        '$route'() {
            this.vibbId = this.$route.query.id ? this.$route.query.id : 0
        },
        deletedPost(){
            this.updateButtonState()
            if(this.isAtBottom){
                this.handleScrollUp()
            } else {
                this.handleScrollDown()
            }
        }
    },
    methods: {
        ...mapActions(usePostStore, ["getVibbsForYouList", "getFollowingVibbsList", "getVibbById", "unsetVibbsList", "unsetVibbInfo"]),
        onScroll(event) {
            this.updateButtonState();
            event.preventDefault();
        },
        updateButtonState() {
            const vibbContainer = this.$refs.vibbContainer;
            this.isAtTop = vibbContainer.scrollTop === 0
            this.isAtBottom = vibbContainer.scrollHeight - vibbContainer.scrollTop === vibbContainer.clientHeight
        },
        async loadVibbInfo(vibbId){
            try {
                await this.getVibbById(vibbId);
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingVibbDetail = false
            }
        },
        handleGetVibbs(){
            if(this.currentTab === 'following'){
                this.getFollowingVibbsList(this.currentPage);
            } else {
                this.getVibbsForYouList(this.currentPage);
            }
        },
        loadMoreVibbs($state) {
            if(this.currentTab === 'following'){
                this.getFollowingVibbsList(++this.currentPage).then((response) => {
                    if (response.length === 0) {
                        $state.complete();
                        this.isAtBottom = true
                    } else {
                        $state.loaded();
                    }
                });
            } else {
                this.getVibbsForYouList(++this.currentPage).then((response) => {
                    if (response.length === 0) {
                        $state.complete();
                        this.isAtBottom = true
                    } else {
                        $state.loaded();
                    }
                });
            }
        },
        handleCreateVibb() {
            if(!this.authenticated){
                this.$router.push({ 'name': 'login' })
                return
            }
            if (this.user) {
				let permission = 'vibb.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'vibb_create' })
                }
			}
        },
        handleScrollUp() {
            this.$refs.vibbContainer.scrollBy({ top: -100 });
        },
        handleScrollDown() {
            this.$refs.vibbContainer.scrollBy({ top: 100 });
        },
        changeTab(name) {
			this.currentTab = name
            this.currentPage = 1
            this.vibbId = 0 
            this.unsetVibbInfo()
            this.handleGetVibbs()
		},
        handleBackToHome(){
            this.$router.push({ name: 'home' })
        }
    },
};
</script>