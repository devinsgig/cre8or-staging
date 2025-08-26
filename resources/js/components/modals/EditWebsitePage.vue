<template>
    <div>
        <div v-for="(website, index) in websites" :key="index" class="flex items-center gap-base-2 mb-3">
            <BaseInputText v-model="websites[index]" :autofocus="true"/>
            <button v-if="websites.length > 1" @click="removeMoreLink(index)"><BaseIcon name="close" class="text-base-red"/></button>
        </div>
        <small v-if="errorWebsites" class="block p-error mb-2">{{errorWebsites}}</small>
        <button class="block text-xs font-bold text-primary-color dark:text-dark-primary-color mb-base-2" @click="addMoreLink">{{$t('Add more link')}}</button>
        <BaseButton @click="saveWebsites(websites)" class="w-full">{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { storeWebsitesPage } from '@/api/page'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            websites: window._.cloneDeep(this.dialogRef.data.pageWebsites),
            errorWebsites: null
        }
    },
    components: { BaseInputText, BaseButton, BaseIcon },
    methods: {
        async saveWebsites(websites){
            try {
                await storeWebsitesPage(websites.filter(website => website != '').join(' '))

                // Remove empty link input
				if(this.websites.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;}).length > 0){
					this.websites = this.websites.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;})
				}
                this.errorWebsites = null
                this.dialogRef.close({websites: this.websites});
				this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.errorWebsites = error.error.detail['websites']
            }
        },
        addMoreLink(){
			this.errorWebsites = null
			this.websites.push('')
		},
        removeMoreLink(id){
			this.websites = this.websites.filter((website, index) => index != id)
			if (this.websites.length == 0) {
				this.websites.push('')
			}
		}
    }
}
</script>