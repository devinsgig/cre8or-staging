<template>
    <div class="base-input-tel w-full">
        <VueTelInput v-model="inputVal" :inputOptions="inputOptions" :value="modelValue" mode="international" @input="onInput" @focus="onFocus" @blur="onFocus" @enter="onFocus" @validate="onValidate" @country-changed="onFocus" ></VueTelInput>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

export default {
    components: { VueTelInput },
    props: {
        modelValue: {
			type: String,
			default: null
		},
		error: {
			type: String,
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
        placeholder: {
			type: String,
			default: null
		}
    },
    data(){
        return{
            inputVal: this.modelValue,
            inputOptions:{
                placeholder: this.placeholder,
                autofocus: this.autofocus
            }
        }
    },
    watch: {
        modelValue(newValue){
            this.inputVal = newValue
        }
    },
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        },
        onFocus(){
            this.$emit('focus');
        },
        onValidate(data){
            this.$emit('validate', data);
        }
    },
    emits: ['update:modelValue', 'focus', 'validate']
}
</script>
