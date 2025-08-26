<template>
    <div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
        <h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
    </div>
    <SignupForm />
</template>

<script>
import SignupForm from '@/pages/signup/SignupForm.vue'

export default {
    components: { SignupForm },
    props: ['data', 'params', 'position']
}
</script>