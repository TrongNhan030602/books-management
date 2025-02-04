<template>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <h1 class="text-center mb-4 main-heading">Quản lý Nhà xuất bản</h1>
                <!-- Loading Spinner -->
                <div v-if="loading" class="text-center my-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Đang tải dữ liệu...</p>
                </div>
                <!--Button Add Nhà xuất bản -->
                <div class="d-flex justify-content-end mb-3">
                    <button @click="scrollToForm(false)" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Thêm
                    </button>
                </div>

                <!-- Form tìm kiếm -->
                <div class="mb-4">
                    <input type="text" v-model="searchQuery" @input="debouncedSearch" class="form-control"
                        placeholder="Tìm kiếm nhà xuất bản..." />
                </div>

                <!-- Danh sách Nhà xuất bản -->
                <div v-if="publishers.length" class="publisher-table">
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th @click="sort('name')">Tên <i :class="sortIcon('name')"></i></th>
                                <th @click="sort('address')">Địa chỉ <i :class="sortIcon('address')"></i></th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(publisher, index) in paginatedPublishers" :key="publisher.id">
                                <td>{{ index + 1 }}</td> <!-- Hiển thị STT -->
                                <td>{{ publisher.name }}</td>
                                <td>{{ publisher.address }}</td>
                                <td>
                                    <div class="d-flex">
                                        <button @click="editPublisher(publisher)"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <button @click="confirmDelete(publisher)" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
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
                    Không tìm thấy nhà xuất bản nào.
                </div>

                <!-- Form thêm/sửa Nhà xuất bản -->
                <form ref="publisherForm" @submit.prevent="savePublisher" class="mt-4 p-4 form-container">
                    <h2 class="form-heading">{{ editing ? 'Cập nhật' : 'Thêm' }} Nhà xuất bản</h2>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-building"></i> Tên:
                        </label>
                        <input type="text" id="name" v-model="publisherForm.name" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Địa chỉ:
                        </label>
                        <input type="text" id="address" v-model="publisherForm.address" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-primary">{{ editing ? 'Cập nhật' : 'Thêm' }} Nhà xuất
                        bản</button>
                </form>
            </div>
        </div>

        <!-- ToastMessage -->
        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <!-- ConfirmationModal -->
        <ConfirmationModal :isVisible="showModal" title="Xác nhận Xóa"
            :message="`Bạn có chắc chắn muốn xóa '${publisherName}' không?`" cancelButtonText="Hủy"
            confirmButtonText="Xóa" @update:isVisible="showModal = $event" @confirm="confirmDeleteAction" />
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
            publishers: [],
            currentPage: 1,
            itemsPerPage: 3,
            totalPages: 0,
            searchQuery: '',
            publisherForm: {
                name: '',
                address: '',
            },
            editing: false,
            currentPublisherId: null,
            showModal: false,
            sortColumn: 'name',
            sortOrder: 'asc',
            publisherName: '',
            toastVisible: false,
            toastMessage: '',
            toastType: 'info',  // (info, success, error)
            loading: true
        };
    },
    mounted() {
        this.fetchPublishers();
        this.debouncedSearch = debounce(this.searchPublishers, 300);
    },
    computed: {
        paginatedPublishers() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.publishers.slice(start, end);
        }
    },
    methods: {
        fetchPublishers() {
            axios.get('/publishers').then((response) => {
                this.loading = true;
                this.publishers = response.data.data;
                this.loading = false;
                this.totalPages = Math.ceil(this.publishers.length / this.itemsPerPage);
            });
        },
        changePage(page) {
            if (page > 0 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        searchPublishers() {
            if (this.searchQuery.length > 2) {
                axios.get(`/publishers/search/${this.searchQuery}`).then((response) => {
                    this.publishers = response.data.data;
                });
            } else {
                this.fetchPublishers();
            }
        },
        savePublisher() {
            if (this.editing) {
                axios.put(`/publishers/${this.currentPublisherId}`, this.publisherForm)
                    .then(() => {
                        this.fetchPublishers();
                        this.resetForm();
                        this.showToast('Cập nhật nhà xuất bản thành công!', 'success');
                    })
                    .catch(() => {
                        this.showToast('Có lỗi xảy ra khi cập nhật.', 'error');
                    });
            } else {
                axios.post('/publishers', this.publisherForm)
                    .then(() => {
                        this.fetchPublishers();
                        this.resetForm();
                        this.showToast('Thêm nhà xuất bản thành công!', 'success');
                    })
                    .catch(() => {
                        this.showToast('Có lỗi xảy ra khi thêm.', 'error');
                    });
            }
        },

        confirmDeleteAction() {
            axios.delete(`/publishers/${this.currentPublisherId}`)
                .then(() => {
                    this.fetchPublishers();
                    this.closeModal();
                    this.showToast('Xóa nhà xuất bản thành công!', 'success');
                })
                .catch(() => {
                    this.showToast('Có lỗi xảy ra khi xóa.', 'error');
                });
        },
        editPublisher(publisher) {
            this.editing = true;
            this.currentPublisherId = publisher.id;
            this.publisherForm = { ...publisher };
            this.scrollToForm(true);
        },
        confirmDelete(publisher) {
            this.currentPublisherId = publisher.id;
            this.publisherName = publisher.name;
            this.showModal = true;
        },
        scrollToForm(isEditing) {
            this.editing = isEditing;
            if (!isEditing) {
                this.resetForm();
            }
            this.$nextTick(() => {
                this.$refs.publisherForm.scrollIntoView({ behavior: 'smooth' });
            });
        },
        showToast(message, type = 'info') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;

            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        },
        closeModal() {
            this.showModal = false;
            this.currentPublisherId = null;
            this.publisherName = '';
        },
        resetForm() {
            this.publisherForm = { name: '', address: '' };
            this.editing = false;
            this.currentPublisherId = null;
        },
        sort(column) {
            this.sortOrder = this.sortColumn === column && this.sortOrder === 'asc' ? 'desc' : 'asc';
            this.sortColumn = column;
            this.publishers.sort((a, b) => {
                let compA = a[column].toUpperCase();
                let compB = b[column].toUpperCase();
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
};
</script>


<style scoped>
@import url("../../../../css/form.css");


.main-heading {
    color: var(--primary-color);
    font-weight: var(--font-weight-bold);
    margin-bottom: 20px;
    text-transform: uppercase;
    text-align: center;
    padding-bottom: 16px;
}


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

/* Table Styles */
.table thead th {
    background: rgb(73, 152, 137);
    color: var(--white-color);
    font-weight: bold;
    cursor: pointer;
    position: relative;
    text-align: center;
}

.table tbody tr:hover {
    background-color: var(--light-bg-color);
}

.table td {
    vertical-align: middle;
    text-align: center;
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

/* Alert Styles */
.alert {
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    border: none;
    margin-bottom: 20px;
}

.alert-info {
    background: #d9edf7;
    color: #31708f;
}

.alert-success {
    background: var(--success-color);
    color: #fff;
}

.alert-danger {
    background: var(--error-color);
    color: #fff;
    font-weight: var(--font-weight-bold);
    font-style: italic;
}

@media (max-width: 768px) {
    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>