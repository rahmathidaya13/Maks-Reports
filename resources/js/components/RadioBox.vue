<script setup>
import { computed } from "vue";

const emit = defineEmits(["update:checked"]);
const props = defineProps({
    checked: [Boolean, String, Number],
    value: [Boolean, String, Number],
    name: String,
    label: String,
    inputClass: String
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit("update:checked", val);
    }
});

// ID unik per radio biar gak bentrok
const inputId = computed(() => `${props.name}-${props.value}`);
</script>

<template>
  <div class="form-check">
    <input
      class="form-check-input"
      type="radio"
      :name="name"
      :id="inputId"
      :value="value"
      v-model="proxyChecked"
      :class="inputClass"
    />
    <label class="form-check-label fw-bold" :for="inputId">
      <slot>{{ label }}</slot>
    </label>
  </div>
</template>
