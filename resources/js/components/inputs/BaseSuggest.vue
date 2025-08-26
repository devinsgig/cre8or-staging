<template>
    <AutoComplete v-model="data" multiple @change="handleChangeValue" :inputClass="inputClass" :disabled="disableAutoComplete" :optionLabel="optionLabel" :suggestions="suggestions" @item-select="handleItemSelect" @item-unselect="handleItemUnselect" @complete="handleComplete($event)" :emptySearchMessage="emptySearchMessage" :placeholder="placeholder" class="w-full">
        <template #option="slotProps">
            <slot name="option" :option="slotProps.option"></slot>
        </template>
    </AutoComplete>
</template>

<script>
import AutoComplete from 'primevue/autocomplete'

export default {
    props: {
        modelValue: null,
        suggestions: {
            type: Array,
            default: () => []
        },
        optionLabel: [String, Function],
        optionValue: [String, Function],
        emptySearchMessage: {
            type: String,
            default: ''
        },
        multiple: {
            type: Boolean,
            default: false
        },
        placeholder: {
			type: String,
			default: null
		}
    },
    components: { AutoComplete },
    data(){
        return{
            data: this.modelValue,
            disableAutoComplete: false,
            inputClass: ''
        }
    },
    methods:{
        handleComplete(event){
            this.$emit('complete', event);
        },
        handleItemSelect(){
            if(!this.multiple){
                this.disableAutoComplete = true
                this.inputClass = 'hidden'
            }
            this.data = window._.uniqBy(this.data, this.optionLabel)
            this.$emit('item-select');
        },
        handleItemUnselect(){
            if(!this.multiple){
                this.disableAutoComplete = false
                this.inputClass = ''
            }
            this.$emit('item-unselect');
        },
        handleChangeValue(event){
            this.$emit('update:modelValue', event.value);
        }
    },
    emits: ['update:modelValue', 'complete', 'item-select', 'item-unselect']
}
</script>