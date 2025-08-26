<template>
    <div class="flex flex-col justify-center relative group" ref="videoContainer" :id="`videoContainer-${videoId}`">
        <div class="absolute inset-0 overflow-hidden rounded-none md:rounded-md">
            <div class="w-full h-full bg-cover bg-center bg-no-repeat blur-3xl scale-150" :style="{ backgroundImage: `url(${video.thumb.url})`}"></div>
        </div>
        <div class="flex flex-col justify-center overflow-hidden w-full h-full z-10 md:rounded-md">
            <video
                :src="video.file?.url"
                :muted="volume === 0"
                :controls="false"
                :loop="loop"
                :preload="preload"
                :id="`videoMain-${videoId}`"
                :autoplay="user.video_auto_play && autoPlay && !isContentWarning"
                ref="player"
                playsinline
                class="max-h-full"           
            />
        </div>
        <div class="h-28 bg-story-detail-linear absolute top-0 left-0 right-0 rounded-none md:rounded-t-md z-10"></div>
        <div class="absolute flex justify-between items-center transition-opacity opacity-0 group-hover:opacity-100 z-30" :style="{ left: actionOffsetX + 'px', top: actionOffsetY + 'px' }">
            <div class="flex gap-base-2">
                <button v-if="allowTogglePlay" :class="vibbActionClass" @click.stop="togglePlay">
                    <BaseIcon :name="playIcon" />
                </button>
                <button v-if="allowFullScreen" :class="vibbActionClass" @click.stop="toggleFullScreen">
                    <BaseIcon :name="fullScreenIcon" />
                </button>
                <button class="volume-controls" :class="vibbActionClass" @click.stop="toggleMute">
                    <BaseIcon :name="volumnIcon" />
                    <div class="volume-controls-slider" :class="volumeOrientation">
                        <Slider v-model="volume" :orientation="volumeOrientation" :step="0.01" :min="0" :max="1" @change="updateVolume" ref="volume" />
                    </div>
                </button>
            </div>
        </div>
        <div v-if="allowTogglePlay" class="absolute inset-0 z-20 cursor-pointe invisible group-hover:visible" @click="togglePlay"></div>
        <div v-if="showProgressBar" class="absolute bottom-0 group/progress h-1 w-full bg-gray-300 z-30 transition-opacity opacity-0 hover:opacity-100">
            <div class="video-short-progress absolute inset-0 h-full w-full transition duration-500 origin-left bg-primary-color dark:bg-dark-primary-color" :style="{ transform: `scaleX(${percentagePlayed.toFixed(1) / 100})` }"></div>
            <div class="absolute start-0 -bottom-1 end-0 video-progress flex rtl:flex-row-reverse">
                <input
                    ref="seekRange"
                    type="range"
                    min="0"
                    max="100"
                    step="1"
                    :value="percentagePlayed.toFixed(1)"
                    @input="onSeek"
                    :style="{ direction: 'ltr' }"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useVideoStore } from '@/store/video'
import { uuidv4, checkiOSWeb } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Slider from 'primevue/slider'

export default {
    props: {
        video: {
            type: Object,
            default: null
        },
        autoPlay:{
            type: Boolean,
            default: false
        },
        preload: {
            type: Boolean,
            default: false
        },
        loop: {
            type: Boolean,
            default: true
        },
        allowFullScreen: {
            type: Boolean,
            default: true
        },
        allowPip: {
            type: Boolean,
            default: true
        },
        allowTogglePlay: {
            type: Boolean,
            default: true
        },
        showProgressBar: {
            type: Boolean,
            default: true
        },
        isContentWarning: {
            type: Boolean,
            default: false
        },
        actionOffsetX: {
			type: Number,
			default: 12
		},
		actionOffsetY: {
			type: Number,
			default: 12
		},
        volumeOrientation: {
            type: String,
            default: 'horizontal'
        }
    },
    components: { BaseIcon, Slider },
    data() {
        return {
            isPlaying: false,
            duration: 0,
            percentagePlayed: 0,
            time: 0,
            volume: 0,
            fullscreen: false,
            videoKey: uuidv4(),
            pipActive: false,
            observer: null
        };
    },
    computed: {
        ...mapState(useAppStore, ['openedModalCount']),
        ...mapState(useAuthStore, ['user']),
        ...mapState(useVideoStore, ['muteVideo', 'volumeValue', 'volumeCurrentValue', 'currentlyPlaying', 'currentPipVideo']),
        volumnIcon(){
            if (this.volume > 0.66) {
                return 'speaker_high'
            } else if (this.volume > 0) {
                return 'speaker_low'
            } else {
               return 'speaker_none'
            }
        },
        playIcon(){
            return this.isPlaying ? 'pause' : 'play';
        },
        fullScreenIcon(){
            return this.fullscreen ? 'fullscreen_exit' : 'fullscreen';
        },
        vibbActionClass(){
            return 'flex justify-center items-center p-3 bg-black-trans-6 text-white rounded-full'
        },
        videoId(){
            return this.video.file.id + '_' + this.videoKey
        }
	},
    mounted() {
        this.bindEvents();
        this.volume = this.volumeCurrentValue ? this.volumeCurrentValue : this.volumeValue
        this.$refs.player.volume = this.volume;

        document.addEventListener('visibilitychange', this.handleVisibilityChange);

        this.handlePlayVisibleItem(this.preload)
    },
    unmounted() {
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
    },
    watch: {
        volumeValue(){
            this.volume = this.volumeValue
        },
        volumeCurrentValue(){
            this.volume = this.volumeCurrentValue
        },
        volume(){
            this.$refs.player.volume = this.volume;
        },
        openedModalCount(newValue){
            if(newValue > 0){
                this.$refs.player?.pause();
            } else {
                this.handlePlayVisibleItem()
            }
        },
        time(){
            this.setCurrentPlayedTime(this.time)
        },
        isContentWarning(newVal){
            if(newVal){
                this.pause()
            } else {
                if (this.user.video_auto_play && this.autoPlay) {
                    this.play();
                }
            }
        },
        currentlyPlaying(newVal){
            if (newVal !== this.videoId) {
                this.pause();
            }
        }
    },
    methods: {
        ...mapActions(useVideoStore, ['setMuteVideo', 'setVolumeValue', 'setVolumeCurrentValue', 'setCurrentlyPlaying', 'setCurrentPlayedTime', 'setCurrentPipVideo']),
        bindEvents() {
            const player = this.$refs.player;
            const playerContainer = this.$refs.videoContainer;
            player.addEventListener("loadeddata", this.onLoadedData);
            player.addEventListener("timeupdate", this.onTimeUpdate);
            player.addEventListener("play", this.onPlay);
            player.addEventListener("pause", this.onPause);
            player.addEventListener("ended", this.onEnded);
            if(this.allowFullScreen){
                playerContainer.addEventListener('fullscreenchange', this.updateFullscreenButton);
                playerContainer.addEventListener('webkitfullscreenchange', this.updateFullscreenButton);
            }
            if(this.allowPip){
                player.addEventListener("enterpictureinpicture", this.onEnterPiP);
                player.addEventListener("leavepictureinpicture", this.onLeavePiP);
            }
        },
        onLoadedData() {
            this.duration = this.$refs.player?.duration;
        },
        onTimeUpdate() {
            this.percentagePlayed = (this.$refs.player?.currentTime / this.duration) * 100;
            this.time = this.$refs.player?.currentTime;
        },
        onPlay() {
            this.isPlaying = true;
        },
        onPause() {
            this.isPlaying = false;
        },
        onEnded() {
            this.isPlaying = false;
        },
        play(){
            if(!this.currentPipVideo){
                this.setCurrentlyPlaying(this.videoId);
                this.$refs.player?.play();
                if ((this.currentlyPlaying && this.currentlyPlaying !== this.videoId) || this.isContentWarning) {
                    this.pause();
                }
            }
        },
        pause(){
            this.$refs.player?.pause();
        },
        togglePlay() {
            if(!this.allowTogglePlay){
                return
            }
            if (this.isPlaying) {
                this.pause()
            } else {
                this.play()
            }
        },
        toggleMute() {
            this.setMuteVideo(!this.muteVideo)
            if (this.muteVideo) {
                this.setVolumeValue(0)
            }else if(this.volumeCurrentValue){
                this.setVolumeValue(this.volumeCurrentValue)
            }else{
				this.setVolumeValue(1)
			}
        },
        onSeek(e) {
            const percentage = e.target.value;
            this.$refs.player.currentTime = (percentage / 100) * this.duration;
        },
        convertTimeToDuration(seconds) {
            return [
                Math.floor(seconds / 60)
                    .toString()
                    .padStart(2, "0"),
                Math.floor(seconds % 60)
                    .toString()
                    .padStart(2, "0"),
            ].join(":");
        },
        updateVolume() {
            if (this.$refs.player.muted) {
                this.$refs.player.muted = false;
            }
			this.setVolumeCurrentValue(this.volume);
            if(this.$refs.player.volume === 0){
                this.setMuteVideo(true)
            } else  {
                this.setMuteVideo(false)
            }
        },
        toggleFullScreen() {
            if(this.allowFullScreen){
                if (document.fullscreenElement || document.webkitIsFullScreen) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen()
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen()
                    }
                } else {
                    const videoContainer = this.$refs.videoContainer
                    const player = this.$refs.player

                    if(checkiOSWeb()){
                        player.webkitEnterFullscreen()
                    } else {
                        if (videoContainer.requestFullscreen) {
                            videoContainer.requestFullscreen();
                        } else if (videoContainer.webkitRequestFullscreen) {
                            videoContainer.webkitRequestFullscreen();
                        } else if (videoContainer.msRequestFullscreen) {
                            videoContainer.msRequestFullscreen();
                        }
                    }
                }
                if(this.user.video_auto_play){
                    setTimeout(() => {
                        this.play()
                    }, 100);
                }
            }
        },
        updateFullscreenButton() {
            if(this.allowFullScreen){
                if (document.fullscreenElement || document.webkitIsFullScreen) {
                    this.fullscreen = true
                } else {
                    this.fullscreen = false
                }
            }
        },
        async togglePiP(){
            if(this.allowPip){
                if (!document.pictureInPictureElement) {
                    try {
                        await this.$refs.player.requestPictureInPicture();
                    } catch (error) {
                        console.error("Error enabling PiP mode:", error);
                    }
                } else {
                    await document.exitPictureInPicture();
                }
            }
        },
        onEnterPiP() {
            if(this.allowPip) {
                this.pipActive = true;
                this.setCurrentPipVideo(this.videoId);
            }
        },
        onLeavePiP() {
            if(this.allowPip) {
                this.pipActive = false;
                this.setCurrentPipVideo(null);
            }
        },
        handlePlayVisibleItem(preload = false){
            const _self = this;
            if (this.observer) {
                this.observer.disconnect();
            }
            this.observer = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting) {
                    _self.$emit('in-viewport');
                    if (_self.user.video_auto_play && _self.autoPlay && !_self.isContentWarning) {
                        _self.play();
                        if (preload) {
                            _self.$refs.player.currentTime = 0;
                        }
                    } else {
                        _self.pause();
                    }
                } else {
                    if (_self.currentPipVideo !== _self.videoId) {
                        _self.pause();
                    }
                }
            }, { threshold: [0.8] });

            const videoContainer = document.getElementById(`videoContainer-${this.videoId}`);
            if (videoContainer) {
                this.observer.observe(videoContainer);
            }
        },
        handleVisibilityChange() {
            if (document.hidden) {
                this.pause();
            } else {
                this.handlePlayVisibleItem();
            }
        }
    },
    emits: ['in-viewport']
};
</script>
