<template>
   <InputSwitch :class="`base-switch-${size}`"/>
</template>

<script>
import InputSwitch from 'primevue/inputswitch';

export default {
    props: {
        size: {
            type: String, // sm, md
            default: 'sm'
        }
    },
    components: { InputSwitch }
}
</script>