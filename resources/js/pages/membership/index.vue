<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Compare plans') }}</h3>
        </div>
        <p class="mb-5">{{ $t("Please choose the appropriate package to subscribe. If you have an active subscription and decide to switch to another package to subscribe. The system will not refund and unused days will not be added to the new package. It's best to cancel your current package and wait for the cycle to end to switch to a new package.") }}</p>
        <CurrentPlan v-if="currentPackage" :subscription="currentPackage.subscription" @cancel="handleCancelSubscription" @resume="handleResumeSubscription" class="mb-5" />
        <Loading v-if="!subscriptionConfig"/>
        <template v-else>
            <div v-if="subscriptionConfig.packages.length">
                <div class="grid md:grid-cols-3 gap-5 mb-5">
                    <PackageBox v-for="subscriptionPackage in subscriptionConfig.packages" :key="subscriptionPackage.id" :data="subscriptionPackage" :current-plan="currentPackage" :badgeStyle="{backgroundColor: subscriptionConfig.highlight_background_color, color: subscriptionConfig.highlight_text_color, text: subscriptionConfig.highlight_text}" @select="handleSelectPackage" @update="getCurrentPackpage" />
                </div>
                <div v-if="subscriptionConfig.compares.length" class="relative overflow-x-auto">
                    <table class="w-full whitespace-nowrap text-center">
                        <thead>
                            <tr class="bg-table-header-color dark:bg-dark-web-wash">
                                <th scope="col" class="p-3 text-start rounded-l-base sticky left-0 bg-inherit max-w-xs whitespace-pre-wrap min-w-40">{{$t('Features')}}</th>
                                <th v-for="subscriptionPackage in subscriptionConfig.packages" :key="subscriptionPackage.id" scope="col" class="p-3 w-44 text-center last:rounded-r-base whitespace-pre-wrap">{{ subscriptionPackage.name }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(subscriptionCompare, index) in subscriptionConfig.compares" :key="index" class="odd:bg-white odd:dark:bg-gray-800 even:bg-light-blue even:dark:bg-gray-900">
                                <td class="p-3 text-start font-semibold rounded-l-base sticky left-0 bg-inherit max-w-xs whitespace-pre-wrap min-w-40">
                                    {{ subscriptionCompare.name }}
                                </td>
                                <td v-for="(packageCompare, index) in subscriptionCompare.packages" :key="index" class="p-3 text-center last:rounded-r-base w-44 whitespace-pre-wrap">
                                    <div v-if="packageCompare.type == 'text'">
                                        {{ packageCompare.value }}
                                    </div>
                                    <template v-else>
                                        <div v-if="packageCompare.value == 1" class="text-base-green">
                                            <BaseIcon name="check_circle"/>
                                        </div>
                                        <div v-else class="text-base-red">
                                            <BaseIcon name="x_circle"/>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else class="text-center">{{ $t('No packages available') }}</div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getSubscriptionConfig, getCurrentSubscription, storeSubscription, storeTrialSubscription } from '@/api/membership'
import { cancelSubscription, resumeSubscription } from '@/api/subscription'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Loading from '@/components/utilities/Loading.vue'
import PackageBox from '@/components/membership/PackageBox.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import CurrentPlan from '@/components/membership/CurrentPlan.vue'

export default {
    components: { BaseIcon, Loading, PackageBox, CurrentPlan },
    data() {
        return {
            currentPackage: null,
            subscriptionConfig: null,
            planSelected: []
        }
    },
    mounted(){
        if(!this.config.membership.enable || this.user.is_moderator){
            return this.$router.push({ 'name': 'permission' })
        } 
        this.getConfig()
        this.getCurrentPackpage()
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        ...mapActions(useAuthStore, ['updateCurrentPlan']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        async getConfig(){
            try {
                const response = await getSubscriptionConfig()
                this.subscriptionConfig = response
            } catch (error) {
                this.showError(error)
            }
        },
        async getCurrentPackpage(){
            try {
                const response = await getCurrentSubscription()
                this.currentPackage = response
            } catch (error) {
                this.showError(error)
            }
        },
        async handleSelectPackage(selectedInfo){
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
                emits: {
                    onConfirm: async (data) => {
                        if (data.password) {
                            try {
                                if(selectedInfo.plan.trial_day){
                                    await storeTrialSubscription({
                                        plan_id: selectedInfo.plan.id,
                                        password: data.password
                                    })                             
                                } else {
                                    await storeSubscription({
                                        plan_id: selectedInfo.plan.id,
                                        password: data.password
                                    })
                                }
                                this.getCurrentPackpage()
                                this.getConfig()
                                this.updateCurrentPlan(selectedInfo.package.name)
                                this.pingNotification()
                                this.showSuccess(this.$t('Select package successfully.'))
                                passwordDialog.close()
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                            }
                        }
                    }
                }
			})
        },
        async handleCancelSubscription(subscriptionId){
            try {
                await cancelSubscription(subscriptionId)
                this.getCurrentPackpage()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleResumeSubscription(subscriptionId){
            try {
                await resumeSubscription(subscriptionId)
                this.getCurrentPackpage()
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>