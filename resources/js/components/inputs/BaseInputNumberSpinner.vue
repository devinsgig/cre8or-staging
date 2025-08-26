<template>
    <InputNumber inputId="horizontal-buttons" showButtons buttonLayout="horizontal" class="w-full">
        <template #incrementbuttonicon>
            <span class="pi pi-plus" />
        </template>
        <template #decrementbuttonicon>
            <span class="pi pi-minus" />
        </template>
    </InputNumber>
</template>

<script>
import InputNumber from 'primevue/inputnumber';

export default {
    components: { InputNumber }
}
</script>