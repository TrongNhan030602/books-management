<template>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <h1 class="text-center mb-4 main-heading">Quản lý Người Dùng</h1>
                <!-- Loading Spinner -->
                <div v-if="loading" class="text-center my-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Đang tải dữ liệu...</p>
                </div>
                <!-- Button Add User -->
                <div class="d-flex justify-content-end mb-3">
                    <button @click="scrollToForm(false)" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Thêm
                    </button>
                </div>
                <!-- Form tìm kiếm -->
                <div class="mb-4">
                    <input type="text" v-model="searchQuery" @input="debouncedSearch" class="form-control"
                        placeholder="Tìm kiếm người dùng..." />
                </div>

                <!-- Danh sách Người Dùng -->
                <div v-if="users.length" class="user-table mb-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th @click="sort('id')">ID <i :class="sortIcon('id')"></i></th>
                                <!-- Thêm sự kiện sort cho cột ID -->
                                <th @click="sort('first_name')">Tên <i :class="sortIcon('first_name')"></i></th>
                                <th @click="sort('email')">Email <i :class="sortIcon('email')"></i></th>
                                <th @click="sort('role')">Role <i :class="sortIcon('role')"></i></th>

                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(user, index) in paginatedUsers" :key="user.id" @click="showUserDetails(user)">
                                <td>{{ index + 1 }}</td>
                                <td>{{ user.id }}</td>
                                <td>{{ user.first_name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.role }}</td>
                                <td>
                                    <button @click.stop="editUser(user)" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <button @click.stop="confirmDelete(user)" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="pagination">
                        <button class="pagination__button" @click="changePage(currentPage - 1)"
                            :disabled="currentPage === 1">
                            <i class="fas fa-chevron-left pagination__button-icon"></i>
                        </button>
                        <span class="pagination__number"> Trang {{ currentPage }} trên {{ totalPages }}</span>
                        <button class="pagination__button" @click="changePage(currentPage + 1)"
                            :disabled="currentPage === totalPages">
                            <i class="fas fa-chevron-right pagination__button-icon"></i>
                        </button>
                    </div>
                </div>

                <div v-else class="alert alert-info text-center">
                    Không tìm thấy người dùng nào.
                </div>

                <!-- Form thêm/sửa Người Dùng -->
                <form @submit.prevent="saveUser" class="mt-4 p-4 form-container" ref="formContainer">
                    <h2 class="form-heading">{{ editing ? 'Cập nhật' : 'Thêm' }} Người Dùng</h2>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">
                            <i class="fas fa-user"></i> Họ:
                        </label>
                        <input type="text" id="first_name" v-model="userForm.first_name" class="form-control"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">
                            <i class="fas fa-user"></i> Tên:
                        </label>
                        <input type="text" id="last_name" v-model="userForm.last_name" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email:
                        </label>
                        <input type="email" id="email" v-model="userForm.email" class="form-control" required
                            autocomplete="username" />
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> Điện thoại:
                        </label>
                        <input type="text" id="phone" v-model="userForm.phone" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Địa chỉ:
                        </label>
                        <input type="text" id="address" v-model="userForm.address" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="dob" class="form-label">
                            <i class="fas fa-calendar-day"></i> Ngày sinh:
                        </label>
                        <input type="date" id="dob" v-model="userForm.dob" class="form-control" />
                    </div>
                    <div v-if="!editing" class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mật khẩu:
                        </label>
                        <input type="password" id="password" v-model="userForm.password" class="form-control" required
                            autocomplete="new-password" />
                    </div>
                    <div v-if="!editing" class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-lock"></i> Xác nhận mật khẩu:
                        </label>
                        <input type="password" id="password_confirmation" v-model="userForm.password_confirmation"
                            class="form-control" required autocomplete="new-password" />
                    </div>
                    <div v-if="isAdmin" class="mb-3">
                        <label for="role" class="form-label">
                            <i class="fas fa-user-tag"></i> Vai trò:
                        </label>
                        <select id="role" v-model="userForm.role" class="form-select form-control" required>
                            <option v-for="role in roleOptions" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>
                    <div v-if="isAdmin" class="mb-3">
                        <label for="membership_level" class="form-label">
                            <i class="fas fa-star"></i> Cấp độ:
                        </label>
                        <select id="membership_level" v-model="userForm.membership_level"
                            class="form-select form-control" required>
                            <option v-for="level in membershipLevels" :key="level" :value="level">{{ level }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">{{ editing ? 'Cập nhật' : 'Thêm' }} Người
                        Dùng</button>
                </form>

            </div>
        </div>

        <!-- ToastMessage -->
        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <!-- Modal -->
        <ConfirmationModal :isVisible="showModal" title="Xác nhận Xóa"
            :message="`Bạn có chắc chắn muốn xóa người dùng '${userName}' không?`" cancelButtonText="Hủy"
            confirmButtonText="Xóa" @update:isVisible="showModal = $event" @confirm="confirmDeleteAction" />

        <!-- Modal Chi Tiết User-->
        <div v-if="showDetailsModal" @click.self="closeDetailsModal"
            :class="['modal-overlay', { 'fade-out': isFadingOut }]">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ selectedUser?.last_name }} {{ selectedUser?.first_name }}</h2>
                    <a class="icon-close" @click="closeDetailsModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-envelope me-2"></i><strong>Email:</strong> {{ selectedUser?.email }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-phone me-2"></i><strong>Điện thoại:</strong> {{ selectedUser?.phone }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-map-marker-alt me-2"></i><strong>Địa chỉ:</strong> {{ selectedUser?.address
                            }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-birthday-cake me-2"></i><strong>Ngày sinh:</strong> {{
                                formatDate(selectedUser?.dob) }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-user-tag me-2"></i><strong>Vai trò:</strong> {{ selectedUser?.role }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-star me-2"></i><strong>Cấp độ:</strong> {{ selectedUser?.membership_level
                            }}
                        </li>
                    </ul>

                    <div v-if="sortedTransactions.length" class="mt-3">
                        <h5 class="d-flex align-items-center">
                            <i class="fas fa-book me-2" style="font-size: 1.5rem;"></i>
                            <strong>Giao dịch mượn sách:</strong>
                        </h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                v-for="transaction in sortedTransactions" :key="transaction.id">
                                <div>
                                    <div>
                                        <strong>Mã Giao dịch:</strong> <span class="text-muted">{{ transaction.id
                                            }}</span>
                                    </div>
                                    <div>
                                        <strong>Mã Sách:</strong> <span class="text-muted">{{ transaction.book_id
                                            }}</span>
                                    </div>
                                    <div>
                                        <strong class="me-2">Trạng thái: </strong>
                                        <span class="badge" :class="{
                                            'bg-success': transaction.status === 'completed',
                                            'bg-warning': transaction.status === 'pending',
                                            'bg-danger': transaction.status === 'cancelled',
                                            'bg-primary': transaction.status === 'approved',
                                            'bg-secondary': transaction.status === 'returned'
                                        }">{{ formatStatus(transaction.status) }}</span>
                                    </div>
                                    <div v-if="transaction.reason">
                                        <strong class="me-2">Lý do hủy: </strong>
                                        <span class="text-muted">{{ transaction.reason }}</span>
                                    </div>
                                    <div>
                                        <strong class="me-2">Thời gian mượn: </strong>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-2"></i>{{
                                                formatDate(transaction.borrow_date) }} - {{
                                                formatDate(transaction.return_date) }}
                                        </small>
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
                            </li>
                        </ul>
                    </div>

                    <div v-else class="mt-3">
                        <p><i class="fas fa-info-circle me-2"></i>Không có giao dịch mượn sách nào.</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-save" @click="closeDetailsModal">
                        <i class="fas fa-check me-2"></i>Đóng
                    </button>
                </div>
            </div>
        </div>





    </div>
</template>

<script>
import axios from '../../../axios';

import debounce from 'lodash/debounce';
import ConfirmationModal from "../../Modal/ConfirmationModal.vue";
import ToastMessage from "../../ToastMessage/ToastMessage.vue";

export default {
    components: {
        ConfirmationModal,
        ToastMessage
    },
    data() {
        return {
            users: [],
            userTransactions: [],
            currentPage: 1,
            itemsPerPage: 3,
            totalPages: 0,
            userForm: {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                address: '',
                dob: '',
                role: 'Reader',
                membership_level: 'Bronze'  // Default
            },
            editing: false,
            isAdmin: true,
            searchQuery: '',
            sortColumn: 'first_name',
            sortOrder: 'asc',
            toastVisible: false,
            toastMessage: '',
            toastType: 'success',  // default 
            roleOptions: ['Admin', 'Reader'],
            membershipLevels: ['Bronze', 'Silver', 'Gold'],
            showModal: false,
            isFadingOut: false,
            currentUserId: null,
            userName: '',
            selectedUser: null,
            showDetailsModal: false,
            showAvatarModal: false,
            loading: true,
        };
    },
    computed: {
        paginatedUsers() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.users.slice(start, end);
        },
        sortedTransactions() {
            return this.userTransactions.sort((a, b) => b.id - a.id);
        }
    },
    methods: {
        fetchUsers() {
            axios.get('/users').then((response) => {
                this.loading = true;
                this.users = response.data.data;
                this.loading = false;
                this.totalPages = Math.ceil(this.users.length / this.itemsPerPage);
            });
        },
        changePage(page) {
            if (page > 0 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        searchUsers() {
            if (this.searchQuery.length > 2) {
                axios.get(`/users/search/${this.searchQuery}`).then((response) => {
                    this.users = response.data.data;
                });
            } else {
                this.fetchUsers();
            }
        },
        saveUser() {
            const url = this.editing ? `/users/${this.userForm.id}` : '/users';
            const method = this.editing ? 'put' : 'post';
            const data = { ...this.userForm };

            if (this.editing) {
                delete data.password;
                delete data.password_confirmation;
            }

            axios[method](url, data)
                .then(response => {
                    this.toastMessage = this.editing ? 'Cập nhật thông tin thành công!' : 'Thêm người dùng thành công!';
                    this.toastType = 'success';
                    this.toastVisible = true;
                    setTimeout(() => {
                        this.toastVisible = false;
                    }, 2000);
                    this.fetchUsers();
                    this.resetForm();
                })
                .catch(error => {
                    console.error("Error saving user:", error);
                    if (error.response && error.response.data && error.response.data.message) {
                        this.toastMessage = error.response.data.message;
                    } else {
                        this.toastMessage = 'Có lỗi xảy ra!';
                    }
                    this.toastType = 'error';
                    this.toastVisible = true;
                    setTimeout(() => {
                        this.toastVisible = false;
                    }, 2000);
                });
        },
        resetForm() {
            this.userForm = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                address: '',
                dob: '',
                password: '',
                password_confirmation: '',
                role: 'Reader',
                membership_level: 'Bronze'
            };
            this.editing = false;
        },
        editUser(user) {
            this.userForm = { ...user };
            this.editing = true;
            this.scrollToForm();
        },
        confirmDelete(user) {
            this.currentUserId = user.id;
            this.userName = `${user.last_name} ${user.first_name}`;
            this.showModal = true;
        },
        confirmDeleteAction() {
            axios.delete(`/users/${this.currentUserId}`)
                .then(() => {
                    this.toastMessage = 'Người dùng đã được xóa thành công!';
                    this.toastVisible = true;
                    this.fetchUsers();
                    setTimeout(() => {
                        this.toastVisible = false;
                    }, 2000);
                })
                .catch(error => {
                    console.error("Error deleting user:", error);
                    this.toastMessage = 'Có lỗi xảy ra!';
                    this.toastType = 'error';
                    this.toastVisible = true;
                    setTimeout(() => {
                        this.toastVisible = false;
                    }, 2000);
                });
            this.showModal = false;
        },
        showUserDetails(user) {
            this.selectedUser = user;
            this.showDetailsModal = true;
            this.fetchUserTransactions(user.id);
        },
        fetchUserTransactions(userId) {
            axios.get(`/users/${userId}/transactions`).then((response) => {
                this.userTransactions = response.data.data;
            }).catch(error => {
                console.error('Error fetching transactions:', error);
                this.userTransactions = [];
            });
        },
        closeDetailsModal() {
            this.isFadingOut = true;
            setTimeout(() => {
                this.selectedUser = null;
                this.userTransactions = [];
                this.showDetailsModal = false;
                this.isFadingOut = false;
            }, 300);
        },
        scrollToForm() {
            this.$refs.formContainer.scrollIntoView({ behavior: 'smooth' });
        },
        debouncedSearch: debounce(function () {
            this.fetchUsers();
        }, 500),
        sort(column) {
            this.sortOrder = this.sortColumn === column && this.sortOrder === 'asc' ? 'desc' : 'asc';
            this.sortColumn = column;

            this.users.sort((a, b) => {
                let compA = a[column];
                let compB = b[column];

                // So sánh các giá trị theo kiểu số nếu column là 'id'
                if (column === 'id') {
                    compA = parseInt(compA);
                    compB = parseInt(compB);
                } else {
                    compA = compA.toUpperCase();
                    compB = compB.toUpperCase();
                }

                if (compA < compB) return this.sortOrder === 'asc' ? -1 : 1;
                if (compA > compB) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
        },

        sortIcon(column) {
            if (this.sortColumn === column) {
                return this.sortOrder === 'asc' ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            }
            return 'fas fa-sort';
        },
        formatDate(date) {
            if (!date) return '';
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(date).toLocaleDateString('vi-VN', options);
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
    },

    mounted() {
        this.fetchUsers();
        this.debouncedSearch = debounce(this.searchUsers, 300);
    }
};
</script>

<style scoped>
@import url("../../../../css/form.css");

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination__number {
    padding: 3px;
    font-style: italic;
}

.pagination__button {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 5px;
    padding: 8px;
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.pagination__button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.pagination__button:hover:not(:disabled) {
    background-color: #296459;
}

.pagination__button-icon {
    font-size: 1rem;
}

.main-heading {
    color: var(--primary-color);
    font-weight: var(--font-weight-bold);
    margin-bottom: 20px;
    text-transform: uppercase;
    text-align: center;
    padding-bottom: 16px;

}

.user-table {
    border-radius: 8px;
}

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

.user-table tbody tr:hover,
.user-table th {
    cursor: pointer;

}

.user-table th i {
    margin-left: 5px;
}

.form-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.form-heading {
    margin-bottom: 20px;
}

/* Modal deltail */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.modal-content {
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 590px;
    padding: 1rem;
    animation: fadeIn 0.3s ease;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.75rem 0.75rem 0 0;
}

.modal-title {
    font-size: 1.75rem;
    font-weight: bold;
    color: var(--primary-color);
}

.modal-body {
    padding: 1rem;
    font-size: 1rem;
    color: #495057;
    line-height: 1.5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    border-radius: 0 0 0.75rem 0.75rem;
}

.list-group-item {
    padding: 1rem;
    border: 1px solid #ddd;
    margin-bottom: 0.5rem;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}

.list-group-item strong {
    font-weight: bold;
}

.list-group-item .badge {
    font-size: 0.9rem;
    padding: 0.3rem 0.6rem;
    border-radius: 0.375rem;
}

.list-group-item .text-muted {
    font-size: 0.9rem;
}

.list-group-item .ms-auto {
    margin-left: auto;
}

/* Icon size and spacing */
.fas {
    font-size: 1.2rem;
}

.fas.fa-check-circle {
    color: #28a745;
}

.fas.fa-clock {
    color: #ffc107;
}

.fas.fa-ban {
    color: #dc3545;
}

.fas.fa-hourglass-half {
    color: #007bff;
}

.fas.fa-undo {
    color: #6c757d;
}

.modal-title {
    font-size: 1.75rem;
    font-weight: bold;
    color: var(--primary-color);
}

.modal-body {
    padding: 1rem;
    font-size: 1rem;
    color: #495057;
    line-height: 1.5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    border-radius: 0 0 0.75rem 0.75rem;
}



.list-group-item i {
    color: var(--primary-color);
}

.icon-close {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    color: var(--primary-color);
    transition: color 0.3s ease;
}

.icon-close:hover {
    color: rgb(48, 94, 86);
    cursor: pointer;
}

.btn-save {
    background-color: #499889;
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    width: 100%;
    color: var(--white-color);
}

.btn-save:hover {
    background-color: #296459;
    color: var(--white-color);

}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: scale(1);
    }

    to {
        opacity: 0;
        transform: scale(0.4);
    }
}

.fade-out {
    animation: fadeOut 0.3s ease;
}

@media (max-width: 768px) {
    .modal-content {
        width: 100%;
        padding: 1.5rem;
    }

    .modal-header {
        flex-direction: column;
        text-align: center;
    }

    .modal-body {
        font-size: 0.9rem;
    }

    .list-group-item {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
}
</style>
