<template>
    <div class="chart-container">
        <Bar v-if="!isLoading && !error" :data="chartData" :options="chartOptions" />
        <div v-if="isLoading" class="loading-message">Đang tải dữ liệu...</div>
        <div v-if="error" class="error-message">{{ error }}</div>
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, TimeScale } from 'chart.js';
import 'chartjs-adapter-date-fns';  // Import date adapter
import axios from '../../../axios';

// Đăng ký các thành phần của chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, TimeScale);

export default {
    name: 'TransactionStatusChart',
    components: {
        Bar
    },
    data() {
        return {
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Pending',
                        data: [],
                        backgroundColor: this.generateGradient('#FF9F40'),
                        borderColor: '#FF9F40',
                        borderWidth: 1,
                        barThickness: 30  // Tăng độ rộng cột cho "Pending"
                    },
                    {
                        label: 'Approved',
                        data: [],
                        backgroundColor: this.generateGradient('#36A2EB'),
                        borderColor: '#36A2EB',
                        borderWidth: 1,
                        barThickness: 30  // Tăng độ rộng cột cho "Approved"
                    },
                    {
                        label: 'Returned',
                        data: [],
                        backgroundColor: this.generateGradient('#9966FF'),
                        borderColor: '#9966FF',
                        borderWidth: 1,
                        barThickness: 30  // Tăng độ rộng cột cho "Returned"
                    },
                    {
                        label: 'Completed',
                        data: [],
                        backgroundColor: this.generateGradient('#FF6384'),
                        borderColor: '#FF6384',
                        borderWidth: 1,
                        barThickness: 30  // Tăng độ rộng cột cho "Completed"
                    },
                    {
                        label: 'Cancelled',
                        data: [],
                        backgroundColor: this.generateGradient('#FF9F40'),
                        borderColor: '#FF9F40',
                        borderWidth: 1,
                        barThickness: 30  // Tăng độ rộng cột cho "Cancelled"
                    }
                ]
            },
            chartOptions: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 14
                            }
                        },
                        title: {
                            display: true,
                            text: 'Số lượng giao dịch',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            color: '#333'
                        }
                    },
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'PP', // Định dạng tooltip
                            displayFormats: {
                                day: 'dd/MM', // Hiển thị ngày theo định dạng dd/MM
                            },
                            timezone: 'Asia/Ho_Chi_Minh',  // Chỉnh múi giờ theo yêu cầu (hoặc 'UTC' nếu cần)
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 10
                        },
                        title: {
                            display: true,
                            text: 'Ngày giao dịch',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            color: '#333'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        cornerRadius: 5,
                        padding: 10,
                        callbacks: {
                            label: function (context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value}`;
                            },
                            title: function (context) {
                                return `Ngày: ${context[0].label}`;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold',
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutBounce'
                }
            },

            isLoading: true,
            error: null
        };
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const response = await axios.get('/report');
                const data = response.data;

                // Cập nhật labels với ngày tháng từ API
                this.chartData.labels = data.labels.map(date => new Date(date));

                // Cập nhật dữ liệu cho các dataset
                data.datasets.forEach(dataset => {
                    const status = dataset.label;
                    const statusData = dataset.data;
                    switch (status) {
                        case 'Pending':
                            this.chartData.datasets[0].data = statusData;
                            break;
                        case 'Approved':
                            this.chartData.datasets[1].data = statusData;
                            break;
                        case 'Returned':
                            this.chartData.datasets[2].data = statusData;
                            break;
                        case 'Completed':
                            this.chartData.datasets[3].data = statusData;
                            break;
                        case 'Cancelled':
                            this.chartData.datasets[4].data = statusData;
                            break;
                        default:
                            break;
                    }
                });

                this.isLoading = false;
            } catch (error) {
                this.error = 'Không thể tải dữ liệu. Vui lòng thử lại sau.';
                this.isLoading = false;
                console.error('Error fetching data:', error);
            }
        },
        generateGradient(color) {
            const ctx = document.createElement('canvas').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color);
            gradient.addColorStop(1, 'rgba(255, 255, 255, 0.2)');
            return gradient;
        }
    }
};
</script>

<style scoped>
.chart-container {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    min-height: 500px;
    min-width: 600px;
    overflow-x: auto;
}

.loading-message {
    color: #3498db;
    font-size: 16px;
    text-align: center;
    margin-top: 20px;
}

.error-message {
    color: red;
    text-align: center;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .chart-container {
        min-width: 100%;
    }
}
</style>
