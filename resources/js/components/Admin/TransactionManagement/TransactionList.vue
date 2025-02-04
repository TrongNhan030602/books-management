<template>
    <div class="transaction-list">
        <h1 class="mb-4 main-heading">Danh Sách Giao Dịch</h1>

        <!-- Lọc và Sắp xếp -->
        <div class="filters">
            <div class="form-group">
                <label for="statusFilter">Lọc theo trạng thái:</label>
                <select v-model="filters.status" id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="pending">Chờ duyệt mượn</option>
                    <option value="approved">Đang mượn</option>
                    <option value="returned">Chờ duyệt trả</option>
                    <option value="completed">Hoàn tất</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sortBy">Sắp xếp theo:</label>
                <select v-model="filters.sortBy" id="sortBy" class="form-control">
                    <option value="borrow_date">Ngày mượn</option>
                    <option value="return_date">Ngày trả</option>
                    <option value="status">Trạng thái</option>
                </select>
            </div>
        </div>

        <!-- Biểu tượng tải dữ liệu -->
        <div v-if="loading" class="spinner-border text-primary" role="status">
            <span class="sr-only">Đang tải...</span>
        </div>

        <div v-if="filteredTransactions.length === 0 && !loading" class="alert alert-warning">
            Không có giao dịch nào phù hợp với tiêu chí lọc.
        </div>

        <div v-else>
            <div v-for="transaction in paginatedTransactions" :key="transaction.id"
                class="transaction-item mb-4 card p-4 shadow-sm">
                <div class="">
                    <h3 class="transaction-title">Giao dịch ID: {{ transaction.id }}</h3>
                    <div class="transaction-details">
                        <div class="transaction-info">
                            <strong>Người Mượn:</strong> {{ transaction.user.last_name }} {{ transaction.user.first_name
                            }}
                        </div>
                        <div class="transaction-info">
                            <strong>Sách:</strong> {{ transaction.book.title }} - {{ transaction.book.author }}
                        </div>
                        <div class="transaction-info">
                            <strong>Ngày Mượn:</strong> {{ formatDate(transaction.borrow_date) }}
                            <strong class="ml-2">Ngày Trả:</strong> {{ formatDate(transaction.return_date) }}
                        </div>
                        <div class="transaction-info">
                            <strong>Trạng Thái: </strong>
                            <span :class="getStatusClass(transaction.status)">{{ formatStatus(transaction.status)
                                }}</span>
                        </div>
                        <button @click="showDetails(transaction.id)" class="btn-detail btn btn-info mt-3"
                            aria-label="Xem chi tiết giao dịch">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </button>
                    </div>
                </div>
                <div class="ms-auto">
                    <i :class="{
                        'fas fa-check-circle text-success': transaction.status === 'completed',
                        'fas fa-clock text-warning': transaction.status === 'pending',
                        'fas fa-ban text-danger': transaction.status === 'cancelled',
                        'fas fa-hourglass-half text-primary': transaction.status === 'approved',
                        'fas fa-undo text-secondary': transaction.status === 'returned'
                    }" style="font-size: 1.2rem;"></i>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="pagination-wrapper">
                <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">Trước</button>
                <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
                <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau</button>
            </div>
        </div>

        <!-- Modal for Transaction Detail -->
        <TransactionDetail v-if="showTransactionDetail" :transaction="selectedTransaction" @close="closeDetails" />
    </div>
</template>

<script>
import TransactionDetail from './TransactionDetail.vue';
import axios from '../../../axios';
import { format } from 'date-fns';

export default {
    components: {
        TransactionDetail
    },
    data() {
        return {
            transactions: [],
            filters: {
                status: '',
                sortBy: 'borrow_date',
            },
            showTransactionDetail: false,
            selectedTransaction: null,
            loading: true,
            currentPage: 1,
            pageSize: 3,
        };
    },
    computed: {
        filteredTransactions() {
            let filtered = this.transactions;
            if (this.filters.status) {
                filtered = filtered.filter(transaction => transaction.status === this.filters.status);
            }
            if (this.filters.sortBy) {
                filtered = filtered.sort((a, b) => {
                    if (this.filters.sortBy === 'borrow_date') {
                        return new Date(a.borrow_date) - new Date(b.borrow_date);
                    } else if (this.filters.sortBy === 'return_date') {
                        return new Date(a.return_date) - new Date(b.return_date);
                    } else if (this.filters.sortBy === 'status') {
                        return a.status.localeCompare(b.status);
                    }
                });
            }
            return filtered;
        },
        paginatedTransactions() {
            const startIndex = (this.currentPage - 1) * this.pageSize;
            return this.filteredTransactions.slice(startIndex, startIndex + this.pageSize);
        },
        totalPages() {
            return Math.ceil(this.filteredTransactions.length / this.pageSize);
        }
    },
    methods: {
        fetchTransactions() {
            this.loading = true;
            axios.get('/transactions')
                .then(response => {
                    this.transactions = response.data.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.error(error);
                    this.loading = false;
                    alert("Không thể tải giao dịch. Vui lòng thử lại.");
                });
        },
        showDetails(id) {
            this.selectedTransaction = this.transactions.find(tx => tx.id === id);
            this.showTransactionDetail = true;
        },
        closeDetails() {
            this.showTransactionDetail = false;
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        getStatusClass(status) {
            switch (status) {
                case 'pending':
                    return 'text-warning';
                case 'approved':
                    return 'text-success';
                case 'returned':
                    return 'text-primary';
                case 'cancelled':
                    return 'text-danger';
                case 'completed':
                    return 'text-muted';
                default:
                    return '';
            }
        },
        formatStatus(status) {
            const statusLabels = {
                pending: 'Chờ duyệt mượn',
                approved: 'Đang mượn',
                returned: 'Chờ duyệt trả',
                completed: 'Hoàn tất',
                cancelled: 'Đã hủy'
            };
            return statusLabels[status] || 'Chưa xác định';
        },
        formatDate(date) {
            return format(new Date(date), 'dd/MM/yyyy');
        }
    },
    created() {
        this.fetchTransactions();
    }
};
</script>

<style scoped>
.filters {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.filters .form-group {
    margin-bottom: 0;
    flex: 1;
}

.filters select {
    width: 100%;
}

.main-heading {
    color: var(--primary-color);
    font-weight: var(--font-weight-bold);
    margin-bottom: 20px;
    text-transform: uppercase;
    text-align: center;
    padding-bottom: 16px;
}

.btn-detail {
    background-color: var(--primary-color);
    color: var(--white-color);
    border: none;
}

.transaction-list {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
}

.transaction-item {
    border-radius: 8px;
    background-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.transaction-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.transaction-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #2c3e50;
}

.transaction-details {
    margin-top: 10px;
    font-size: 1rem;
    color: #7f8c8d;
}

.transaction-info {
    margin-bottom: 10px;
}

.transaction-info strong {
    color: #34495e;
}

.page-info {
    margin: 0 15px;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

.btn-pagination {
    padding: 8px 15px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-pagination:hover {
    background-color: #2980b9;
}

.btn-pagination:disabled {
    background-color: #bdc3c7;
    cursor: not-allowed;
}
</style>
