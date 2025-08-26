<template>
	<div v-if="appendTo === 'body'" class="inline-block">
        <button @click="toggleDropdownMenu">
            <slot name="dropdown-button">
                <BaseIcon name="more_horiz_outlined" size="20"/>
            </slot>
        </button>
        <OverlayPanel class="dropdown-menu-box bg-white text-main-color shadow-sidebar-more rounded-base dark:text-white dark:bg-slate-800" :style="{'marginStart': offsetX+'px', 'marginTop': offsetY+'px'}" ref="dropdown_menu" @click.stop="handleClickOverlayPanel" @hide="closeDropdownMenu">        
            <slot></slot>
        </OverlayPanel>
    </div>
	<div v-else>
		<div class="inline-block relative">
			<button ref="dropdown_btn" @click="toggleDropdownMenu">
				<slot name="dropdown-button">
					<BaseIcon name="more_horiz_outlined" size="20"/>
				</slot>
			</button>
			<div v-if="isShown" class="dropdown-menu-box bg-white text-main-color shadow-sidebar-more rounded-base dark:text-white dark:bg-slate-800 z-10 absolute" ref="dropdown_menu" v-click-outside="closeDropdownMenu" @click.stop="handleClickOverlayPanel"
				:style="{
					top: caretPosition.top !== null ? `${caretPosition.top}px` : 'auto',
					right: caretPosition.right !== null ? `${caretPosition.right}px` : 'auto',
					bottom: caretPosition.bottom !== null ? `${caretPosition.bottom}px` : 'auto',
					left: caretPosition.left !== null ? `${caretPosition.left}px` : 'auto',
				}">
				<slot></slot>
			</div>
		</div>
	</div>
</template>

<script>
import OverlayPanel from 'primevue/overlaypanel';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
	components: { OverlayPanel, BaseIcon },
	data() {
		return {
			isShown: false,
			caretPosition: {
				top: null,
				right: null,
				bottom: null,
				left: null
			},
			measuredWidth: null,
			measuredHeight: null
		}
	},
	props: {
		offsetX: {
            type: Number,
            default: 0
        },
        offsetY: {
            type: Number,
            default: 5
        },
		appendTo: {
            type: String,
            default: 'body'
        },
		closeWhenSelect: {
			type: Boolean,
			default: true
		}
	},
	methods: {
		measureDropdown() {
			const dropdown = this.$refs.dropdown_menu;
			this.measuredWidth = dropdown.offsetWidth;
			this.measuredHeight = dropdown.offsetHeight;
		},
		toggleDropdownMenu(e){
			if(this.appendTo === 'body'){
				this.$refs.dropdown_menu.toggle(e);
			} else {
				this.isShown = !this.isShown;
				this.$nextTick(() => {
					if (this.isShown && !this.measuredWidth) {
						this.measureDropdown();
					}
					this.updateCaretPosition();
				});
			}
			this.$emit('toggle_menu');
		},
		closeDropdownMenu(e) {
			if(this.appendTo === 'body'){
				this.$refs.dropdown_menu.hide(e);
			} else {
				this.isShown = false;
			}
			this.$emit('close_menu');
		},
		updateCaretPosition () {
			const dropdownButtonRect = this.$refs.dropdown_btn.getBoundingClientRect()

			// set X coordinate overlay panel
			if((window.innerWidth - dropdownButtonRect.left) > this.measuredWidth){
				this.caretPosition.left = this.offsetX
				this.caretPosition.right = null
			}else{
				this.caretPosition.right = this.offsetX
				this.caretPosition.left = null
			}

			// set Y coordinate overlay panel
			if((window.innerHeight - dropdownButtonRect.top) > this.measuredHeight){
				this.caretPosition.top = dropdownButtonRect.height + Number(this.offsetY)
				this.caretPosition.bottom = null
			}else{
				this.caretPosition.bottom = dropdownButtonRect.height + Number(this.offsetY)
				this.caretPosition.top = null
			}
		},
		handleClickOverlayPanel(){
			this.closeWhenSelect && this.closeDropdownMenu()
		}
	},
	emits: ['toggle_menu', 'close_menu']
}
</script>
