<template>	
	<div v-if="authenticated" class="widget-box bg-white border border-white rounded-none md:rounded-base-lg p-4 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
		<div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
			<h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
		</div>
		<template v-if="storiesList.length > 0" >
			<SlimScroll>
				<div @click="createStory()" class="w-32 flex-32 cursor-pointer">
					<div class="create-story-box border border-divider rounded-base-lg relative pb-story-ratio bg-cover bg-center dark:bg-slate-800 dark:border-white/10">
						<div class="absolute left-0 top-0 right-0 bottom-16 border-b border-divider dark:border-white/10">
							<div class="h-full">
								<img class="h-full rounded-t-base-lg object-cover" :src="user.avatar" :alt="user.user_name">
							</div>
							<div class="create-story-btn relative flex items-center justify-center bg-primary-color rounded-full -mt-5 w-10 h-10 mx-auto border-3 border-white z-10 dark:bg-dark-primary-color dark:border-slate-800"><BaseIcon name="plus" class="text-white"/></div>
						</div>
						<div class="create-story-box-bottom absolute bottom-3 left-1 right-1 text-center font-semibold">{{$t('Create story')}}</div>
					</div>		
				</div>
				<div class="stories-list-scroll w-32 flex-32" v-for="story in storiesList" :key="story.id" @click="showStoryDetail(story.id)">
					<div class="rounded-base-lg relative pb-story-ratio cursor-pointer text-sm overflow-hidden">
						<div class="absolute left-3 top-2 z-10">
							<Avatar :user="story.user" :border="false" class="border-3 rounded-full" :class="story.seen ? 'border-white' : 'border-color-link dark:border-dark-primary-color'"/>
						</div>
						<StoryContentPreview v-if="story.item" :story="story.item" class="pointer-events-none"/>
						<UserName :user="story.user" :router="false" :activePopover="false" class="stories-list-scroll-name absolute left-3 bottom-3 right-3 text-white" />
					</div>
				</div>
				<InfiniteLoading @infinite="loadMoreStories">
					<template #spinner><span></span></template>
					<template #complete><span></span></template>
				</InfiniteLoading>
			</SlimScroll>
		</template>
		<template v-else>
			<div @click="createStory()" class="w-32 flex-32 cursor-pointer">
				<div class="create-story-box border border-divider rounded-base-lg relative pb-story-ratio bg-cover bg-center dark:bg-slate-800 dark:border-white/10">
					<div class="absolute left-0 top-0 right-0 bottom-16 border-b border-divider dark:border-white/10">
						<div class="h-full">
							<img class="h-full rounded-t-base-lg object-cover" :src="user.avatar" :alt="user.user_name">
						</div>
						<div class="create-story-btn relative flex items-center justify-center bg-primary-color rounded-full -mt-5 w-10 h-10 mx-auto border-3 border-white z-10 dark:bg-dark-primary-color dark:border-slate-800"><BaseIcon name="plus" class="text-white"/></div>
					</div>
					<div class="create-story-box-bottom absolute bottom-3 left-1 right-1 text-center font-semibold">{{$t('Create story')}}</div>
				</div>		
			</div>				
		</template>
	</div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import { getStories, getStoryDetailInList } from '@/api/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import StoryDetailModal from '@/components/stories/StoryDetailModal.vue'
import StoryContentPreview from '@/components/stories/StoryContentPreview.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import { useAuthStore } from '../../../store/auth'
import { useStoriesStore } from '../../../store/stories'
import { useActionStore } from '../../../store/action'
import { useAppStore } from '../../../store/app'
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
	components: { BaseIcon, StoryContentPreview, InfiniteLoading, Avatar, UserName, SlimScroll },
	props: ['data', 'params', 'position'],
	data(){
		return {
			storiesList: [],
			currentPage: 1
		}
	},
	computed:{
        ...mapState(useAuthStore, ['user', 'authenticated'])	,
		...mapState(useStoriesStore, ['seenStoryItem', 'deleteStoryItem']),
		...mapState(useActionStore, ['samePage']),
		...mapState(useAppStore, ['setOpenedModalCount'])
    },
	mounted(){
		this.getStoriesList(this.currentPage)
	},
	watch: {
		seenStoryItem(seenStoryItemNew){
			var story = window._.find(this.storiesList, {id: seenStoryItemNew.storyItem.story_id})
			if (story) {
				if (! story.seen && story.item.id != seenStoryItemNew.storyItem.id) {
					story.item = seenStoryItemNew.storyItem
				}

				if (! story.seen && seenStoryItemNew.seen) {
					story.seen = true
				}
			}
		},
		async deleteStoryItem(storyItem){
			var index = window._.findIndex(this.storiesList, function(story) { 
                return story.id == storyItem.storyId; 
            });

			if (index == -1) {
				return
			}

			try {
				var story = await getStoryDetailInList(storyItem.storyId)
				this.storiesList[index] = story
			} catch (error) {				
				this.storiesList = this.storiesList.filter(story => story.id !== storyItem.storyId)
			}
		},
		samePage(){
			this.currentPage = 1
			this.storiesList = []
			this.getStoriesList(this.currentPage)
		}
	},
	methods: {
		async getStoriesList(page){
			if (! this.authenticated) {
				return
			}
			try {
				let pushedStoriessList = await getStories(page)
				if (page == 1) {
					this.storiesList = [];
				}			
				this.storiesList = window._.concat(this.storiesList, pushedStoriessList)
				return pushedStoriessList
			} catch (error) {
				this.showError(error.error)
			}
		},
		showStoryDetail(storyId){
			let storyUrl = this.$router.resolve({
                name: 'story_view',
                params: { 'storyId': storyId }
            });
            changeUrl(storyUrl.fullPath)
			this.setOpenedModalCount()
            this.$dialog.open(StoryDetailModal, {			
                data: {
                    id: storyId,
					storiesList: this.storiesList
                },
                props:{
					class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
					changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
					this.setOpenedModalCount(false)
                }
            });
        },
		loadMoreStories($state) {
			this.getStoriesList(++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		},
		createStory() {
			if (this.user) {
				let permission = 'story.allow_create'
				if(this.checkPermission(permission)){
					this.$router.push({ 'name': 'stories' })
				}
			}
		}
	}
}
</script>