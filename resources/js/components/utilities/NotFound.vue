<template>
    <div class="main-content-section bg-white border border-white text-main-color p-16 text-center rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="text-base-lg font-bold mb-base-2">{{$t("Sorry, this page isn't available.")}}</div>
        <div class="text-base-sm">{{$t('The link you followed may be broken, or the page may have been removed.')}}</div>
        <BaseButton :to="{ name: 'home' }" class="mt-5">{{$t('Go back to Home')}}</BaseButton>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton }
}
</script>