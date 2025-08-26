<template>
    <div v-for="i in itemNumber" :key="i" class="feed-item bg-white border border-white rounded-none md:rounded-base-lg p-base-2 md:p-4 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <div class="flex mb-base-2">
            <Skeleton shape="circle" size="2.5rem" />
            <div class="flex-grow ps-base-2">
                <Skeleton width="8rem" class="mb-base-1" />
                <Skeleton width="6rem" />
            </div>
        </div>
        <div>
            <Skeleton width="100%" height="5rem" />
        </div>
        <div class="flex flex-col gap-base-2 mt-base-2">
            <div class="flex flex-wrap gap-x-base-2 gap-y-base-1 items-center">
                <div class="flex-1 flex gap-base-2">
                    <Skeleton width="3rem" height="1rem" />
                    <Skeleton width="3rem" height="1rem" />
                    <Skeleton width="3rem" height="1rem" />
                </div>
                <Skeleton width="4rem" height="1.5rem" />
            </div>
            <div class="flex gap-base-2 justify-between py-base-1">
                <div class="flex-1 flex gap-2 items-center justify-center px-2 py-base-1">
                    <Skeleton width="1.5rem" height="1.5rem" />
                    <Skeleton width="3rem" height="1rem" class="hidden md:block" />
                </div>
                <div class="flex-1 flex gap-2 items-center justify-center px-2 py-base-1">
                    <Skeleton width="1.5rem" height="1.5rem" />
                    <Skeleton width="3rem" height="1rem" class="hidden md:block" />
                </div>
                <div class="flex-1 flex gap-2 items-center justify-center px-2 py-base-1">
                    <Skeleton width="1.5rem" height="1.5rem" />
                    <Skeleton width="3rem" height="1rem" class="hidden md:block" />
                </div>
                <div class="flex-1 flex gap-2 items-center justify-center px-2 py-base-1">
                    <Skeleton width="1.5rem" height="1.5rem" />
                    <Skeleton width="3rem" height="1rem" class="hidden md:block" />
                </div>
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
            default: 5
        }
    }
};
</script>