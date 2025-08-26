<template>
    <div class="w-full">
        <div :class="{'p-input-icon-left': left_icon, 'p-input-icon-right': right_icon}">
            <BaseIcon v-if="left_icon" :name="left_icon" size="16" class="p-inputtext-icon text-input-icon-color dark:text-slate-400"/>
            <InputText ref="baseInputText" :placeholder="placeholder" :value="modelValue" :autofocus="autofocus" :readonly="readonly" :disabled="disabled" @input="onInput" @focus="onFocus" :autocomplete="autocomplete" :class="{'p-invalid': error}" v-tooltip.focus.bottom="(isMobile && tooltip_mobile) ? tooltip_mobile : ''" />
            <BaseIcon v-if="right_icon" :name="right_icon" size="16" class="p-inputtext-icon text-input-icon-color dark:text-slate-400" />
        </div>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import InputText from 'primevue/inputtext';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAppStore } from '../../store/app'

export default {
    components: { InputText, BaseIcon },
    props: {
        modelValue: {
			type: [String, Number],
			default: null
		},
		error: {
			type: String,
			default: null
		},
        left_icon: {
			type: String,
			default: null
		},
        right_icon: {
			type: String,
			default: null
		},
		placeholder: {
			type: String,
			default: null
		},
        autocomplete: {
            default: false
        },
        readonly: {
            type: Boolean,
            default: false
        },
        autofocus: {
            type: Boolean,
			default: false
        },
        tooltip_mobile: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        ...mapState(useAppStore, ['isMobile'])
    },
    mounted() {
		if(this.autofocus){
            this.$nextTick(() => this.$refs.baseInputText.$el.focus())
        }
	},
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        onFocus(){
            this.$emit('focus');
        }
    },
    emits: ['update:modelValue', 'focus']
}
</script>