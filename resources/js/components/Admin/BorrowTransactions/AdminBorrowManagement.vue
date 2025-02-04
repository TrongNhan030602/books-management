<template>
    <div>
        <h1 class="mb-4 main-heading">Duyệt mượn sách</h1>
        <div class="borrow-management">
            <div class="d-flex justify-content-between mb-3">
                <button @click="sort('id')" class="btn btn-outline-secondary">
                    Sắp xếp theo ID <i :class="sortIcon('id')"></i>
                </button>
                <button @click="sort('borrow_date')" class="btn btn-outline-secondary">
                    Sắp xếp theo Ngày Mượn <i :class="sortIcon('borrow_date')"></i>
                </button>
            </div>
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Sách</th>
                        <th>Ngày Mượn</th>
                        <th>Ngày Trả</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="borrowTransactions.length === 0">
                        <td colspan="6" class="text-center text-warning font-weight-bolder p-2">Hiện tại chưa có sách
                            nào
                            đang chờ duyệt.</td>
                    </tr>
                    <tr v-for="transaction in borrowTransactions" :key="transaction.id">
                        <td>{{ transaction.id }}</td>
                        <td class="book-title">{{ transaction.book.title }}</td>
                        <td>{{ transaction.borrow_date }}</td>
                        <td>{{ transaction.return_date }}</td>
                        <td>
                            <span :class="{
                                'text-warning': transaction.status === 'pending',
                                'text-success': transaction.status === 'approved'
                            }">{{ transaction.status === 'pending' ? 'Chờ duyệt' : 'Đã duyệt' }}</span>
                        </td>
                        <td>
                            <button @click="confirmApproveTransaction(transaction.id)" class="btn btn-outline-primary"
                                v-if="transaction.status === 'pending'">Duyệt</button>
                            <button @click="confirmRejectTransaction(transaction.id)" class="btn btn-outline-danger"
                                v-if="transaction.status === 'pending'">Từ chối</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <ModalConfirmation :isVisible="showConfirmModal" @update:isVisible="showConfirmModal = $event"
            @confirm="approveTransaction(confirmedTransactionId)" title="Xác nhận duyệt mượn sách"
            message="Bạn có chắc chắn muốn duyệt mượn sách này không?" />

        <ModalConfirmation :isVisible="showRejectModal" @update:isVisible="showRejectModal = $event"
            @confirm="submitRejectTransaction" title="Xác nhận từ chối mượn sách">
            <div>
                <label for="rejectReason">Chọn lý do từ chối:</label>
                <select v-model="selectedRejectReason" class="form-control mb-3">
                    <option value="" disabled>Chọn lý do</option>
                    <option value="Sách đã được mượn">Sách đã được mượn</option>
                    <option value="Không đủ số lượng sách">Không đủ số lượng sách</option>
                    <option value="Lý do khác">Lý do khác</option>
                </select>

                <div v-if="selectedRejectReason === 'Lý do khác'">
                    <label for="customRejectReason">Nhập lý do khác:</label>
                    <input type="text" v-model="customRejectReason" class="form-control" />
                </div>
            </div>
        </ModalConfirmation>
    </div>
</template>

<script>
import axios from '../../../axios';
import ToastMessage from "../../ToastMessage/ToastMessage.vue";
import ModalConfirmation from "../../Modal/ConfirmationModal.vue";
import EventBus from '../../../eventBus';

export default {
    name: 'AdminBorrowManagement',
    components: {
        ToastMessage,
        ModalConfirmation,
    },
    data() {
        return {
            borrowTransactions: [],
            toastVisible: false,
            toastMessage: '',
            toastType: 'info', // info, success, error
            sortColumn: 'id',
            sortOrder: 'asc',
            showConfirmModal: false,
            showRejectModal: false,
            confirmedTransactionId: null,
            selectedRejectReason: '', // Lý do từ chối đã chọn
            customRejectReason: '', // Lý do từ chối tùy chỉnh
        };
    },
    methods: {
        async fetchBorrowTransactions() {
            try {
                const response = await axios.get('/borrow-transaction/pending');
                this.borrowTransactions = response.data;
            } catch (error) {
                console.error('Error fetching borrow transactions:', error);
                this.showToast('Có lỗi xảy ra khi tải danh sách giao dịch.', 'error');
            }
        },

        confirmApproveTransaction(id) {
            this.confirmedTransactionId = id;
            this.showConfirmModal = true;
        },

        confirmRejectTransaction(id) {
            this.confirmedTransactionId = id;
            this.selectedRejectReason = '';
            this.customRejectReason = '';
            this.showRejectModal = true;
        },

        async approveTransaction(id) {
            try {
                await axios.put(`/borrow-transaction/${id}/approve`);
                this.showToast('Duyệt giao dịch thành công!', 'success');
                EventBus.$emit('update-summary');
                this.fetchBorrowTransactions();
            } catch (error) {
                console.error('Error approving borrow transaction:', error);
                this.showToast('Có lỗi xảy ra khi duyệt.', 'error');
            }
        },

        async submitRejectTransaction() {
            let reason = this.selectedRejectReason;

            if (reason === 'Lý do khác' && this.customRejectReason) {
                reason = this.customRejectReason;
            }

            if (!reason) {
                this.showToast('Vui lòng chọn hoặc nhập lý do từ chối.', 'error');
                return;
            }

            try {
                await axios.post(`/borrow-transaction/reject/${this.confirmedTransactionId}`, { reason });
                this.showToast('Từ chối giao dịch thành công!', 'success');
                this.fetchBorrowTransactions();
            } catch (error) {
                console.error('Error rejecting borrow transaction:', error);
                this.showToast('Có lỗi xảy ra khi từ chối.', 'error');
            } finally {
                this.showRejectModal = false; // Đóng modal sau khi gửi yêu cầu
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
            this.borrowTransactions.sort((a, b) => {
                let compA = a[column];
                let compB = b[column];
                if (this.sortColumn === 'borrow_date') {
                    compA = new Date(a[column]);
                    compB = new Date(b[column]);
                }
                return this.sortOrder === 'asc' ? (compA > compB ? 1 : -1) : (compA < compB ? 1 : -1);
            });
        },

        sortIcon(column) {
            return this.sortColumn === column
                ? this.sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
                : 'fas fa-sort';
        },
    },
    mounted() {
        this.fetchBorrowTransactions();
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

.borrow-management {
    margin-top: 20px;
    background-color: var(--light-bg-color);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px var(--box-shadow-color);
}

/* Table Styles */
.table thead th {
    background: rgb(73, 152, 137);
    color: var(--white-color);
    font-weight: bold;
    cursor: pointer;
    position: relative;
    text-align: center;
}

.table td {
    vertical-align: middle;
    text-align: center;
}

.table td.book-title {
    max-width: 160px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table td button {
    margin: 0 4px;
    display: inline-flex;
    align-items: center;
}

.table td button i {
    font-size: 16px;
    margin-right: 4px;
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: var(--white-color);
}

.btn-outline-danger {
    border-color: var(--danger-color);
    color: var(--danger-color);
}

.btn-outline-danger:hover {
    background-color: var(--danger-color);
    color: var(--white-color);
}

/* Toast Styles */
.toast-success {
    background-color: var(--success-color);
    color: var(--white-color);
}

.toast-error {
    background-color: var(--danger-color);
    color: var(--white-color);
}

@media (max-width: 768px) {
    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .modal-content {
        width: 90%;
    }
}
</style>
