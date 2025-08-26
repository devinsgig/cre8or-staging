<template>
    <div class="main-content-section bg-white rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <div class="flex flex-wrap items-center justify-between gap-1 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Request Identity Verification')}}</h3>
        </div>
        <p class="mb-base-2">{{$t("The Verified badge lets people know that an account is authentic. To receive the verified badge, please submit the verification documents such as a driver's license, ID card, passport... if you're a business please submit company registration number. The information in the document must be matched to the information on your account. Information shared will only be used for verification purpose and will auto delete after the request is processed.")}}</p>
        <div class="mb-base-2">
            <h4 class="text-base font-bold mb-base-2">{{ $t('How to take the photos') }}</h4>
            <ul class="list-disc ps-5">
                <li>{{ $t('Take the photos in a room with enough light') }}</li>
                <li>{{ $t('Select the highest quality setting on your device') }}</li>
                <li>{{ $t("Have your driver's license, ID card or passport on hand") }}</li>
                <li>{{ $t('Make sure that your whole document is in the frame, as well as your face - nothing can be censored') }}</li>
            </ul>
        </div>
        <div>
            <BaseInputFile ref="baseInputFile" @upload-file="uploadVerificationDocuments" multiple accept="*/*" class="mb-2">{{$t('Upload Verification Documents')}}</BaseInputFile>
            <div v-if="myVerificationDocuments.length > 0">
                <div v-for="(myVerificationDocument, index) in myVerificationDocuments" :key="index">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <a :href="myVerificationDocument.file_url" target="_blank">{{myVerificationDocument.name}}</a>
                    </div>
                </div>
            </div>
            <div v-if="uploadedVerificationDocuments.length > 0">
                <div v-for="(uploadedVerificationDocument, index) in uploadedVerificationDocuments" :key="index">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <a :href="uploadedVerificationDocument.file_url" target="_blank">{{uploadedVerificationDocument.name}}</a>
                        <button @click="removeDocument(uploadedVerificationDocument.id)">
                            <BaseIcon name="close" class="text-base-red" size="20" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-base-2">
            <BaseButton @click="storeDocuments()">{{$t('Submit')}}</BaseButton>
            <BaseButton type="outlined" :to="{name: 'home'}">{{$t('Cancel')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { getVerificationDocuments, uploadVerificationDocuments, deleteVerificationDocuments, storeVerificationDocuments } from '@/api/verify'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseInputFile from '@/components/inputs/BaseInputFile.vue'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';

export default {
    components: { BaseButton, BaseIcon, BaseInputFile },
    data() {
        return {
            myVerificationDocuments: [],
            uploadedVerificationDocuments: []
        }
    },
    mounted() {
        if(this.config.userVerifyEnable && !this.user.is_verify){
            this.getMyVerificationDocuments()
        }else{
            this.$router.push({ name: 'home' })
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        async getMyVerificationDocuments(){
            try {
                const response = await getVerificationDocuments()
                this.myVerificationDocuments = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async uploadVerificationDocuments(files){
			this.startUploadImages(files.target.files)
		},
        async startUploadImages(uploadedFiles, clipboard){
			if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i], 'user_verify')
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
					try {
						const response = await uploadVerificationDocuments(formData);
						this.uploadedVerificationDocuments.push(response);
					} catch (error) {
						this.showError(error.error)
					}	
				}
			}			
		},
        async removeDocument(documentId){
			try {
				await deleteVerificationDocuments({
					id: documentId
				});
				this.uploadedVerificationDocuments = this.uploadedVerificationDocuments.filter(document => document.id !== documentId);
			} catch (error) {
				this.showError(error.error)
			}
		},
        async storeDocuments(){
            try {
                await storeVerificationDocuments({
                    files: this.uploadedVerificationDocuments.map(x => x.id)
                })
                this.myVerificationDocuments = [...this.myVerificationDocuments, ...this.uploadedVerificationDocuments]
                this.uploadedVerificationDocuments = []
                this.showSuccess(this.$t('Your request has been submitted.'))
                this.$refs.baseInputFile.clearSelectedFile()
            } catch (error) {
                this.showError(error.error)
            }
        }
    },
}
</script>