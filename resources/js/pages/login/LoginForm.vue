<template>
    <div class="widget-box bg-white rounded-base-lg max-w-full w-full sm:w-96 p-7 mx-auto mb-base-2 dark:bg-slate-800"> 
        <h3 class="text-base-lg font-extrabold mb-5 dark:text-white">{{$t('Login')}}</h3>
        <BaseInputText v-model="user.email" :placeholder="$t('Email')" :error="error.email" left_icon="mail" @keyup.enter="login" class="mb-base-2" />
        <BasePassword v-model="user.password" :placeholder="$t('Password')" autocomplete="current-password" :error="error.password" @keyup.enter="login" class="mb-base-2" />
        <div v-if="enableWidget" class="text-center">
            <CloudFlareTurnstile v-model="turnstileToken" />
        </div>
        <BaseButton @click="login" :loading="loadingLogin" :disabled="!isVerified()" class="w-full">{{$t('Login')}}</BaseButton>
        <template v-if="config.openidProviders.length">
            <div class="my-5 text-center">{{$t('Or Sign in using')}}</div>
            <div class="flex flex-wrap gap-3 justify-center mb-5">
                <a v-for="(openId, index) in config.openidProviders" :key="index" :href="openId.href">
                    <img class="max-w-[2rem]" :src="openId.photo" :alt="openId.name">
                </a>
            </div>
        </template>
        <div class="mt-5 text-center"><router-link :to="{ name: 'recover' }" class="text-primary-color dark:text-dark-primary-color">{{$t('Forgot password')}}</router-link></div>
        <div v-if="this.config.signupEnable" class="mt-5 text-center">{{$t("Don't have an account?")}}&nbsp;<router-link :to="{ name: 'signup' }" class="text-primary-color dark:text-dark-primary-color">{{$t('Sign up')}}</router-link></div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useCaptcha } from '@/hooks/useCaptcha'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import CloudFlareTurnstile from '@/components/utilities/CloudFlareTurnstile.vue'

export default {
    components: { BaseButton, BasePassword, BaseInputText, CloudFlareTurnstile },
    data() {
        const appStore = useAppStore()
        const captcha = useCaptcha(appStore.config.loginEnableRecapcha, this.enableRecapcha, this.enableTurnstile)
        return {
            user: {
                email: null,
                password: null
            },
            error: {
                email: null,
				password: null
			},
            loadingLogin: false,
            ...captcha
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    mounted(){
        setTimeout(() => {
            this.loadRecaptcha(this.$recaptchaInstance)
        }, 2000);
    },
    unmounted(){
        this.unloadRecaptcha(this.$recaptchaInstance)
    },
    methods: {
        async login() {
            this.loadingLogin = true
            try {
                this.user.token = await this.getCaptchaToken(this.$recaptcha, this.turnstileToken, "login")

                await useAuthStore().login(this.user);
                
                const redirect = this.$route.query.redirect
                const target = redirect && atob(redirect).includes(window.siteConfig.siteUrl)
                    ? atob(redirect)
                    : window.siteConfig.siteUrl

                window.location.href = target
                
                this.resetErrors(this.error)
            }
            catch (error) {
                this.handleApiErrors(this.error, error)
            }
            finally {
                this.loadingLogin = false;
            }
        }
    }
}
</script>