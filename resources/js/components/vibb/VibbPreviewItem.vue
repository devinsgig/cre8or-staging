<template>
    <ContentWarningWrapper :content-warning-list="item.content_warning_categories" :post="item" class="rounded-lg">
        <div @click="handleOpenVibbModal" class="cursor-pointer">
            <div class="relative pb-[162.5%] bg-cover bg-center bg-no-repeat rounded-lg" :style="{ backgroundImage: `url(${subjectInfo?.thumb?.url})`}">
                <span class="absolute bottom-base-2 start-3 flex items-center gap-2 text-white z-10">
                    <BaseIcon name="eye"/>
                    {{ item.view_count }}
                </span>
                <div class="h-20 bg-footer-linear absolute bottom-0 left-0 right-0 rounded-b-lg"></div>
            </div>        
        </div>
    </ContentWarningWrapper>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';

export default {
    props: {
        item: {
            type: Object,
            default: null
        },
        subjectId: {
            type: Number,
            default: 0
        },
        subjectType: {
            type: String,
            default: ''
        }
    },
    components: { BaseIcon, ContentWarningWrapper },
    computed:{
        subjectInfo(){
            return this.item.items[0]?.subject
        }
    },
    methods:{
        handleOpenVibbModal(){
            this.openVibb({
                vibb: this.item,
                subjectId: this.subjectId,
                subjectType: this.subjectType
            })
        }
    }
}
</script>