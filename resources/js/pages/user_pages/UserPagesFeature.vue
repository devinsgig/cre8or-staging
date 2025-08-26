<template>
     <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="text-center">
            <h3 class="text-base-lg font-extrabold mb-base-1">{{ $t('Help People more Ealisy Connect with Your Business') }}</h3>
            <p class="mb-base-2">{{ $t('Get your page featured and get discovered by thousand of users on site.') }}</p>
            <BaseSelect class="mb-base-2 max-w-[240px] mx-auto" v-model="selectedPackage" :options="pagePackagesList" optionLabel="description" :placeholder="$t('Select a package')"  :emptyMessage="$t('No packages found')"/>
            <BaseButton class="mb-base-2" @click="handleClickFeaturePage()" :disabled="!selectedPackage">{{ $t('Featured now!') }}</BaseButton>
            <div>
                <img :src="featureImageSrc" alt="featureImage" class="mx-auto">
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions} from 'pinia'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import FeaturePageModal from '@/components/modals/FeaturePageModal.vue'
import { getFeaturePackages } from '@/api/page'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'

export default {
    components : { BaseSelect, BaseButton },
    data(){
        return {
            pagePackagesList: [],
            selectedPackage: null
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode']),
        featureImageSrc(){
            if (this.darkmode === 'on') {
				return this.config.user_page.featureImageDark
			}else if (this.darkmode === 'off') {
				return this.config.user_page.featureImage
			}else{
				if (this.systemMode === 'dark') {
					return this.config.user_page.featureImageDark
				} else if (this.systemMode === 'light') {
					return this.config.user_page.featureImage
				}
			}
            return null
        }
    },
    mounted(){
        if (! this.user.is_page) {
            this.setErrorLayout(true)
        } else {
            this.getPackagesList()
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getPackagesList(){
            try {
                const response = await getFeaturePackages()
                this.pagePackagesList = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleClickFeaturePage(){
            if(!this.config.wallet.enable){
                this.showError(this.$t('The eWallet system is not available at this time.'))
                return
            }
            this.$dialog.open(FeaturePageModal, {
                data: {
                    packageInfo: this.selectedPackage
                },
                props: {
                    header: this.$t('Featured page'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        }
    }
}
</script>