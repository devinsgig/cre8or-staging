<template>
    <div class="leading-none">
        <TextareaPrimevue :maxlength="maxlength" ref="baseTextarea" :placeholder="placeholder" :value="modelValue" :autoResize="autoResize" :rows="rows" :autofocus="autofocus" :disabled="disabled" @input="onInput" :class="[`w-full resize-none ${classInput}`, error && 'p-invalid']" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import TextareaPrimevue from 'primevue/textarea'

export default {
    components: { TextareaPrimevue },
    props: {
        modelValue: {
			type: String,
			default: null
		},
		error: {
			type: String,
			default: null
		},
		placeholder: {
			type: String,
			default: null
		},
        autoResize: {
			type: Boolean,
			default: false
		},
        rows: {
			type: Number,
			default: 3
		},
		classInput: {
			type: String,
			default: ''
		},
		autofocus: {
            type: Boolean,
			default: false
        },
		maxlength: {
			type: Number,
			default: null
		},
		disabled: {
            type: Boolean,
            default: false
        }
    },
	mounted() {
		if(this.autofocus){
            this.$nextTick(() => this.$refs.baseTextarea.$el.focus())
        }
	},
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        }
    },
    emits: ['update:modelValue']
}
</script>