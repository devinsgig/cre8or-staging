<template>
    <div v-if="groupsList.length" class="groups-list">
        <div v-for="group in groupsList" :key="group.id" class="groups-list-item flex items-center gap-base-2 mb-base-2 last:mb-0">
            <router-link :to="{name: 'group_profile', params: { id: group.id, slug: group.slug }}" class="w-10 h-10 rounded-full border border-divider dark:border-slate-700">
                <img :src="group.cover" :alt="group.name" class="rounded-full w-full h-full object-cover">
            </router-link>
            <div class="flex-1 min-w-0">
                <GroupName :group="group" class="!block truncate" />
                <div class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(group.member_count, $t('[number] member'), $t('[number] members')) }}</div> 
            </div>
        </div>
    </div>
    <div v-else class="text-center">
        <slot name="empty">{{ $t('No groups') }}</slot>
    </div>
</template>

<script>
import GroupName from '@/components/group/GroupName.vue';

export default {
    components: { GroupName },
    props: {
        groupsList: {
            type: Array,
            default: () => []
        }
    }
}
</script>