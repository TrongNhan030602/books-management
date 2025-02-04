<template>
    <div class="transaction-detail">
        <!-- Overlay khi modal được mở -->
        <div class="overlay" @click="closeDetail"></div>

        <!-- Modal Chi Tiết -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi Tiết Giao Dịch</h5>
                    <button type="button" class="btn-close" @click="closeDetail" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="loading" class="spinner-container">
                        <!-- Spinner loading khi dữ liệu chưa tải xong -->
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div v-else>
                        <div class="row pt-3">
                            <!-- Thông Tin Người Mượn -->
                            <div class="col-md-6">
                                <h6>Thông Tin Người Mượn</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Tên:</strong> {{ transaction.user.last_name }} {{
                                        transaction.user.first_name }}</li>
                                    <li><strong>Email:</strong> {{ transaction.user.email }}</li>
                                    <li><strong>Điện Thoại:</strong> {{ transaction.user.phone }}</li>
                                    <li><strong>Địa Chỉ:</strong> {{ transaction.user.address }}</li>
                                </ul>
                            </div>

                            <!-- Thông Tin Sách -->
                            <div class="col-md-6">
                                <h6>Thông Tin Sách</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Tiêu Đề:</strong> {{ transaction.book.title }}</li>
                                    <li><strong>Tác Giả:</strong> {{ transaction.book.author }}</li>
                                    <li><strong>Giá:</strong> {{ transaction.book.price }} VNĐ</li>
                                    <li><strong>Vị Trí:</strong> {{ transaction.book.location }}</li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Thông Tin Nhà Xuất Bản -->
                            <div class="col-md-6" v-if="publisher">
                                <h6>Nhà Xuất Bản</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Tên:</strong> {{ publisher.name }}</li>
                                    <li><strong>Địa Chỉ:</strong> {{ publisher.address }}</li>
                                </ul>
                            </div>

                            <!-- Thông Tin Thể Loại -->
                            <div class="col-md-6" v-if="category">
                                <h6>Thể Loại</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Tên:</strong> {{ category.name }}</li>
                                    <li><strong>Mô Tả:</strong> {{ category.description }}</li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <!-- Thông Tin Giao Dịch -->
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Ngày Mượn:</strong> {{ formatDate(transaction.borrow_date) }}</p>
                                <p><strong>Ngày Trả:</strong> {{ formatDate(transaction.return_date) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong class="me-2">Trạng Thái:</strong>
                                    <span :class="getStatusClass(transaction.status)">
                                        {{ formatStatus(transaction.status) }}
                                    </span>
                                </p>
                                <p v-if="transaction.reason"><strong class="me-2">Lý do hủy:</strong>
                                    <span>
                                        {{ transaction.reason }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="closeDetail">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from '../../../axios';
import { format } from 'date-fns';

export default {
    props: {
        transaction: Object
    },
    data() {
        return {
            publisher: null,
            category: null,
            loading: true, // Đặt mặc định là đang tải
        };
    },
    methods: {
        closeDetail() {
            this.$emit('close');
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
        fetchPublisher(publisherId) {
            axios.get(`/publishers/${publisherId}`)
                .then(response => {
                    if (response.data.success) {
                        this.publisher = response.data.data;
                    }
                })
                .catch(error => {
                    console.error('Error fetching publisher:', error);
                });
        },
        fetchCategory(categoryId) {
            axios.get(`/categories/${categoryId}`)
                .then(response => {
                    if (response.data.success) {
                        this.category = response.data.data;
                    }
                })
                .catch(error => {
                    console.error('Error fetching category:', error);
                });
        },
        formatDate(date) {
            return format(new Date(date), 'dd-MM-yyyy');
        }
    },
    mounted() {
        if (this.transaction.book.publisher_id) {
            this.fetchPublisher(this.transaction.book.publisher_id);
        }
        if (this.transaction.book.category_id) {
            this.fetchCategory(this.transaction.book.category_id);
        }
    },
    watch: {
        // Watch for changes in transaction or fetched data and update loading state
        publisher() {
            if (this.publisher && this.category) {
                this.loading = false;
            }
        },
        category() {
            if (this.publisher && this.category) {
                this.loading = false;
            }
        }
    }
};
</script>

<style scoped>
.transaction-detail {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 999;
}

.modal-dialog {
    max-width: 900px;
    margin: 30px auto;
    position: relative;
    z-index: 1001;
}

.modal-title {
    color: var(--primary-color);
    font-weight: 600;
}

.modal-content {
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    padding: 15px;
    background-color: white;
    max-height: 90vh;
    /* Giới hạn chiều cao của modal */
    overflow-y: auto;
    /* Cho phép cuộn dọc */
    transition: max-height 0.3s ease-in-out;
    /* Hiệu ứng mượt mà khi thay đổi chiều cao */
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.modal-footer {
    border-top: 1px solid #ddd;
}

.btn-close {
    background: none;
    border: none;
    color: #6c757d;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.btn-close:hover {
    color: #dc3545;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 5px;
}

button:hover {
    background-color: #c82333;
}

h6 {
    margin-bottom: 10px;
    font-weight: 600;
}

.list-unstyled li {
    margin-bottom: 5px;
}

/* Spinner */
.spinner-container {
    text-align: center;
    padding: 30px 0;
}
</style>
