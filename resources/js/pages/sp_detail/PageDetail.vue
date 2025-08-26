<template>
    <div class="bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between mb-3">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{data.data.title}}</h3>
        </div>
        <div v-html="data.data.content" class="page-content-section"></div>
    </div>
</template>

<script>
export default {
    props: ['data', 'params', 'position']
}
</script>

<style>

</style>