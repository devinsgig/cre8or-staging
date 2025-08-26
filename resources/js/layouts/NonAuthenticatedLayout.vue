<template>
    <template v-if="checkMobileApp">
        <router-view :key="$route.path" />
    </template>
    <template v-else>
        <HeaderNonLogin v-if="!(['login', 'signup', 'recover'].includes($route.name)) && !checkOfflinePage" />
        <div v-if="error" class="max-w-container w-full mx-auto p-0">
            <NotFound />
        </div>
        <div v-else class="max-w-container w-full mx-auto p-0">
            <div class="flex flex-col justify-between items-center p-4 lg:px-0"
                :class="['login', 'signup', 'recover'].includes($route.name) ? 'h-screen' : 'h-screen-non-login'">
                <div v-if="['login', 'signup', 'recover'].includes($route.name) || checkOfflinePage">&nbsp;
                </div>
                <div class="w-full">
                    <router-view :key="$route.path" />
                </div>
                <div v-if="checkOfflinePage">&nbsp;</div>
                <FooterSite v-if="!checkOfflinePage" class="text-center" />
            </div>
        </div>
    </template>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { checkOffline, checkMobileApp } from '@/utility/index'
import FooterSite from '@/components/layout/FooterSite.vue';
import HeaderNonLogin from '@/components/layout/HeaderNonLogin.vue';
import NotFound from '@/components/utilities/NotFound.vue';

export default {
    components: {
        FooterSite,
        HeaderNonLogin,
        NotFound
    },
    data() {
		return {
			error: false
		};
	},
    computed: {
        ...mapState(useAppStore, ['errorLayout']),
        checkOfflinePage() {
			return checkOffline()
		},
        checkMobileApp() {
			return checkMobileApp()
		}
    },
    watch: {
        errorLayout(error) {
			this.error = error
		},
    },
}
</script>