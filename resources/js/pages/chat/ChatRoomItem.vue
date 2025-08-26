<template>
    <div @click="showRoom" class="room_items flex items-center px-2 py-base-2 rounded-xl cursor-pointer md:hover:bg-gray-6 md:dark:hover:bg-dark-body" :class="room_id == chatRoom.id ? 'room_items_active bg-light-blue dark:bg-dark-message': ''">
        <div class="relative">
            <Avatar v-for="member in getOthersMemberInRoom(chatRoom.members)" :key="member.id" :user="member" :activePopover="false" :router="false" />
            <div v-if="chatRoom.is_online" class="absolute bottom-0 right-1 border border-white block w-base-2 h-base-2 bg-base-green rounded-full"></div>
        </div>
        <div class="flex-1 ms-base-2 me-4 min-w-0">
            <UserName v-for="member in getOthersMemberInRoom(chatRoom.members)" :key="member.id" :user="member" :activePopover="false" :router="false" class="room_items_title_text" :class="{'font-bold': chatRoom.message_count > 0 }"/>
            <div v-if="chatRoom.last_message" class="room_items_date_text flex text-xs text-sub-color whitespace-nowrap dark:text-slate-400" :class="{'font-bold': chatRoom.message_count > 0 }">
                <span class="truncate">{{chatRoom.last_message.is_delete ? $t('Message has been unsent') : chatRoom.last_message.short_content}}</span>
                <span class="mx-1">.</span>
                <span>{{chatRoom.last_message.created_at_short}}</span>
            </div>          
        </div>
        <div v-if="showAction" class="flex items-center gap-base-1">
            <template v-if="chatRoom.enable && room_id != chatRoom.id">                    
                <DropdownMenu class="flex items-center justify-center" :offsetY="0">
                    <template v-slot:dropdown-button>
                        <button class="flex items-center justify-center w-8 h-8" name="chatOptions">                     
                            <BaseIcon name="more_horiz_outlined" size="20" class="room_items_dropdown_icon chat-room-options text-sub-color dark:text-slate-400" />
                        </button>
                    </template> 
                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200 w-40">
                        <li v-if="chatRoom.message_count > 0" class="hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                            <button @click="this.markSeen(chatRoom.id)" class="block w-full text-start py-2 px-4">{{$t('Mark as Read')}}</button>
                        </li>
                        <li v-else class="hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                            <button @click="this.markUnseen(chatRoom.id)" class="block w-full text-start py-2 px-4">{{$t('Mark as Unread')}}</button>
                        </li>                                
                    </ul>
                </DropdownMenu>
            </template>
            <BaseIcon v-if="!chatRoom.enable_notify" name="bell_slash" size="16" class="align-middle" />
            <span v-if="chatRoom.message_count > 0" class="room_items_dot inline-block h-base-2 w-base-2 rounded-full bg-primary-color dark:bg-dark-primary-color"></span>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { changeUrl } from '@/utility';
import { useChatStore } from '@/store/chat';
import { useAuthStore } from '@/store/auth';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { BaseIcon, DropdownMenu, Avatar, UserName },
    props: {
		chatRoom: {
			type: Object,
			default: null
		},
        room_id: {
			type: Number,
			default: null
		},
        showAction: {
			type: Boolean,
			default: true
		},
        hasRouter: {
			type: Boolean,
			default: true
		},
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        ...mapActions(useChatStore, ['markSeenRoom', 'markUnseenRoom']),
        showRoom(e){
            if(e.target.getAttribute('name') == 'chatOptions'){
                return 
            }
            var name = 'chat'
            if (this.$route.name == 'chat_requests')  {
                name = 'chat_requests';
            }
            let roomUrl = this.$router.resolve({
                name: name,
                params: {'room_id': this.chatRoom.id}
            });
            if(this.hasRouter){
                changeUrl(roomUrl.fullPath)
            }
            this.$emit('updateRoomId', this.chatRoom.id)
        },
        async markSeen(roomId){
            try {
                await this.markSeenRoom(roomId)
            } catch (error) {
                this.showError(error.error)
            }
        },
        async markUnseen(roomId){
            try {
                await this.markUnseenRoom(roomId)
            } catch (error) {
                this.showError(error.error)
            }
        }
        
    },
    emits: ['updateRoomId']
}
</script>