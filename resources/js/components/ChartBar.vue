<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import {
    BarController, Chart,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
)

const props = defineProps({
    labels: {
        type: Array,
        required: true
    },
    data: {
        type: [Array, Object],
        required: true
    },
    options: {
        type: [Object, Array],
        required: true
    },
    label: {
        type: String,
        default: 'Api Data'
    },
    height: {
        type: Number,
        default: 400
    },
    type: {
        type: String,
        default: 'bar'
    }
})
const chartCanvas = ref(null)
let chartInstance = null;

const renderChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    chartInstance = new Chart(chartCanvas.value, {
        type: props.type,
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: props.label,
                    data: props.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 32
                }
            ]
        },
        options: props.options
    });
}

onMounted(renderChart)
watch(
    () => [props.labels, props.data],
    renderChart,
    { deep: true }
)

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy()
    }
})
</script>
<template>
    <div :style="{ height: height + 'px' }">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>