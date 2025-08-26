<template>
    <div class="w-full">
        <input 
            type="number" 
            inputmode="decimal" 
            step="0.01" 
            class="p-inputtext p-component w-full" 
            :value="modelValue" 
            @input="onInput"
            @keydown="preventCharacter" 
            :autofocus="autofocus" 
            :readonly="readonly" 
            :disabled="disabled" 
            :class="{'p-invalid': error}"
        >
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: {
			type: [String, Number, Object],
			default: null
		},
        readonly: {
            type: Boolean,
            default: false
        },
        autofocus: {
            type: Boolean,
			default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        error: {
			type: String,
			default: null
		}
    },
    methods:{
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        preventCharacter(event) {
            if (['e', 'E', '+', '-'].includes(event.key)) {
                event.preventDefault();
            }
        }
    },
    emits: ['update:modelValue']
}
</script>