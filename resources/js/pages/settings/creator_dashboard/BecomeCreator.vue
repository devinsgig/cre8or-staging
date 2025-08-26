<template>
    <div>
        <h3 class="font-bold text-xl mb-base-2">{{ $t('Become a creator') }}</h3>
        <p class="mb-4">{{ $t('To become a creator, you need to complete the items below.') }}</p>
        <div v-if="paidContentConfig" class="flex flex-col items-start gap-4 mb-4">
            <div
                v-for="item in checklistItems"
                :key="item.field"
                class="flex gap-base-2 items-center cursor-pointer"
                :class="paidContentConfig[item.field] ? 'text-base-green cursor-text pointer-events-none dark:text-base-green' : 'text-primary-color dark:text-primary-dark-color'"
                @click="item.handler"
            >
                <BaseIcon :name="paidContentConfig[item.field] ? 'check_circle' : 'x_circle'" />
                <span>{{ item.title }}</span>
            </div>
        </div>
        <div v-if="canCreatePaidContent" class="bg-web-wash rounded-base-lg px-5 py-3 text-center dark:bg-dark-web-wash">
            <BaseIcon name="check_circle" size="44" class="text-base-green mb-3" />
            <div>{{ $t('Your account has met all the requirements to create paid content to earn money.') }}</div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'   
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { getPaidContentConfig } from '@/api/paid_content'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { 
        BaseIcon
    },
    data(){
        return{
            paidContentConfig: null
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user']),
        checklistItems() {
            const items = [
            {
                field: 'check_profile',
                title: this.$t('Update your profile picture and profile cover'),
                handler: this.handleUpdateProfile
            },
            {
                field: 'check_membership',
                title: this.$t('Upgrade membership'),
                handler: this.handleUpgradeMembership
            },
            {
                field: 'check_price',
                title: this.$t('Setup subscription pricing'),
                handler: this.handleSetPricing
            }
            ]
            if (this.config?.userVerifyEnable && this.config?.paid_content.require_verify) {
                items.push({
                    field: 'check_verify',
                    title: this.$t('Verify your profile'),
                    handler: this.handleVerifyProfile
                });
            }
            return items;
        },
        canCreatePaidContent() {
            return this.paidContentConfig 
                ? Object.values(this.paidContentConfig).every(value => value === true)
                : false;
        }
    },
    mounted(){
        this.handleGetConfig()
    },
    methods:{
        async handleGetConfig(){
            try {
                const response = await getPaidContentConfig()
                this.paidContentConfig = response
            } catch (error) {
                this.showError(error)
            }
        },
        handleUpdateProfile() {
            this.$router.push({ name: 'profile', params: {user_name: this.user.user_name} });
        },
        handleUpgradeMembership() {
            if(!this.config.membership.enable && !this.paidContentConfig.check_membership){
                return this.showPermissionPopup('paid_content.allow_create')
            }
            this.$router.push({ name: 'membership' });
        },
        handleSetPricing() {
            this.$router.push({ name: 'setting_creator_dashboard', params: { tab: 'set_pricing' } });
        },
        handleVerifyProfile() {
            this.$router.push({ name: 'verify_profile' });
        },
    }
}
</script>