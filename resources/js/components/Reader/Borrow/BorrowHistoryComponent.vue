<template>
    <div id="history-borrow" class="borrow-history-section container mt-5">
        <h2 class="text-center section-title">Lịch sử mượn sách</h2>
        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>
        <!-- Sort and Filter Controls -->
        <div class="filter-controls d-flex justify-content-end mb-4">
            <select v-model="sortBy" class="form-select form-select-sm me-3" @change="sortHistory">
                <option value="borrow_date">Ngày mượn</option>
                <option value="return_date">Ngày trả</option>
                <option value="title">Tên sách</option>
            </select>
            <input type="text" class="form-control form-control-sm" v-model="filterText"
                placeholder="Lọc theo tên sách..." @input="filterHistory" />
        </div>

        <!-- Borrow History List -->
        <div class="row borrow-history">
            <div v-if="filteredHistory.length === 0" class="alert alert-info text-center" role="alert">
                Không có lịch sử mượn sách.
            </div>
            <div v-for="transaction in paginatedHistory" :key="transaction.id" class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <img :src="`${host}/storage/${transaction.book.cover_image}`" alt="Bìa sách" class="card-img-top" />
                    <div class="card-body">
                        <h5 class="card-title">{{ transaction.book.title }}</h5>
                        <p class="card-text">Tác giả: <strong>{{ transaction.book.author }}</strong></p>
                        <p class="card-text">Ngày mượn: <strong>{{ formatDate(transaction.borrow_date) }}</strong></p>
                        <p class="card-text">Ngày trả: <strong>{{ formatDate(transaction.return_date) }}</strong></p>
                        <p class="card-text">
                            Trạng thái:
                            <strong class="text-success">
                                {{ transaction.status == 'completed' ? 'Đã trả' : 'Đang chờ xử lý' }}
                            </strong>
                        </p>

                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <nav class="d-flex justify-content-center mt-4">
            <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="changePage(currentPage - 1)">Trước</button>
                </li>
                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                    <button class="page-link" @click="changePage(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="changePage(currentPage + 1)">Sau</button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
import axios from "../../../axios";

export default {
    name: "HistoryBorrowComponent",
    data() {
        return {
            borrowHistory: [],
            filteredHistory: [],
            currentPage: 1,
            perPage: 3,
            sortBy: 'borrow_date',
            filterText: '',
            host: "http://127.0.0.1:8000",
            loading: true,
        };
    },
    computed: {
        totalPages() {
            return Math.ceil(this.filteredHistory.length / this.perPage);
        },
        paginatedHistory() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredHistory.slice(start, end);
        },
    },
    mounted() {
        this.fetchBorrowHistory();
    },
    methods: {
        async fetchBorrowHistory() {
            this.loading = true;
            try {
                const response = await axios.get('/borrow-transaction/history');
                if (response.data.success) {
                    this.borrowHistory = response.data.data;
                    this.filteredHistory = this.borrowHistory;
                }
            } catch (error) {
                console.error("Lỗi khi lấy lịch sử mượn sách:", error);
            } finally {
                this.loading = false;
            }

        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        },
        changePage(pageNumber) {
            if (pageNumber >= 1 && pageNumber <= this.totalPages) {
                this.currentPage = pageNumber;
            }
        },
        sortHistory() {
            this.filteredHistory.sort((a, b) => {
                if (this.sortBy === 'title') {
                    return a.book.title.localeCompare(b.book.title);
                }
                return new Date(a[this.sortBy]) - new Date(b[this.sortBy]);
            });
        },
        filterHistory() {
            this.filteredHistory = this.borrowHistory.filter(transaction =>
                transaction.book.title.toLowerCase().includes(this.filterText.toLowerCase())
            );
        },
    },
};
</script>

<style scoped>
@import url("../../../../css/card.css");

.borrow-history-section {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    background-color: var(--background-color);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.filter-controls {
    margin-bottom: 20px;
}
</style>
