<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Create New Page') }}</h3>
        </div>
        <form @submit.prevent="handleCreatePage">
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Page Name') }}</label>
                <BaseInputText v-model="name" :placeholder="$t('Please enter page name')" :error="error.name"/>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Page Alias') }}</label>
                <BaseInputText v-model="user_name" :placeholder="$t('Please enter page alias')" :error="error.user_name"/>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Description') }}</label>
                <BaseTextarea v-model="description" :placeholder="$t('Enter page description')" :error="error.description" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Categories') }}</label>
                <BaseSelectCategories v-model="categories" :error="error.categories" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Hashtags') }}</label>
                <BaseSelectHashtags v-model="hashtags" :error="error.hashtags" />
            </div>
            <BaseButton class="w-full" :loading="loadingCreate">{{ $t('Continue') }}</BaseButton>
        </form>
    </div>
</template>

<script>
import { storeUserPage } from '@/api/page'
import { mapState, mapActions} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseSelectCategories from '@/components/inputs/BaseSelectCategories.vue'
import BaseSelectHashtags from '@/components/inputs/BaseSelectHashtags.vue'
import Constant from '@/utility/constant'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea, BaseSelectCategories, BaseSelectHashtags },
    data(){
        return{
            loadingCreate: false,
            name: null,
            user_name: null,
            description: null,
            categories: [],
            hashtags: [],
            error: {
				name: null,
				user_name: null,
                description: null,
                categories: null,
                hashtags: null
            }
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    mounted() {
        if (this.user.is_page) {
            this.setErrorLayout(true)
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        async handleCreatePage(){
            this.loadingCreate = true
            try {
                const response = await storeUserPage({
                    name: this.name,
                    user_name: this.user_name,
                    description: this.description,
                    categories: this.categories,
                    hashtags: this.hashtags.map(hashtag => hashtag.name)
                })
                this.$router.push({ name: 'profile',  params: {user_name: response.user_name} })
                this.showSuccess(this.$t('Your page has been created.'))
                this.resetErrors(this.error)
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('user_page', error.error.message);
				} else {
					this.showError(error.error);
				}
            } finally {
                this.loadingCreate = false
            }
        }
    }
}
</script>