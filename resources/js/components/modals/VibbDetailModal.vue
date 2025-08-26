<template>
    <div class="absolute top-3 start-4 end-4 flex flex-wrap items-center justify-between gap-2 z-[1001]">
        <div class="flex-1">
            <button @click="closeModal" class="vibb-action-button flex items-center justify-center bg-white shadow-md dark:bg-slate-800 dark:shadow-slate-600 w-10 h-10 rounded-full">
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
    <div class="px-4 pt-16 pb-6 h-full">
        <div class="flex items-center justify-center h-full">
            <div ref="vibbContainer" class="flex items-center vibb-container w-full h-full scrollbar-hidden" 
            :class="{'fixed inset-0 z-10': screen.md}"
            :style="{ 'overflow-y': (showVibbComment && screen.md) ? 'hidden' : 'scroll' }"
            @scroll="onScroll">
                <VibbsList :loading="loadingVibbsList" :vibbs-list="filteredVibbs" @load-more="loadMoreVibbs" />
            </div>
            <VibbComment />
            <div class="hidden md:flex flex-col gap-4 items-center justify-center fixed top-0 end-4 bottom-0">
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
import VibbsList from "@/components/vibb/VibbsList.vue";
import BaseIcon from '@/components/icons/BaseIcon.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue';
import BaseButton from "@/components/inputs/BaseButton.vue";
import VibbComment from "@/components/vibb/VibbComment.vue";

export default {
    components: { VibbsList, BaseIcon, TabsMenu, BaseButton, VibbComment },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return {
            subjectId: this.dialogRef.data?.subjectId ? this.dialogRef.data?.subjectId : 0,
            subjectType: this.dialogRef.data?.subjectType ? this.dialogRef.data?.subjectType : '',
            vibb: this.dialogRef.data?.vibb ? this.dialogRef.data?.vibb : null,
            currentPage: 1,
            isAtTop: true,
            isAtBottom: false,
            show_upload_video: false
        };
    },
    computed: {
        ...mapState(useAuthStore, ["user", "authenticated"]),
        ...mapState(usePostStore, ["loadingVibbsList", "vibbsList"]),
        ...mapState(useAppStore, ['screen', 'config']),
        ...mapState(useVibbStore, ['showVibbComment']),
        filteredVibbs(){
            if (this.vibb) {
                const filteredList = this.vibbsList.filter(item => item.id !== this.vibb.id);
                return [this.vibb, ...filteredList];
            }
            return this.vibbsList;
        },
        vibbMenus(){
            return [
                { name: this.$t('For you'), isShow: true, isActive: this.subjectType === '', tab: ''},
                { name: this.$t('Following'), isShow: true, isActive: this.subjectType === 'following', tab: 'following'}
            ]
        }
    },
    mounted() {
        this.handleGetVibbs()
        this.show_upload_video = this.config.ffmegEnable;
    },
    unmounted() {
        this.unsetVibbsList();
    },
    watch:{
        vibbsList(){
            this.isAtBottom = this.filteredVibbs.length > 0 ? false : true
        },
        '$route'(){
            this.dialogRef.close()
        }
    },
    methods: {
        ...mapActions(usePostStore, ["getVibbsForYouList", "getFollowingVibbsList", "getUserVibbsModalList", "getMyVibbsModalList", "unsetVibbsList"]),
        onScroll(event) {
            this.updateButtonState();
            event.preventDefault();
        },
        updateButtonState() {
            const vibbContainer = this.$refs.vibbContainer;
            this.isAtTop = vibbContainer.scrollTop === 0
            this.isAtBottom = vibbContainer.scrollHeight - vibbContainer.scrollTop === vibbContainer.clientHeight
        },
        handleGetVibbs(){
            switch (this.subjectType) {
                case 'following':
                    this.getFollowingVibbsList(this.currentPage);
                    break;
                case 'my':
                    this.getMyVibbsModalList(this.currentPage);
                    break;
                case 'user':
                    this.getUserVibbsModalList(this.subjectId, this.currentPage);
                    break;
                default:
                    this.getVibbsForYouList(this.currentPage);
                    break;
            }
        },
        loadMoreVibbs($state) {
            switch (this.subjectType) {
                case 'following':
                    this.getFollowingVibbsList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                case 'my':
                    this.getMyVibbsModalList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                case 'user':
                    this.getUserVibbsModalList(this.subjectId, ++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                default:
                    this.getVibbsForYouList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
            }
        },
        handleScrollUp() {
            this.$refs.vibbContainer.scrollBy({ top: -100 });
        },
        handleScrollDown() {
            this.$refs.vibbContainer.scrollBy({ top: 100 });
        },
        closeModal() {
            this.dialogRef.close()
        },
        handleCreateVibb() {
            if(!this.authenticated){
                this.$router.push({ 'name': 'login' })
                return
            }
            if (this.user) {
				let permission = 'vibb.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'vibb_create', force: true })
                }
			}
        },
        changeTab(name) {
            this.subjectType = name
            this.currentPage = 1
            this.handleGetVibbs()
		},
    },
};
</script>