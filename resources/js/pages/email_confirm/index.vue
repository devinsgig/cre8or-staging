<template>
	<div class="bg-white rounded-base w-full sm:w-96 p-7 mx-auto mb-base-2 dark:bg-slate-800 dark:text-white">
		<div>
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Enter verification code')}}</h3>
			</div>
            <p class="mb-4">{{$t('Please check your email for a message with your code.')}}</p>
            <BaseInputText v-model="code" class="w-full" :placeholder="$t('Code')" />
            <p class="mt-1 break-words">{{$t('We sent your code to')}}:&nbsp;{{ user.email }}</p>	
            <BaseButton :loading="loading" class="w-full mt-3" @click="checkVerifyCode()">{{$t('Continue')}}</BaseButton>
            <BaseButton :loading="loading_resend" type="transparent" class="w-full mt-2" @click="resendCode()">{{$t('Resend code')}}</BaseButton>
            <div class="mt-base-2 text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color" @click="logout()">{{$t('Login')}}</button></div>
		</div>
	</div>
</template>
<script>
import { checkEmailVerify, sendEmailVerify } from '@/api/user'
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
	components:{ BaseInputText, BaseButton },
    data(){
		return{
            code: null,
            loading: false,
			loading_resend: false
		}
	},
    computed: {
        ...mapState(useAuthStore, ['user']),
    },
	methods: {
		async checkVerifyCode(){
            this.loading = true
            try {
                await checkEmailVerify(this.code)
                this.loading = false
                if (! this.user.already_setup_login) {
                    this.$router.push({'name' : 'first_login'})
                }
                
            } catch (error) {                
                this.loading = false
                this.showError(error.error)
            }
        },
        async resendCode() {
            this.loading_resend = true
            try {
                await sendEmailVerify()
                this.loading_resend = false             
                this.showSuccess(this.$t('A verification code has been sent to your email account.'))   
            } catch (error) {
                this.loading_resend = false
                this.showError(error.error)
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