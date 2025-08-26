<template>
    <div v-if="story" class="h-full w-full select-none">
        <div class="h-full w-full relative">
            <div class="absolute top-4 left-2 right-2 lg:left-4 lg:right-4 flex gap-1 z-10">
                <div v-for="item in story.items" :key="item.id" class="flex-1 bg-gray-400 rounded-full story-remaining-bar">
                    <div v-if="storyItem.id > item.id" class="rounded-full h-1 w-full bg-white story-progress-bar"></div>
                    <div v-if="storyItem.id == item.id" class="rounded-full h-1 w-full bg-progress story-progress-bar" :style="{'width': (time * 100 / config.story.timeout) +'%'}"></div>
                </div>
            </div>
            <StoryItemDetail :canMessage="story.canMessage" :story="storyItem" :owner="story.user" :runningStory="run" :isPage="isPage" @play_story="doPlayStory" @stop_story="stopPlayStory" @close_story_modal="$emit('closeModal')" @share_story_modal_open="handleShareStoryModal" />
        </div>
        <button v-if="showPrev" @click="prevStories" class="absolute top-1/2 -translate-y-1/2 ltr:left-2 ltr:lg:-left-20 rtl:right-2 rtl:lg:-right-20 z-10 flex items-center justify-center w-10 h-10 bg-white rounded-full shadow-md text-main-color dark:bg-dark-web-wash dark:text-white"><BaseIcon :name="user.rtl ? 'caret_right' : 'caret_left'"/></button>
        <button v-if="showNext" @click="nextStories" class="absolute top-1/2 -translate-y-1/2 ltr:right-2 ltr:lg:-right-20 rtl:left-2 rtl:lg:-left-20 z-10 flex items-center justify-center w-10 h-10 bg-white rounded-full shadow-md text-main-color dark:bg-dark-web-wash dark:text-white"><BaseIcon :name="user.rtl ? 'caret_left' : 'caret_right'"/></button>
    </div>
	<Loading v-else class="mx-auto"/>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getStoryDetail, storyViewItem } from '@/api/stories'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ViewersModal from '@/components/stories/ViewersModal.vue'
import StoryItemDetail from '@/components/stories/StoryItemDetail.vue'
import { useAppStore } from '../../store/app'
import { useStoriesStore } from '../../store/stories'
import { useAuthStore } from '../../store/auth'

export default {
	components: { Loading, BaseIcon, StoryItemDetail },
    props: {
        storyId: {
            default: ''
        },
        storiesList: {
            default: []
        },
        isPage: {
            default: false
        }
    },
    data() {    
        return {
            id: this.storyId,
			story: null,
            storyItem: null,
            showPrev: false,
            showNext: false,
            interval: null,
            time: 0,
            run: false,
            shareModalIsOpen: false
		}
	},
    watch: {
        storyItem(newStoryItem) {
            if (newStoryItem) {
                if (this.authenticated) {
                    storyViewItem({
                        'id' : newStoryItem.id
                    })
                    var index = window._.findIndex(this.story.items, function(item) { 
                        return item.id == newStoryItem.id; 
                    });
                    var seen = (index == (this.story.items.length - 1))
                    this.setSeenStoryItem({'storyItem' : seen ? this.story.items[0] : this.story.items[index + 1], 'seen': seen})
                }
                this.time = 0;
                this.doPlayStory()
            }
        },
        time(newTime) {
            if (parseInt(newTime) == this.config.story.timeout) {
                clearInterval(this.interval)
                this.interval = null
                this.run = false
                if (this.showNext) {
                    this.nextStories()
                } else {
                    if (! this.isPage) {
                        this.$emit('closeModal');
                        this.storyItem = null
                    }
                }
            }
        },
        deleteStoryItem(){
            if(this.story.items && window._.find(this.story.items, {id: this.deleteStoryItem.id})){
                this.story.items = this.story.items.filter(story => story.id !== this.deleteStoryItem.id)
                if (this.showPrev) {
                    this.prevStories()
                }
                if (this.showNext) {
                    this.nextStories()
                }              
            }
            if(this.storyItem && this.storyItem.id == this.deleteStoryItem.id){
                this.$emit('closeModal');
                this.storyItem = null
            }
        },
        shareModalIsOpen(newValue){
            if(newValue){
                window.removeEventListener('keydown', this.onKeyDown)
            } else {
                window.addEventListener('keydown', this.onKeyDown)
            }
        }
    },
	mounted() {
        if(this.id){
            this.loadStoryDetail(this.id)
            window.addEventListener('keydown', this.onKeyDown)
        }
    },
    unmounted() {
        window.removeEventListener('keydown', this.onKeyDown)
    },
    computed: {
		...mapState(useAppStore, ['config']),
        ...mapState(useStoriesStore, ['deleteStoryItem']),
        ...mapState(useAuthStore, ['user', 'authenticated'])
	},
    methods: {        
        ...mapActions(useStoriesStore, ['setSeenStoryItem']),
        async loadStoryDetail(storyId){
            try {             
                this.story = await getStoryDetail(storyId);
                var self = this;
                this.storyItem = window._.find(this.story.items, function(item) {
                    return item.id == self.story.item_view_id;
                });
				this.checkShowButton();
            } catch (error) {
                this.$emit('setError')
                if (! this.isPage) {
                    this.showError(error.error)
                }
            }
        },
        prevStories(){
            var self = this;
            var item = window._.findLast(this.story.items, function(item) { 
                return item.id < self.storyItem.id; 
            });

            if (item) {
                this.storyItem = item;
                this.checkShowButton()
            } else {
                var index = this.getIndexStory();
                var story = this.storiesList[index - 1];

                this.id = story.id;
                this.loadStoryDetail(this.id);

                let storyUrl = this.$router.resolve({
                    name: 'story_view',
                    params: { 'storyId': this.id }
                });
                changeUrl(storyUrl.fullPath)
            }
        },
        nextStories(){
            var self = this;
            var item = window._.find(this.story.items, function(item) { 
                return item.id > self.storyItem.id; 
            });

            if (item) {
                this.storyItem = item;
                this.checkShowButton()
            } else {
                var index = this.getIndexStory();
                var story = this.storiesList[index + 1];
                
                this.id = story.id;
                this.loadStoryDetail(this.id);

                let storyUrl = this.$router.resolve({
                    name: 'story_view',
                    params: { 'storyId': this.id }
                });
                changeUrl(storyUrl.fullPath)
            }
        },
        checkShowButton(){
            var self = this;

            var index = window._.findIndex(this.story.items, function(item) { 
                return item.id == self.storyItem.id; 
            });
            
            if (index == 0 && this.story.items.length == 1) {
                this.checkPrev()
                this.checkNext()
            } else {
                if (index == 0) {
                    this.checkPrev()
                    this.showNext = true;
                } else if (index == this.story.items.length - 1)  {
                    this.checkNext()
                    this.showPrev = true;
                } else {
                    this.showPrev = true;
                    this.showNext = true;
                }
            }
        },
        checkPrev()
        {
            var index = this.getIndexStory()
            if (index < 1) {
                this.showPrev = false;
            } else {
                this.showPrev = true;
            }
        },
        checkNext()
        {
            var index = this.getIndexStory()
            if (this.storiesList.length > 1 && index < this.storiesList.length - 1) {                
                this.showNext = true;
            } else {
                this.showNext = false;
            }
        },
        getIndexStory()
        {
            var self = this
            return window._.findIndex(this.storiesList, function(story) { 
                return story.id == self.id; 
            });
        },
        doPlayStory(){
            if (parseInt(this.time) >= this.config.story.timeout ) {
                this.time = 0                
                this.storyItem = this.story.items[0]
                this.checkShowButton()
            }
            this.run = true
            if (this.interval != null) {
                clearInterval(this.interval)
            }
            this.interval = setInterval(() => {
                this.time+=0.01
            }, 10)
        },
        stopPlayStory(){
            clearInterval(this.interval)
            this.interval = null
            this.run = false
        },
        openViewersModal(id){
            this.$dialog.open(ViewersModal, {
                data: {
                    itemId: id
                },
                props:{
                    header: this.$t('Viewers'),
                    class: 'likers-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.doPlayStory()
                }
            });
            this.stopPlayStory()
        },
        onKeyDown (e){
			switch(e.key){
                case 'ArrowLeft':
                    if(this.user.rtl){
                        this.showNext && this.nextStories()
                    }else{
                        this.showPrev && this.prevStories()
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    break;
                case 'ArrowRight':
                    if(this.user.rtl){
                        this.showPrev && this.prevStories()
                    }else{
                        this.showNext && this.nextStories()
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    break;
                default:
                    break;
            }
		},
        handleShareStoryModal(status){
            this.shareModalIsOpen = status
        }
    }
}
</script>