<template>
    <div class="main-content-section bg-white border border-white text-main-color p-16 text-center rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="text-base-lg font-bold mb-base-2">{{$t("Unsubscribe successful.")}}</div>
        <div class="text-base-sm">{{$t('You have successfully unsubscribed from email communications.')}}</div>
        <BaseButton :to="{ name: 'home' }" class="mt-5">{{$t('Go back to Home')}}</BaseButton>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import {unsubscribeEmail} from '@/api/utility'

export default {
    components: { BaseButton },
    props: ['email', 'hash'],
    mounted() {
        unsubscribeEmail({
            'email': this.email,
            'hash': this.hash
        })
    }
}
</script>