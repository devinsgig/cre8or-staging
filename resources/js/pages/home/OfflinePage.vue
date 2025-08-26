<template>
    <div class="flex justify-center w-full">
        <div>
            <div class="text-center mb-10">
                <Logo />
            </div>
            <div class="main-content-section bg-white rounded-base mb-base-2 w-full sm:w-96 p-7 dark:bg-slate-800 dark:text-white">
                <div class="text-base-lg mb-4">{{$t("Site is under maintenance")}}</div>
                <div class="text-base-sm" v-html="config.offlineMessage"></div>
                <div class="text-center mt-3">
                    <BaseButton @click="visibleAccessCodeModal = true">{{$t('Access Site')}}</BaseButton>
                </div>
            </div>
        </div>
    </div>
    <DialogPrimevue :header="$t('Enter Access Code')" v-model:visible="visibleAccessCodeModal" :style="{ maxWidth: '28rem' }" :modal="true" :dismissableMask="true">
        <BaseInputText v-model="code" @keyup.enter="checkAccessCode(code)"/>
        <div class="text-center mt-3">
            <BaseButton :loading="loading" @click="checkAccessCode(code)">{{$t('Enter')}}</BaseButton>
        </div>
    </DialogPrimevue>
</template>

<script>
import { mapState } from 'pinia';
import { checkAccessCode } from '@/api/utility'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAppStore } from '../../store/app';
import { useActionStore } from '../../store/action'
import localData from '@/utility/localData';
import DialogPrimevue from 'primevue/dialog';
import Logo from '@/components/utilities/Logo.vue';

export default {
    components: { BaseButton, DialogPrimevue, BaseInputText, Logo },
    props: ['data', 'params', 'position'],
    data(){
        return{
            code: null,
            visibleAccessCodeModal: false,
            loading : false
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useActionStore, ['samePage'])
    },    
    methods: {
        async checkAccessCode(code){
            if (this.loading) {
                return
            }
            this.loading = true
            try {
                await checkAccessCode({
                    access_code: code
                })
                localData.set('access_code', code)
                window.location.href = window.siteConfig.siteUrl
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loading = false
            }
        }
    },
    watch: {
        samePage(){
			window.location.href = window.siteConfig.siteUrl
		}
    }
}
</script>