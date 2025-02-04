<template>
    <div class="chart-container">
        <Bar v-if="!isLoading && !error" :data="chartData" :options="chartOptions" />
        <div v-if="isLoading">Đang tải dữ liệu...</div>
        <div v-if="error" class="error-message">{{ error }}</div>
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
import axios from '../../../axios'

// Register chart.js components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

export default {
    name: 'BorrowedChart',
    components: {
        Bar
    },
    data() {
        return {
            chartData: {
                labels: [], // Tháng từ API
                datasets: [
                    {
                        label: 'Số sách mượn',
                        data: [],
                        backgroundColor: '#42A5F5',
                        borderColor: '#1E88E5',
                        borderWidth: 1
                    }
                ]
            },
            chartOptions: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            isLoading: true,
            error: null
        }
    },
    mounted() {
        this.fetchData()
    },
    methods: {
        async fetchData() {
            try {
                const response = await axios.get('/report/transaction-stats');
                const stats = response.data.data;

                this.chartData.labels = stats.transactions_by_month.map(item => `Tháng ${item.month}`);
                this.chartData.datasets[0].data = stats.transactions_by_month.map(item => item.total);

                this.isLoading = false;
            } catch (error) {
                this.error = 'Không thể tải dữ liệu. Vui lòng thử lại sau.';
                this.isLoading = false;
                console.error('Error fetching data:', error);
            }
        }
    }
}
</script>

<style scoped>
.chart-container {
    background: var(--white-color);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px var(--box-shadow-color);
}

.error-message {
    color: red;
    text-align: center;
    margin-top: 20px;
}
</style>
