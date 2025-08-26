<template>
    <div class="flex flex-col items-start ps-16 py-10 pe-10">
        <button v-for="(language, index) in languages" :key="index" class="block relative mt-5 first:mt-0" :class="getActiveLanguage == index ? 'font-bold' : ''" @click="(getActiveLanguage != index) ? setActiveLanguage(index) : null">
            <BaseIcon v-if="getActiveLanguage == index" name="check" class="absolute -top-[2px] -start-8 text-main-color dark:text-white"></BaseIcon>
            {{ language }}
        </button>
        <button autofocus @click="cancel()"></button>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { changeLanguage } from '@/api/user'
import { useAuthStore } from '@/store/auth';
import localData from '@/utility/localData';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    data(){
        return{
            languages: window.siteConfig.languages
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user', 'authenticated']),
        getActiveLanguage(){
            return this.user.language
        }
    },
    methods: {
        async setActiveLanguage(languageKey){
            if(this.authenticated){
                try {
                    await changeLanguage({key: languageKey})
                    window.location.reload()
                } catch (error) {
                    this.showError(error.error)
                }
            }else{
                localData.set('locale', languageKey)
                window.location.reload()
            }
        },
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>