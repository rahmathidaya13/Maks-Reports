<script setup>
// import moment from 'moment';
import { onMounted } from 'vue';

onMounted((edited) => {
    console.log(moment(edited).format('YYYY-MM-DD'));
    
})
</script>
<template>

</template>