<template>
    <div class="video-container flex flex-col justify-center group" ref="videoContainer" :id="`videoContainer-${videoId}`">
        <video
            :src="video.file?.url"
            :muted="volume === 0"
            :controls="false"
            :loop="loop"
            :poster="video.thumb?.url"
            :preload="preload"
            :id="`videoMain-${videoId}`"
            :autoplay="user.video_auto_play && autoPlay && !isContentWarning"
            ref="player"
            playsinline
        />
        <template v-if="!pipActive">
            <div class="absolute left-0 bottom-0 right-0 bg-black-gradient z-20 transition-opacity duration-300 opacity-100 group-hover:opacity-100">
                <div class="flex items-center justify-between gap-3 rtl:flex-row-reverse text-white p-base-2">
                    <div class="flex items-center gap-3 rtl:flex-row-reverse">
                        <button @click="togglePlay">
                            <BaseIcon :name="playIcon" />
                        </button>
                        <div class="text-xs whitespace-nowrap">
                            <div>{{ convertTimeToDuration(time) }} / {{ convertTimeToDuration(duration) }}</div>
                        </div>
                    </div>
                    <div class="video-progress flex rtl:flex-row-reverse">
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
                    <div class="flex items-center gap-3 rtl:flex-row-reverse">
                        <button v-if="allowFullScreen" @click="toggleFullScreen">
                            <BaseIcon :name="fullScreenIcon" />
                        </button>
                        <button v-if="allowPip" @click="togglePiP">
                            <BaseIcon name="pip" />
                        </button>
                        <button class="volume-controls flex items-center">
                            <BaseIcon :name="volumnIcon" @click="toggleMute" class="relative z-10"/>
                            <div class="volume-controls-slider" :class="volumeOrientation">
                                <Slider v-model="volume" :orientation="volumeOrientation" :step="0.01" :min="0" :max="1" @change="updateVolume" ref="volume" />
                            </div>
                        </button>
                    </div>
                </div>    
            </div>
            <div class="absolute inset-0 flex justify-center items-center w-full z-5 cursor-pointer z-10" @click="togglePlay">
                <div v-if="!isPlaying" class="flex justify-center items-center w-12 h-12 bg-black-trans-6 text-white rounded-full">
                    <BaseIcon name="play_fill" class="leading-none"/>
                </div>
            </div>
        </template>
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
            default: false
        },
        allowFullScreen: {
            type: Boolean,
            default: true
        },
        allowPip: {
            type: Boolean,
            default: true
        },
        isContentWarning: {
            type: Boolean,
            default: false
        },
        volumeOrientation: {
            type: String,
            default: 'vertical'
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
            return this.isPlaying ? 'pause' : 'play'
        },
        fullScreenIcon(){
            return this.fullscreen ? 'fullscreen_exit' : 'fullscreen';
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
        },
        openedModalCount(newValue){
            if(newValue > 0){
                this.$refs.player?.pause();
            } else {
                this.handlePlayVisibleItem()
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
                this.handlePlayVisibleItem()
            }
        }
    },
    emits: ['in-viewport']
};
</script>
