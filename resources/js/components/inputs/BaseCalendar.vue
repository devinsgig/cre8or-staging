<template>
    <div class="w-full">
        <Calendar v-model="value" @date-select="selectDate" @input="inputDate" :selectionMode="selectionMode" :manualInput="manualInput" :dateFormat="dateFormat" :timeOnly="timeOnly" :placeholder="placeholder" :showTime="showTime" :hourFormat="hourFormat" :stepHour="stepHour" :stepMinute="stepMinute" :stepSecond="stepSecond" :showSeconds="showSeconds" :disabled="disabled" :showButtonBar="showButtonBar" @today-click="selectToday" @clear-click="clearDate" class="w-full" :class="{'p-invalid': error}" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import Calendar from 'primevue/calendar'

export default {
    components: { Calendar },
    props: {
        modelValue: {
			type: [String, Number, Date],
			default: null
		},
        disabled: {
            type: Boolean,
            default: false
        },
		error: {
			type: String,
			default: null
		},
        timeOnly: {
            type: Boolean,
            default: false
        },
        dateFormat: {
			type: String,
			default: null
		},
        placeholder: {
            type: [String, Number],
			default: null
        },
        manualInput: {
            type: Boolean,
            default: true
        },
        showTime: {
            type: Boolean,
            default: false
        },
        hourFormat: {
            type: String,
            default: '24'
        },
        stepHour: {
            type: Number,
            default: 1
        },
        stepMinute: {
            type: Number,
            default: 1
        },
        stepSecond: {
            type: Number,
            default: 1
        },
        showSeconds: {
            type: Boolean,
            default: false
        },
        selectionMode: {
            type: String,
            default: "single"
        },
        showButtonBar: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return {
            value: this.modelValue
        }
    },
    methods: {
        selectDate(value){
            this.$emit('update:modelValue', value);
        },
        inputDate(event){
            this.$emit('update:modelValue', event.target.value);
        },
        selectToday(value){
            this.$emit('update:modelValue', value)
        },
        clearDate(){
            this.$emit('update:modelValue', '')
        }
    }
}
</script>