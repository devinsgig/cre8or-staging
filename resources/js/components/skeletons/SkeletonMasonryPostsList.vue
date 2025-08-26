<template>
    <div class="grid grid-cols-3 gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="pb-[100%] relative">
            <div class="absolute inset-0">
                <Skeleton height="100%" width="100%" />
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
            default: 3
        }
    }
};
</script>