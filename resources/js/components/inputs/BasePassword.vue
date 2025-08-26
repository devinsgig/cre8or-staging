<template>
    <div>
        <div class="p-input-icon-right" :class="{'p-input-icon-left': left_icon, 'p-disabled': disabled}">
            <BaseIcon v-if="left_icon" :name="left_icon" size="16" class="p-inputtext-icon text-input-icon-color dark:text-slate-400"/>
            <InputText ref="basePassword" :type="inputType" :placeholder="placeholder" :value="modelValue" :autofocus="autofocus" :autocomplete="autocomplete" :disabled="disabled" @change="onInput" class="w-full" :class="{'p-invalid': error}" />
            <BaseIcon v-if="right_icon" :name="passwordIcon" size="16" @click="showHiddenPassword()" class="p-inputtext-icon text-input-icon-color dark:text-slate-400 cursor-pointer" />
        </div>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import InputText from 'primevue/inputtext';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { InputText, BaseIcon },
    data() {
        return {
            passwordIcon: 'eye_slash',
            inputType: 'password'
        }
    },
    props: {
        modelValue: {
			type: String,
			default: null
		},
		error: {
			type: String,
			default: null
		},
        left_icon: {
			type: [String, Boolean],
			default: 'key'
		},
        right_icon: {
			type: Boolean,
			default: true
		},
		placeholder: {
			type: String,
			default: null
		},
        autofocus: {
            type: Boolean,
			default: false
        },
        autocomplete: {
            type: String,
			default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    mounted() {
		if(this.autofocus){
            this.$nextTick(() => this.$refs.basePassword.$el.focus())
        }
	},
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        showHiddenPassword() {
            if (this.inputType == 'password') {
                this.inputType = 'text'
                this.passwordIcon = 'eye'
            } else {
                this.inputType = 'password'
                this.passwordIcon = 'eye_slash'
            }
        }
    },
    emits: ['update:modelValue']
}
</script>