<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button :class="importantActionClass" @click="reportUser(userInfo.id)">{{$t('Report')}}</button>
        <button v-if="user.id !== userInfo.id" :class="importantActionClass" @click="blockUser(userInfo)">{{$t('Block')}}</button>
        <button v-if="userInfo.check_privacy && userInfo.is_followed && userInfo.enable_notify" :class="normalActionClass" @click="stopNotificationUser(userInfo.id)">{{$t('Stop Notifications')}}</button>
        <button v-if="userInfo.check_privacy && userInfo.is_followed && !userInfo.enable_notify" :class="normalActionClass" @click="turnOnNotificationUser(userInfo.id)">{{$t('Turn on Notifications')}}</button>
        <button :class="normalActionClass" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import { toggleBlockUser } from '@/api/user'
import { toggleStopNotificationUser } from '@/api/follow'
import { useAuthStore } from '@/store/auth'
import ReportModal from '@/components/modals/ReportModal.vue'

export default {
    data() {
        return {
            userInfo: this.dialogRef.data.userInfo
        };
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user"]),
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods: {
        reportUser(userId) {
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: "users",
                    id: userId
                },
                props: {
                    header: this.$t("Report"),
                    class: "user-report-modal",
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            }), 300);
        },
        blockUser(user) {
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: user.is_page ? this.$t('Are you sure you want to block this page?') : this.$t('Are you sure you want to block this user?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await toggleBlockUser({
                            id: user.id,
                            action: "add"
                        });
                        this.$router.push({ name: "home" });
                    }
                    catch (error) {
                        this.showError(error.error)
                    }
                    checkPopupBodyClass()
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);
        },
        async stopNotificationUser(userId) {
            try {
                await toggleStopNotificationUser({
                    id: userId,
                    action: "remove"
                });
                this.userInfo.enable_notify = 0
                this.dialogRef.close();
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async turnOnNotificationUser(userId) {
            try {
                await toggleStopNotificationUser({
                    id: userId,
                    action: "add"
                });
                this.userInfo.enable_notify = 1
                this.dialogRef.close();
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        cancel() {
            this.dialogRef.close();
        }
    }
}
</script>

<style>

</style>