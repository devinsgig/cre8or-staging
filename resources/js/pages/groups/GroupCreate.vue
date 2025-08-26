<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Create New Group') }}</h3>
        </div>
        <form @submit.prevent="handleCreateGroup">
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Group Name') }}</label>
                <BaseInputText v-model="name" :placeholder="$t('Please enter group name')" :error="error.name"/>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Description') }}</label>
                <BaseTextarea v-model="description" :placeholder="$t('Enter group description')" :error="error.description" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Categories') }}</label>
                <BaseSelectCategories v-model="categories" type="group" :error="error.categories" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Hashtags') }}</label>
                <BaseSelectHashtags v-model="hashtags" :error="error.hashtags" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Group Type') }}</label>
                <div class="flex flex-col gap-1">
                    <div v-for="type in typesList" :key="type.value" class="flex gap-3">
                        <BaseRadio v-model="groupType" :inputId="type.value.toString()" name="group_type" :value="type.value" :error="error.type" />
                        <label :for="type.value">
                            <div class="leading-5">{{ type.label }}</div>
                            <div class="text-sub-color text-base-xs dark:text-slate-400">{{ type.description }}</div>
                        </label>
                    </div>
                </div>
                <small v-if="error" class="p-error">{{error.type}}</small>
            </div>
            <BaseButton class="w-full" :loading="loadingCreate">{{ $t('Continue') }}</BaseButton>
        </form>
    </div>
</template>

<script>
import { storeGroup } from '@/api/group'
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseSelectCategories from '@/components/inputs/BaseSelectCategories.vue'
import BaseSelectHashtags from '@/components/inputs/BaseSelectHashtags.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import Constant from '@/utility/constant'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea, BaseSelectCategories, BaseSelectHashtags, BaseRadio },
    data(){
        return{
            loadingCreate: false,
            name: null,
            description: null,
            categories: [],
            hashtags: [],
            groupType: 0,
            typesList: [
                { value: 0, label: this.$t('Public Group'), description: this.$t('Everyone can join')},
                { value: 1, label: this.$t('Closed Group'), description: this.$t("Everyone can join but need approval from admins. Private groups can't be changed to public to protect the privacy of group members")}
            ],
            error: {
				name: null,
                description: null,
                categories: null,
                hashtags: null,
                type: null
            }
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        async handleCreateGroup(){
            this.loadingCreate = true
            try {
                const response = await storeGroup({
                    name: this.name,
                    user_name: this.user_name,
                    description: this.description,
                    categories: this.categories,
                    hashtags: this.hashtags.map(hashtag => hashtag.name),
                    type: this.groupType
                })
                if(response.status === 'pending'){
                    this.$router.push({ name: 'list_groups' })
                    this.showSuccess(this.$t('Your group is pending for approval.'))
                } else {
                    this.$router.push({ name: 'group_profile', params: { id: response.id, slug: response.slug } })
                    this.showSuccess(this.$t('Your group has been created.'))
                }
                this.resetErrors(this.error)
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('group', error.error.message);
				} else {
                    this.handleApiErrors(this.error, error)
                }
            } finally {
                this.loadingCreate = false
            }
        }
    }
}
</script>