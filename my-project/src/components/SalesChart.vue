<template>
  <div class="bar-chart">
    <Bar :data="chartData" :options="chartOptions" />

    <!-- Summary below the chart -->
    <ul class="summary-list" v-if="props.labels && props.data">
      <li v-for="(label, index) in props.labels" :key="index">
        {{ label }}: {{ props.data[index] }}
      </li>
      <li><strong>Total Users:</strong> {{ totalUsers }}</li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart,
  BarElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Title, Tooltip, Legend)

const props = defineProps({
  labels: Array,
  data: Array,
  title: String,
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'Count',
      backgroundColor: '#42b983',
      data: props.data,
    },
  ],
}))

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: true, text: props.title },
  },
}

const totalUsers = computed(() =>
  (props.data && props.data.length) ? props.data.reduce((acc, val) => acc + val, 0) : 0
)
</script>
<style scoped>
.bar-chart {
  max-width: 600px;
  margin: 20px auto;
  font-family: 'Helvetica Neue', Arial, sans-serif;
  color: #2c3e50;
}

.summary-list {
  list-style: none;
  padding: 0;
  margin-top: 25px;
  background: #f7f9fc;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
  overflow: hidden;
}

.summary-list li {
  display: flex;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #e1e8f0;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.summary-list li:hover {
  background-color: #e8f0fe;
}

.summary-list li:last-child {
  font-weight: 700;
  color: #1e40af;
  border-bottom: none;
  background: #dbeafe;
  font-size: 1.15rem;
}

.summary-list li strong {
  color: #1e40af;
}
</style>
