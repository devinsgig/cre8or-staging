<template>
    <div class="w-full">
        <Dropdown class="bg-input-background-color text-input-text-color border border-input-border-color w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:placeholder:text-slate-300" v-model="value" :options="options" :optionLabel="optionLabel" :optionValue="optionValue" :disabled="disabled" @change="handleOnChange($event)" :emptyMessage="emptyMessage ? emptyMessage : $t('No results found')" :class="{'p-invalid': error}" :placeholder="placeholder" :filter="filter" :resetFilterOnHide="resetFilterOnHide" @filter="handleFilter($event)" @before-show="handleBeforeShow($event)" @show="handleShow($event)" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import Dropdown from 'primevue/dropdown'

export default {
    components: { Dropdown },
    props: {
        modelValue: {
			type: [String, Number],
			default: null
		},
        error: {
            type: String,
			default: null
		},
        disabled: {
            type: Boolean,
            default: false
        },
        options: {
            type: Array,
            default: null
        },
        optionLabel: [String, Function],
        optionValue: [String, Function],
        placeholder: {
			type: String,
			default: null
		},
        filter: {
            type: Boolean,
            default: false
        },
        resetFilterOnHide: {
            type: Boolean,
            default: false
        },
        emptyMessage: {
            type: String,
            default: ''
        }
    },
    data(){
        return {
            value: this.modelValue
        }
    },
    watch:{
        modelValue(newValue){
            this.value = newValue
        }
    },
    methods: {
        handleOnChange(event) {
            this.value = event.value;
            this.$emit('update:modelValue', event.value);
            this.$emit('change', event.value);
        },
        handleFilter(event){
            this.$emit('filter', event);
        },
        handleShow(event){
            this.$emit('show', event);
        },
        handleBeforeShow(event){
            this.$emit('before-show', event);
        }
    },
    emits: ['update:modelValue', 'change', 'filter', 'show', 'before-show']
}
</script>