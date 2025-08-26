<template>
    <TabsMenu :menus="creatorDashboardTabs" type="secondary" @select="changeTab" class="mb-4" />
    <Component :is="creatorDashboardComponent" />
</template>

<script>
import { mapState } from 'pinia'   
import { useAppStore } from '@/store/app'
import TabsMenu from '@/components/menu/TabsMenu.vue';
import BecomeCreator from './BecomeCreator.vue';
import SetPricing from './SetPricing.vue';
import EarningReport from './EarningReport.vue';
import Subscribers from './Subscribers.vue'

export default {
    props: ['tab'],
    components: { 
        TabsMenu
    },
    data(){
        return{
            currentTab: this.tab || ''
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        creatorDashboardTabs(){
            return [
                { name: this.$t('Become a creator'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Subscribers'), tab: 'subscribers', isActive: this.currentTab === 'subscribers' },
                { name: this.$t('Set Pricing'), tab: 'set_pricing', isActive: this.currentTab === 'set_pricing' },
                { name: this.$t('Earning Report'), tab: 'earning_report', isActive: this.currentTab === 'earning_report' },
            ]
        },
        creatorDashboardComponent() {
			switch(this.currentTab){
                case 'subscribers':
                    return Subscribers;
                case 'set_pricing':
                    return SetPricing;
                case 'earning_report':
                    return EarningReport;
				default: 
					return BecomeCreator;
			}
		}
    },
    methods:{
        changeTab(name) {
            this.$router.push({ name: 'setting_creator_dashboard', params: { tab: name } })
		}
    }
}
</script>