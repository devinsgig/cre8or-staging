<template>
    <div class="widget-box bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
            <h3 class="widget-box-header-title text-main-color text-base-lg font-extrabold dark:text-white">
                <slot name="title">{{ data.title }}</slot>
            </h3>
            <slot name="link"></slot>
        </div>
        <slot name="body"></slot>
    </div>
</template>

<script>
export default {
    props: ['data']
}
</script>