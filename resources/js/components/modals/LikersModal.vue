<template>
    <Error v-if="error">{{error}}</Error>
    <UsersList :loading="loading_users" :usersList="likersList" :has-load-more="loadmore_status" @load-more="getLikersList(dialogRef.data.itemType, dialogRef.data.itemId, page)" />
</template>

<script>
import { getLikersByItemId } from '@/api/likes';
import UsersList from '@/components/lists/UsersList.vue'
import Error from '@/components/utilities/Error.vue'

export default {
    components: { Error, UsersList },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            error: null,
            loadmore_status: false,
            loading_users: true,
            likersList: [],
            page: 1
        }
    },
    mounted(){
        if(this.dialogRef.data.itemId){
            this.getLikersList(this.dialogRef.data.itemType, this.dialogRef.data.itemId, this.page)
        }
    },
    methods: {
        async getLikersList(itemType, itemId, page) {
            try {
                const response = await getLikersByItemId(itemType, itemId, page)
                if(page === 1){
                    this.likersList = response.items
                }else{
                    this.likersList = window._.concat(this.likersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_users = false
            }
            catch (error) {
                this.error = error
                this.loading_users = false
            }
        }
    }
}
</script>