<template>
    <div class="mb-base-2">{{$t('Message')}}</div>
    <div class="relative bg-input-background-color text-input-text-color border border-input-border-color rounded-base-lg mb-base-2 pt-2 px-base-2 pb-8 dark:bg-slate-800 dark:text-white dark:border-white/10">
        <Mentionable v-model="share_content" ref="mention" :rows="4" className="max-h-28"/>     
        <div class="absolute bottom-2 start-2 leading-none">
            <EmojiPicker @emoji_click="addEmoji"/>
        </div>   
    </div>
    <div v-if="type === 'posts'" class="bg-white border border-divider rounded-none md:rounded-base-lg mb-base-2 dark:bg-slate-800 dark:border-white/10">
        <div v-if="subject.parent" class="feed-entry-wrap">
            <PostContent :post="subject.parent" :showCommentAction="false" :showMenuAction="false"/>
        </div>
        <div v-else class="feed-entry-wrap">
            <PostContent :post="subject" :showCommentAction="false" :showMenuAction="false"/>
        </div>
    </div>
    <div class="text-end">
        <BaseButton @click="shareToProfile()" :loading="loading">{{$t('Share')}}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import EmojiPicker from "@/components/utilities/EmojiPicker.vue";
import BaseButton from '@/components/inputs/BaseButton.vue';
import Mentionable from "@/components/utilities/Mentionable.vue";
import PostContent from '@/components/posts/PostContent.vue';
import Constant from '@/utility/constant'
import { usePostStore } from '../../store/post';

export default {
    components: { EmojiPicker, BaseButton, PostContent, Mentionable },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            loading: false,
            type: this.dialogRef.data.type,
            subject: this.dialogRef.data.subject,
            share_content: ''
        }
    },
    mounted(){
		this.$refs.mention.addContent('') // Focus Textarea when open
	},
    methods:{
        ...mapActions(usePostStore, ['postNewFeed']),
        addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
        async shareToProfile(){
            this.loading = true
            try {
                let sharePayload
                if(this.type === 'posts'){
                    sharePayload = {
                        type: 'share',
                        content: this.share_content,
                        parent_id: this.subject.parent ? this.subject.parent.id: this.subject.id
                    }
                } else {
                    sharePayload = {
                        type: 'share_item',
                        subject_type: this.type,
                        subject_id: this.subject.id,
                        content: this.share_content
                    }
                }
                await this.postNewFeed(sharePayload)
                this.dialogRef.close()
                this.showSuccess(this.$t('Shared Successfully.'))
                this.loading = false
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
                this.loading = false
            }
        }
    }
}
</script>