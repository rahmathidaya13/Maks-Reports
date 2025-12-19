<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    name: {
        type: String,
        default: ''
    },

})
const emit = defineEmits(["update:modelValue"]);
const inputRef = ref(null);
let instance = null;

onMounted(() => {
    instance = flatpickr(inputRef.value, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",   // tampilan AM/PM
        time_24hr: false,
        defaultHour: 12,
        defaultMinute: 0,
        minuteIncrement: 1,

        // Saat user pilih waktu
        onChange: (selectedDates, dateStr) => {
            const date = selectedDates[0];
            if (!date) return;

            // Format simpan ke DB → "H:i:s"
            const formatted =
                date.toTimeString().split(" ")[0]; // contoh: "12:05:00"

            emit("update:modelValue", formatted);
        },
    });

    // Jika ada initial value, set ke input
    if (props.modelValue) {
        instance.setDate(props.modelValue, true, "H:i:s");
    }
});

watch(() => props.modelValue, (val) => {
    if (instance && val) {
        instance.setDate(val, true, "H:i:s");
    }
});

onBeforeUnmount(() => {
    if (instance) instance.destroy();
});
</script>
<template>
    <input type="text" ref="inputRef" class="form-control text-bg-grey" :name="name" :id="name" :value="modelValue">
</template>
