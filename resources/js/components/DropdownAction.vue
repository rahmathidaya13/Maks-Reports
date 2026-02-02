<script setup>
import { computed } from 'vue';
import { hasPermission, hasRole } from "@/composables/useAuth";
const props = defineProps({
    item: {
        type: [Object, Array, String, Number, Boolean],
        required: true,
    },
    actions: {
        type: Array,
        required: true,
    },
    size: {
        type: String,
        default: 'lg',
    },
});

// Emit event
const emit = defineEmits();

/* Filter action berdasarkan permission */
const visibleActions = computed(() => {
    return props.actions.filter(action => {
        if (action.type === 'divider') return true;
        if (action.show === false) return false;
        if (action.permission && !hasPermission(action.permission)) return false;
        if (action.role && !hasRole(action.role)) return false;
        return true;
    })
})
</script>
<template>
    <div class="dropdown dropstart">
        <button :class="`btn btn-icon btn-${props.size} btn-light rounded-circle shadow-sm`" type="button"
            data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-h text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
            <template v-for="(action, index) in visibleActions" :key="index">

                <li v-if="action.type === 'divider' && index !== visibleActions.length - 1">
                    <hr class="dropdown-divider my-1" />
                </li>

                <li v-else>
                    <button type="button" class="dropdown-item rounded-2 py-2 d-flex align-items-center" :class="[action.color ? `text-${action.color}` : '',
                    action.class ? action.class : '']" @click.prevent="emit(action.action, props.item)">
                        <div v-if="action.icon" class="icon-box-xs rounded-1 me-2" :class="[
                            `bg-${action.color_icon || action.color} bg-opacity-10`,
                            `text-${action.color_icon || action.color}`
                        ]">
                            <i :class="[`${action.icon} text-${action.color_icon} fs-10`]"></i>
                        </div>

                        <span :class="action.color ? `text-${action.color}` : ''">{{ action.label }}</span>
                    </button>
                </li>

            </template>
        </ul>
    </div>
</template>
<style scoped>
.btn-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-icon:hover {
    background-color: #e9ecef;
    transform: rotate(90deg);
}

.icon-box-xs {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fs-10 {
    font-size: 0.8rem;
}

.dropdown-menu {
    min-width: 14rem;
    border-radius: 0.5rem;
    border: 1px solid #c9c9c9;
    overflow: hidden;
    box-shadow: 0 5px 8px rgba(85, 80, 80, 0.377);
    padding: 10px 0;

}

.dropdown-menu .dropdown-item {
    padding: 3px 15px;
    transition: background-color 0.2s ease-in-out, color 0.2;
}

.dropdown-menu .dropdown-item:hover {
    background-color: #2e57dd42;
    color: #ffffff;
    background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
}

.dropdown-menu .dropdown-header {
    background-color: #414141;
    font-weight: bold;
    font-size: 1rem;
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 0.25rem 0.25rem 0 0;
    text-align: center;
}
</style>
