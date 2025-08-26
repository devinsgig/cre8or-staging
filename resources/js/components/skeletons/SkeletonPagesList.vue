<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="rounded-lg border border-divider dark:border-white/10 bg-white h-full dark:bg-slate-800">
            <div class="block pb-[35%] relative overflow-hidden rounded-t-lg">
                <div class="absolute inset-0">
                    <Skeleton height="100%" width="100%" class="rounded-none"/>
                </div>
            </div>
            <div class="p-base-2">
                <div class="flex gap-4 mb-base-2">
                    <Skeleton shape="circle" size="3.125rem" class="flex-shrink-0" />
                    <div class="flex flex-col gap-2">
                        <Skeleton height="1.25rem" width="15rem" />
                        <Skeleton width="10rem" />
                        <Skeleton width="10rem" />
                    </div>
                </div>
                <Skeleton width="100%" height="2.25rem" />
            </div>
        </div>
    </div>
</template>

<script>
import Skeleton from "primevue/skeleton";

export default {
    components: { Skeleton },
    props: {
        itemNumber: {
            type: Number,
            default: 2
        }
    }
};
</script>