<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button v-if="story.canReport" :class="importantActionClass" @click="reportStory(story.id)">{{$t('Report')}}</button>
        <button v-if="story.canDelete" :class="importantActionClass" @click="deleteStory(story.id)">{{$t('Delete Story')}}</button>
        <button :class="normalActionClass" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import { useStoriesStore } from '@/store/stories';
import ReportModal from '@/components/modals/ReportModal.vue';

export default {
    data(){
        return{
            story: this.dialogRef.data.story
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        ...mapActions(useStoriesStore, ['doDeleteStoryItem']),
        reportStory(storyItemId){
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'story_items',
                    id: storyItemId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'post-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                    this.dialogRef.close();
                }
            }), 300);
        },
        deleteStory(storyItemId){
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this story?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.doDeleteStoryItem({
                        id: storyItemId,
                        storyId: this.story.story_id
                    });
                    checkPopupBodyClass()
                    this.dialogRef.close();
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);         
        },
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>