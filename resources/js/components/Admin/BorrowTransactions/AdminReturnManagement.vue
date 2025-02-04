<template>
    <div>
        <h1 class="main-heading mb-4">Duyệt trả sách</h1>
        <div class="return-management">
            <div class="d-flex justify-content-between mb-3">
                <button @click="sort('id')" class="btn btn-outline-secondary">
                    Sắp xếp theo ID <i :class="sortIcon('id')"></i>
                </button>
                <button @click="sort('return_date')" class="btn btn-outline-secondary">
                    Sắp xếp theo Ngày Trả <i :class="sortIcon('return_date')"></i>
                </button>
            </div>
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Sách</th>
                        <th>Ngày Trả</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tr v-if="returnTransactions.length === 0">
                    <td colspan="5" class="text-center text-warning font-weight-bolder p-2">Hiện tại chưa có sách nào
                        đang chờ trả.</td>
                </tr>
                <tbody>
                    <tr v-for="transaction in returnTransactions" :key="transaction.id">
                        <td>{{ transaction.id }}</td>
                        <td class="book-title">{{ transaction.book.title }}</td>
                        <td>{{ transaction.return_date }}</td>
                        <td>
                            <span :class="{ 'text-warning': transaction.status === 'returned' }">{{ transaction.status
                                ==
                                'returned' ? 'Chưa duyệt' : 'Đã duyệt' }}</span>
                        </td>
                        <td>
                            <button @click="confirmCompleteReturn(transaction.id)" class="btn btn-outline-primary"
                                v-if="transaction.status === 'returned'">Hoàn Tất</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <ModalConfirmation :isVisible="showConfirmModal" @update:isVisible="showConfirmModal = $event"
            @confirm="completeReturn(confirmedTransactionId)" title="Xác nhận hoàn tất trả sách"
            message="Bạn có chắc chắn muốn hoàn tất trả sách này không?" />
    </div>
</template>

<script>
import ToastMessage from "../../ToastMessage/ToastMessage.vue";
import ModalConfirmation from "../../Modal/ConfirmationModal.vue";
import axios from '../../../axios';
import EventBus from '../../../eventBus';

export default {
    name: 'AdminReturnManagement',
    components: {
        ToastMessage,
        ModalConfirmation,
    },
    data() {
        return {
            returnTransactions: [],
            showReturnModal: false,
            toastVisible: false,
            toastMessage: '',
            toastType: 'info', // info, success, error
            sortColumn: 'id',
            sortOrder: 'asc',
            showConfirmModal: false,
            confirmedTransactionId: null,
        };
    },
    methods: {
        async fetchReturnTransactions() {
            try {
                const response = await axios.get('/borrow-transaction/return');
                this.returnTransactions = response.data;
            } catch (error) {
                console.error('Error fetching return transactions:', error);
            }
        },
        confirmCompleteReturn(id) {
            this.confirmedTransactionId = id;
            this.showConfirmModal = true;
        },
        async completeReturn(id) {
            try {
                await axios.put(`/borrow-transaction/${id}/complete-return`);
                this.showToast('Hoàn tất trả sách thành công!', 'success');
                EventBus.$emit('update-summary');
                this.fetchReturnTransactions();
            } catch (error) {
                console.error('Error completing return transaction:', error);
                this.showToast('Có lỗi xảy ra khi hoàn tất.', 'error');
            }
        },
        showToast(message, type = 'info') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;

            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        },
        sort(column) {
            this.sortOrder = this.sortColumn === column && this.sortOrder === 'asc' ? 'desc' : 'asc';
            this.sortColumn = column;
            this.returnTransactions.sort((a, b) => {
                let compA = a[column];
                let compB = b[column];
                if (this.sortColumn === 'return_date') {
                    compA = new Date(a[column]);
                    compB = new Date(b[column]);
                }
                if (compA < compB) return this.sortOrder === 'asc' ? -1 : 1;
                if (compA > compB) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
        },
        sortIcon(column) {
            return this.sortColumn === column
                ? this.sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
                : 'fas fa-sort';
        },
    },
    mounted() {
        this.fetchReturnTransactions();
    },
};
</script>




<style scoped>
.main-heading {
    color: var(--primary-color);
    font-weight: var(--font-weight-bold);
    text-transform: uppercase;
    text-align: center;
    padding-bottom: 20px;
}

.return-management {
    margin-top: 20px;
    background-color: var(--light-bg-color);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px var(--box-shadow-color);
}

.table th,
.table td {
    vertical-align: middle;
}

.table th {
    background-color: var(--primary-color);
    color: var(--white-color);
}

.table td.book-title {
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table tbody tr:hover {
    background-color: var(--hover-color);
    color: #fff;
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-outline-primary:hover {
    background: var(--hover-color);
    color: #fff;
}

/* Responsive styles */
@media (max-width: 768px) {

    .table th,
    .table td {
        font-size: 14px;
    }

    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .modal-content {
        width: 90%;
    }

    h1 {
        font-size: 1.5rem;
    }
}
</style>
