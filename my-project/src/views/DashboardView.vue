<template>
  <div class="dashboard-view">
    <SalesChart
      v-if="chartLoaded"
      :labels="labels"
      :data="counts"
      title="Users by Role"
    />
    <p v-else class="loading-text">Loading chart data...</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/axios' // ✅ Custom axios instance with interceptors
import SalesChart from '@/components/SalesChart.vue'

const labels = ref([])
const counts = ref([])
const chartLoaded = ref(false)

onMounted(async () => {
  try {
    const response = await api.get('/dashboard/user-role-stats')
    const result = response.data

    // Example expected response: [{ label: 'Admin', count: 5 }, { label: 'User', count: 10 }]
    labels.value = result.map(item => item.label)
    counts.value = result.map(item => item.count)

    chartLoaded.value = true
  } catch (error) {
    console.error('Failed to fetch chart data:', error)
  }
})
</script>

<style scoped>
.dashboard-view {
  max-width: 700px;
  margin: 0 auto;
  padding: 20px;
}

.loading-text {
  text-align: center;
  margin-top: 50px;
  font-size: 1.1rem;
  color: #888;
}
</style>
