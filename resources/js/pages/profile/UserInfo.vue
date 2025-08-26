<template>
    <div class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Name')}}</div>
            <div class="break-word">{{userInfo.name}}</div>
        </div>
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Username')}}</div>
            <div class="break-word">{{getUserName}}</div>
        </div>
        <div class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Joined')}}</div>
            <div class="break-word">{{ userInfo.joined_at }}</div>
        </div>
        <div v-if="userInfo.birthday" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Birthday')}}</div>
            <div>{{userInfo.birthday}}</div>
        </div>
        <div v-if="userInfo.gender" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Gender')}}</div>
            <div>{{userInfo.gender}}</div>
        </div>
        <div v-if="userInfo.phone_number" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Phone')}}</div>
            <div>{{userInfo.phone_number}}</div>
        </div>
        <div v-if="userInfo.address_full" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Location')}}</div>
            <div>{{userInfo.address_full}}</div>
        </div>
        <div v-if="userInfo.about" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('About')}}</div>
            <div><ContentHtml :content="userInfo.about" /></div>
        </div>
        <div v-if="userInfo.links" class="flex gap-4 mb-base-2">
            <div class="flex-150px">{{$t('Links')}}</div>
            <div>
                <a v-for="(link, index) in splitedLink" :key="index" target="_blank" :href="splitedLink[index]" class="block break-word mb-1">{{ splitedLink[index] }}</a>
            </div>
        </div>
    </div>
</template>

<script>
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Constant from '@/utility/constant'

export default {
    components: { ContentHtml },
    props: ['userInfo'],
    computed: {
        splitedLink(){
            return this.userInfo.links.split(" ")
        },
        getUserName(){
            return Constant.MENTION + this.userInfo.user_name;
        }
    },
    emits: ['change_tab', 'update_user_info']
}
</script>