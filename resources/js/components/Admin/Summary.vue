<template>
    <div class="summary">
        <!-- Thẻ thống kê -->
        <div class="summary-cards row g-4">
            <div class="col-md-4">
                <div class="card shadow border-0 statistic-card bg-primary text-white">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-book fa-3x me-3"></i>
                        <div>
                            <h5 class="card-title mb-1">Sách Hiện Có</h5>
                            <p class="card-text h4">{{ report.total_books }} / {{ report.total_books_init }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow border-0 statistic-card bg-success text-white">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-users fa-3x me-3"></i>
                        <div>
                            <h5 class="card-title mb-1">Độc Giả Hiện Có</h5>
                            <p class="card-text h4">{{ report.active_users }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow border-0 statistic-card bg-info text-white">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-book-reader fa-3x me-3"></i>
                        <div>
                            <h5 class="card-title mb-1">Sách Đang Mượn</h5>
                            <p class="card-text h4">{{ report.borrowed_books }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cảnh báo yêu cầu mượn -->
        <div v-if="report.pending_requests > 0" class="alert alert-warning mt-4 d-flex align-items-center">
            <i class="fas fa-exclamation-circle fa-2x me-2"></i>
            <div>
                Có <strong>{{ report.pending_requests }}</strong> yêu cầu mượn sách chưa được duyệt!
                <router-link class="alert-link ms-2" to="/borrow-management">Duyệt ngay</router-link>
            </div>
        </div>

        <!-- Cảnh báo yêu cầu trả (nếu có) -->
        <div v-if="report.return_requests > 0" class="alert alert-info mt-2 d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-2"></i>
            <div>
                Có <strong>{{ report.return_requests }}</strong> yêu cầu trả sách cần duyệt!
                <router-link class="alert-link ms-2" to="/return-management">Xem ngay</router-link>
            </div>
        </div>
    </div>
</template>

<script>
import axios from '../../axios';
import EventBus from '../../eventBus';

export default {
    name: 'Summary',
    data() {
        return {
            report: {
                total_books: 0,
                total_books_init: 0,
                active_users: 0,
                borrowed_books: 0,
                pending_requests: 0,
                return_requests: 0,
            },
        };
    },
    mounted() {
        this.fetchReportData();
        EventBus.$on('update-summary', this.fetchReportData);
    },
    methods: {
        async fetchReportData() {
            try {
                const response = await axios.get('/report/summary');
                if (response.data.success) {
                    this.report = response.data.data;
                } else {
                    console.error('Không thể tải báo cáo:', response.data.message);
                }
            } catch (error) {
                console.error('Lỗi khi tải dữ liệu báo cáo:', error);
            }
        },
    },
};
</script>

<style scoped>
.summary {
    margin: 20px 0;
}

/* Card styles */
.statistic-card {
    border-radius: 12px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bg-primary {
    background-color: #26867e !important;
}

.bg-info {
    background-color: #3120b4 !important;

}

.statistic-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* Title and text in the cards */
.card-title {
    font-size: 1.1rem;
    font-weight: 600;
}

.card-text {
    font-size: 2rem;
    font-weight: bold;
}

/* Alert styles */
.alert {
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 500;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.alert-link {
    font-weight: bold;
    color: var(--bs-primary);
    text-decoration: underline;
}

.alert-link:hover {
    color: var(--bs-dark);
}
</style>
