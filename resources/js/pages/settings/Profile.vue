<template>
	<template v-if="user.is_page">
		<div class="flex flex-wrap gap-x-5 mb-3"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Page Name')}}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="pageName" :error="error.name"/>
			</div>  
		</div>
		<div class="flex flex-wrap gap-x-5 mb-3"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Page Alias')}}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="pageUserName" :error="error.user_name"/>
			</div>  
		</div>
	</template>
	<template v-else>
		<Loading v-if="loadingStatus" />
		<div v-else>
			<div class="flex gap-x-2 md:gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end mb-1">
					<Avatar :user="user" :activePopover="false" :size="50" class="ms-auto" />
				</div>
				<div class="md:flex-2 flex flex-col items-start gap-1">
					<UserName :user="user" />
					<button class="text-xs font-bold text-primary-color dark:text-dark-primary-color" @click="$refs.avatar.click()">{{$t('Change profile photo')}}</button>
					<input type="file" ref="avatar" @change="uploadAvatar($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Display Name')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseInputText v-model="name" :error="error.name"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Bio')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseTextarea v-model="bio" :autoResize="true" :error="error.bio"/>
					<BaseSelectPrivacy v-model="privacyField['bio']" :options="privaciesList" class="absolute top-0 -end-9"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('About')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseTextarea v-model="about" :autoResize="true" :error="error.about"/>
					<BaseSelectPrivacy v-model="privacyField['about']" :options="privaciesList" class="absolute top-0 -end-9"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Location')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseSelectLocation v-model="location" :show-label="false" :error="error"/>
					<BaseSelectPrivacy v-model="privacyField['location']" :options="privaciesList" class="absolute top-0 -end-9"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Gender')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseSelect v-model="gender_id" :options="genders" optionLabel="name" optionValue="id" :error="error.gender_id" />
					<BaseSelectPrivacy v-model="privacyField['gender_id']" :options="privaciesList" class="absolute top-0 -end-9"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Birthday')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseCalendar v-model="birthday" :error="error.birthday"/>
					<BaseSelectPrivacy v-model="privacyField['birthday']" :options="privaciesList" class="absolute top-0 -end-9"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Timezone')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseSelect v-model="timezone" :options="timezones" optionLabel="key" optionValue="value" :error="error.timezone" />
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-0 md:pt-2"><label>{{$t('Link')}}</label></div>
				<div class="md:flex-2 w-full relative">
					<BaseSelectPrivacy v-model="privacyField['link']" :options="privaciesList" class="absolute top-0 -end-9"/>
					<div v-for="(link, index) in links" :key="index" class="flex items-center gap-base-2 mb-3">
						<BaseInputText v-model="links[index]" />
						<button v-if="links.length > 1" @click="removeMoreLink(index)"><BaseIcon name="close" class="text-base-red"/></button>
					</div>
					<small v-if="error.links" class="block p-error mb-2">{{error.links}}</small>
					<button class="block text-xs font-bold text-primary-color dark:text-dark-primary-color" @click="addMoreLink">{{$t('Add more link')}}</button>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3">
				<div class="md:flex-1 md:text-end w-full mb-1"></div>
				<div class="md:flex-2 w-full"><div class="text-sub-color text-xs italic dark:text-slate-400">{{ $t("* Field privacy setting will apply for the 'Info' tab in your profile. If it's Only me, it does not show there.") }}</div></div>
			</div>
		</div>
	</template>
	<div class="flex flex-wrap gap-x-5">
		<div class="md:flex-1 md:text-end w-full"></div>
		<div class="md:flex-2 w-full">
			<BaseButton :loading="loadingSave" @click="saveProfileSettings()" class="w-full">{{$t('Save')}}</BaseButton>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { defineAsyncComponent } from 'vue'
import { getProfileSettings } from '@/api/setting'
import { checkPopupBodyClass } from '@/utility/index'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseSelectPrivacy from "@/components/inputs/BaseSelectPrivacy.vue"
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
	components: { Loading, BaseButton, BaseIcon, BaseInputText, BaseSelect, BaseTextarea, BaseCalendar, BaseSelectLocation, BaseSelectPrivacy, Avatar, UserName },
    data(){
		return {
			loadingStatus: true,
			about: null,
			avatar: null,
			bio: null,
			gender_id: null,
			genders: [],
			links: null,
			name: null,
			timezone: null,
			timezones: [],
			birthday: null,
			error: {
				about: null,
				bio: null,
				gender_id: null,
				links: null,
				location: null,
				name: null,
				user_name: null,
				timezone: null,
				birthday: null,
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			},
			privacyField: null,
			loadingSave: false,
			pageName: null,
			pageUserName: null,
			location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			},
			privaciesList: [
				{ icon: 'globe', name: this.$t('Everyone'), value: 1 },
				{ icon: 'users', name: this.$t('My followers'), value: 2 },
				{ icon: 'lock', name: this.$t('Only me'), value: 3 }
			],
			originalName: ''
		}
	},
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config'])
	},
	mounted(){
		this.getProfileSettings()
	},
	methods:{
		...mapActions(useAuthStore, ['storeProfileSettings', 'storeProfilePageSettings', 'updateUserMeInfo']),
		async getProfileSettings(){
			if(this.user.is_page){
				this.pageName = this.user.name
				this.pageUserName = this.user.user_name
				this.originalName = this.user.name
			} else {
				try {
					const response = await getProfileSettings()
					this.avatar = response.avatar
					this.name = response.name
					this.bio = response.bio
					this.about = response.about
					this.links = response.links.split(" ")
					this.gender_id = response.gender_id
					this.genders = [{'id': 0, 'name': this.$t('Prefer not to say')}, ...response.genders]
					this.timezone = response.timezone
					this.timezones = window._.map(response.timezones, function(key, value) {
						return { key, value }
					});
					this.birthday = response.birthday
					this.location.country_id = response.country_id
					this.location.state_id = response.state_id
					this.location.city_id = response.city_id
					this.location.zip_code = response.zip_code
					this.location.address = response.address
					this.loadingStatus = false
					this.privacyField = response.privacyField
					this.originalName = response.name
				} catch (error) {
					this.loadingStatus = false
				}
			}
		},
		async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {
				const saveSettings = async() => {
					if(this.user.is_page){
						await this.storeProfilePageSettings({
							name: this.pageName,
							user_name: this.pageUserName
						})
					} else {
						await this.storeProfileSettings({
							name: this.name,
							bio: this.bio,
							about: this.about,
							links: this.links.filter(link => link != '').join(' '),
							gender_id: this.gender_id == 0 ? null : this.gender_id,
							timezone: this.timezone,
							birthday: this.formatDateTime(this.birthday),
							country_id: this.location.country_id,
							state_id: this.location.state_id,
							city_id: this.location.city_id,
							zip_code: this.location.zip_code,
							address: this.location.address,
							privacyField: this.privacyField
						})
		
						// Remove empty link input
						if(this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;}).length > 0){
							this.links = this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;})
						}
					}	
					this.showSuccess(this.$t('Your changes have been saved.'))
					this.resetErrors(this.error)
				}

				if(this.user.is_verify){
					const isNameChanged = (this.name && this.name !== this.originalName) || (this.pageName && this.pageName !== this.originalName);
					if(isNameChanged){
						this.$confirm.require({
							message: this.$t('You will lose your profile verification badge if you change your name. Do you want to continue?'),
							header: this.$t('Please confirm'),
							acceptLabel: this.$t('Ok'),
							rejectLabel: this.$t('Cancel'),
							accept: async() => {
								await saveSettings()
								this.updateUserMeInfo({
									...this.user,
									is_verify: false
								})
							},
							reject: () => {
								this.name = this.originalName
								this.pageName = this.originalName
							}
						});
					} else {
						await saveSettings()
					}
				} else {
					await saveSettings()
				}
				
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loadingSave = false
			}
		},
		uploadAvatar(event){
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
					const UploadAvatarModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadAvatarModal.vue')
					)
					this.$dialog.open(UploadAvatarModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Avatar'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								this.avatar = options.data.avatar
							}
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);

			}
		},
		onInputClick(event){
			event.target.value = null
		},
		addMoreLink(){
			this.error.links = null
			this.links.push('')
		},
		removeMoreLink(id){
			this.links = this.links.filter((link, index) => index != id)

			if (this.links.length == 0) {
				this.links.push('')
			}
		}
    }
}
</script>