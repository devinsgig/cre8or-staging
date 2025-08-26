<template>
    <div>
        <div v-if="message" class="mb-base-2">{{message}}</div>
        <div class="flex gap-base-2 justify-center">
            <BaseButton v-if="config.membership.enable" :to="{name: 'membership', force: true}">{{$t('Upgrade Now')}}</BaseButton>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue';
import { useAppStore } from '@/store/app'
export default {
    components: { BaseButton },
    inject: ['dialogRef'],
    data() {
		return {
			permission: this.dialogRef.data.permission,
            message: this.dialogRef.data.message,
		}
	},
    mounted() {
        if (! this.message) {
            this.message = window._.has(this.config.permissionMessages, this.permission) ? this.config.permissionMessages[this.permission] : this.$t('You do not have permission to do it.')
        }
    },
    computed: {
		...mapState(useAppStore, ['config']),
    },
    methods: {
        clickClose() {
            this.dialogRef.close()
        }
    }
}
</script>