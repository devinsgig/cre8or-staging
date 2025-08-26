<template>
    <button class="relative group">
        <BaseIcon name="circle_switch" size="48" class="relative z-10" svgClass="group-hover:transition-transform group-hover:duration-500 group-hover:ease-in-out group-hover:rotate-180"/>
        <img :src="page.avatar" alt="" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-9 rounded-full z-10">
    </button>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    props: ['page']
}
</script>