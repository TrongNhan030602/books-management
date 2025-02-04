<template>
    <div class="chart-container">
        <Pie v-bind="chartProps" v-if="chartDataIsReady" />
    </div>
</template>

<script>
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js'
import axios from '../../../axios'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

export default {
    name: 'StatusChart',
    components: { Pie },
    data() {
        return {
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Trạng thái giao dịch',
                        data: [],
                        backgroundColor: [
                            '#FFEB3B',  // pending (Vàng)
                            '#42A5F5',  // approved (Xanh dương)
                            '#66BB6A',  // returned (Xanh lá)
                            '#FF7043',  // cancelled (Cam đậm)
                            '#8BC34A'   // completed (Xanh lá đậm)
                        ]
                    }
                ]
            },
            chartOptions: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        }
    },
    computed: {
        chartDataIsReady() {
            return this.chartData.labels.length > 0 && this.chartData.datasets[0].data.length > 0;
        },
        chartProps() {
            return {
                data: this.chartData,
                options: this.chartOptions
            };
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            const statusMap = {
                'pending': 'Chờ duyệt mượn',
                'approved': 'Đã duyệt mượn',
                'returned': 'Chờ duyệt trả',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy'
            };

            axios
                .get('/report/transaction-stats')
                .then(response => {
                    const stats = response.data.data;

                    this.chartData.labels = stats.transactions_by_status.map(item => statusMap[item.status] || item.status);
                    this.chartData.datasets[0].data = stats.transactions_by_status.map(item => item.total);

                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }
    }
}
</script>

<style scoped>
.chart-container {
    width: 100%;
    max-width: 500px;
    height: 400px;
    margin: auto;
    background: var(--white-color);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px var(--box-shadow-color);
}
</style>
