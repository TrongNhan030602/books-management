<template>
    <div class="statistics-dashboard container mt-5">
        <h2 class="text-center mb-4">Thống kê mượn và trả sách</h2>
        <div class="row">
            <!-- Biểu đồ Sách Mượn -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Sách Mượn</h5>
                        <BorrowedChart />
                    </div>
                </div>
            </div>
            <!-- Biểu đồ Trạng Thái Sách -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Trạng Thái Giao Dịch Hiện Tại</h5>
                        <StatusChart />
                    </div>
                </div>
            </div>


        </div>
        <div class="row">
            <!--Biểu đồ thống kê giao dịch theo trạng thái -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Biểu đồ thống kê giao dịch theo trạng thái</h5>
                        <MostBorrowedChart />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import BorrowedChart from '../Admin/Chart/BorrowedChart.vue'
import MostBorrowedChart from '../Admin/Chart/MostBorrowedChart.vue'
import StatusChart from '../Admin/Chart/StatusChart.vue'

export default {
    name: 'StatisticsDashboard',
    components: {
        BorrowedChart,
        MostBorrowedChart,
        StatusChart,
    }
}
</script>

<style scoped>
.statistics-dashboard {
    padding: 0 0 20px 0;
}

h2 {
    font-family: var(--font-family-secondary);
    font-weight: var(--font-weight-bold);
    color: var(--primary-color);
}

.card {
    transition: transform 0.2s;
    background-color: #f8f9fa;
    /* Màu nền sáng cho card */
    border-radius: 10px;
    /* Bo góc cho card */
}

.card:hover {
    transform: scale(1.05);
}

.card-body {
    padding: 20px;
}

.card-title {
    font-family: var(--font-family-secondary);
    font-weight: var(--font-weight-bold);
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .card-body {
        padding: 15px;
    }
}
</style>
