<template>
    <div>
        <div class="main-content-section bg-white p-5 md:p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800">
            <div class="flex gap-5 flex-col sm:flex-row">
                <div class="create-photo-block rounded-base-lg flex-1 flex flex-col justify-center items-center min-h-40 sm:min-h-96 p-4 cursor-pointer" @click="$refs.story_photo.click()">
                    <div class="flex justify-center items-center bg-white h-12 w-12 rounded-full dark:bg-slate-800 mb-2">
                        <BaseIcon name="photo"/>
                    </div>
                    <div class="font-semibold text-sm text-white">{{ $t('Create a photo story') }}</div>                 
                    <input type="file" ref="story_photo" @change="showPhotoStoryModal($event)" accept="image/*" class="hidden">
                </div>
                <div class="create-text-block rounded-base-lg flex-1 flex flex-col justify-center items-center min-h-40 sm:min-h-96 p-4 cursor-pointer" @click="showTextStoryModal">              
                    <div class="flex justify-center items-center bg-white h-12 w-12 rounded-full dark:bg-slate-800 mb-2">
                        <BaseIcon name="character"/>                  
                    </div>
                    <div class="font-semibold text-sm text-white">{{ $t('Create a text story') }}</div>  
                </div>          
            </div>
        </div>
    </div>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index'
import Photo from './Photo.vue'
import Text from './Text.vue'
import BaseIcon from '../../components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    methods: {
        showPhotoStoryModal(event){      
            var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					this.$dialog.open(Photo, {
						data: {
							imageData: e.target.result
						},
						props:{
							class: 'p-dialog-story p-dialog-no-header-title',
                            modal: true,
                            showHeader: false,
                            draggable: false
						},
						onClose: () => {					
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);
			}
        },
        showTextStoryModal(){
            this.$dialog.open(Text, {
                props:{
                    class: 'p-dialog-story p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        }
    }
}
</script>