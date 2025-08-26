<template>
	<DropdownMenu v-if="!isMobile" ref="emojiDropdown" :appendTo="appendTo" :close-when-select="false" @toggle_menu="openEmoji" @close_menu="closeEmoji">
		<template v-slot:dropdown-button>
			<BaseIcon name="emoji" :size="size" class="emoji-picker" v-tooltip.top="{value: tip, showDelay: 1000}" />
		</template>
		<div class="emoji-picker-box flex flex-col w-80 h-72">
			<div class="emoji-lists rounded-lg overflow-auto scrollbar-thin p-4 z-50">
				<div class="emoji-picker-box-title flex flex-col mb-1 text-gray-400" v-for="category in categories" :key="`category_${category}`">
					<span class="font-medium mb-4">{{ $t(category) }}</span>
					<div class="flex flex-wrap">
						<button class="border-0 text-2xl mb-2 w-2/12" @click="handleEmojiClick($event, emoji)" v-for="(emoji, name, index) in category_emojis(category)" :key="`emoji_${index}`" v-bind:title="name">
							{{ emoji}}
						</button>
					</div>
				</div>
			</div>
		</div>
	</DropdownMenu>
</template>

<script>
import { mapState } from 'pinia'
import data from '@/data/emojis-data.js';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import { useAppStore } from '@/store/app';

export default {
	name: 'EmojiPicker',
	components: {
		BaseIcon, DropdownMenu
	},
	props: {
		appendTo: {
            type: String,
            default: 'body'
        },
		size: {
            type: String,
			default: '24'
        },
		tip: {
            type: String,
            default: ''
        }
	},
	computed: {
		...mapState(useAppStore, ['isMobile']),
		categories() {
			this.$t('Frequently used')
			this.$t('Nature')
			this.$t('Objects')
			this.$t('Places')
			this.$t('Symbols')
			return Object.keys(data);
		},
		category_emojis: () => (category) => {
			return data[category];
		}
	},
	methods: {
		handleEmojiClick(e, emoji){
			e.preventDefault();
			this.$emit('emoji_click', emoji);
		},
		openEmoji(){
			this.$emit('open_emoji');
		},
		closeEmoji() {
			this.$emit('close_emoji');
		},
		triggerClose(){
			this.$refs.emojiDropdown?.closeDropdownMenu()
		}
	},
	emits: ['emoji_click', 'open_emoji', 'close_emoji']
}
</script>
