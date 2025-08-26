<template>
	<div class="bg-white rounded-base w-full sm:w-96 p-7 mx-auto mb-base-2 dark:bg-slate-800 dark:text-white">
		<div>
            <div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Enter verification code')}}</h3>
			</div>
            <p class="mb-1">{{$t('We just sent a code to your phone number')}}&nbsp;{{ phoneNumber }}.&nbsp;{{ $t('Code will expire in 2 minutes.') }}</p>	
            <BaseInputText v-model="code" class="w-full mb-base-2" :placeholder="$t('Code')" />
            <div v-if="enableWidget" class="text-center">
                <CloudFlareTurnstile v-model="turnstileToken" />
            </div>
            <div class="flex flex-col gap-base-2">
                <BaseButton :loading="loadingSend" :disabled="!isVerified()" @click="handleCheckPhoneVerify()">{{$t('Continue')}}</BaseButton>
                <BaseButton :loading="loadingResend" :disabled="!isVerified()" type="secondary" @click="handleSendPhoneVerify(false)">{{$t('Resend code')}}</BaseButton>
                <BaseButton :disabled="!isVerified()" type="secondary" @click="reEnterPhoneNumber()">{{$t('Re-enter phone number')}}</BaseButton>
            </div>
            <div class="mt-base-2 text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color" @click="logout()">{{$t('Logout')}}</button></div>
		</div>
	</div>
</template>
<script>
import { sendPhoneVerify, checkPhoneVerify } from '@/api/user'
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import ChangePhoneVerifyModal from '@/components/modals/ChangePhoneVerifyModal.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
	components:{ 
        BaseInputText, 
        BaseButton,
        CloudFlareTurnstile 
    },
    data(){
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.sendPhoneEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
		return{
            token: null,
            code: null,
            loadingSend: false,
			loadingResend: false,
            phoneNumber: null,
            ...captcha
		}
	},
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
    },
    mounted(){
        this.phoneNumber = this.user.phone_number
        this.handleSendPhoneVerify()

        setTimeout(() => {
            this.loadRecaptcha(this.$recaptchaInstance)
        }, 2000);
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
	methods: {
        async handleSendPhoneVerify(isFirst = true){
            if(!isFirst){
                this.loadingResend = true
            }
            try {
                this.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "send_phone")
                await sendPhoneVerify({
                    token: this.token
                })
                if(!isFirst){
                    this.showSuccess(this.$t('A new code has been sent to your phone number.'))
                }
            } catch (error) {
                if(!isFirst){
                    this.showError(error.error)
                }
            } finally{
                if(!isFirst){
                    this.loadingResend = false
                }
            }
        },
        reEnterPhoneNumber(){
            this.$dialog.open(ChangePhoneVerifyModal, {
                props:{
					header: this.$t('Enter new phone number'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false,
                    class: 'enter-phone-modal'
                },
				onClose: (options) => {
                    if(options.data){
                        this.phoneNumber = options.data.phone_number
                    }
                }
            });
        },
		async handleCheckPhoneVerify(){
            this.loadingSend = true
            try {
                await checkPhoneVerify(this.code)
                if (! this.user.already_setup_login) {
                    this.$router.push({'name' : 'first_login'})
                } else {
                    this.$router.push({'name' : 'home'})
                }
            } catch (error) {                
                this.showError(error.error)
            } finally {
                this.loadingSend = false
            }
        },
        async logout() {
            try {
                await useAuthStore().logout();
                window.location.href = `${window.siteConfig.siteUrl}/login`;
            } catch (error) {
                this.showError(error.error)
            }
        }
	}
}
</script>