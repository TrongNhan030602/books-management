<template>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <h1 class="text-center mb-4 main-heading">Quản lý Thể loại</h1>
                <!-- Loading Spinner -->
                <div v-if="loading" class="text-center my-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Đang tải dữ liệu...</p>
                </div>
                <!-- Button Add Thể loại -->
                <div class="d-flex justify-content-end mb-3">
                    <button @click="scrollToForm(false)" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Thêm
                    </button>
                </div>

                <!-- Form tìm kiếm -->
                <div class="mb-4">
                    <input type="text" v-model="searchQuery" @input="debouncedSearch" class="form-control"
                        placeholder="Tìm kiếm thể loại..." />
                </div>

                <!-- Danh sách Thể loại -->
                <div v-if="categories.length" class="category-table">
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th @click="sort('name')">Tên <i :class="sortIcon('name')"></i></th>
                                <th @click="sort('description')">Mô tả <i :class="sortIcon('description')"></i></th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(category, index) in paginatedCategories" :key="category.id">
                                <td>{{ index + 1 }}</td> <!-- Hiển thị STT -->
                                <td>{{ category.name }}</td>
                                <td>{{ category.description }}</td> <!-- Hiển thị Mô tả -->
                                <td>
                                    <div class="d-flex">
                                        <button @click="editCategory(category)" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <button @click="confirmDelete(category)" class="btn btn-outline-danger btn-sm">
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
                    Không tìm thấy thể loại nào.
                </div>

                <!-- Form thêm/sửa Thể loại -->
                <form ref="categoryForm" @submit.prevent="saveCategory" class="mt-4 p-4 form-container">
                    <h2 class="form-heading">{{ editing ? 'Cập nhật' : 'Thêm' }} Thể loại</h2>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag"></i> Tên:
                        </label>
                        <input type="text" id="name" v-model="categoryForm.name" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-info-circle"></i> Mô tả:
                        </label>
                        <textarea id="description" v-model="categoryForm.description" class="form-control"
                            required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ editing ? 'Cập nhật' : 'Thêm' }} Thể loại</button>
                </form>
            </div>
        </div>

        <!-- ToastMessage -->
        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <!-- ConfirmationModal -->
        <ConfirmationModal :isVisible="showModal" title="Xác nhận Xóa"
            :message="`Bạn có chắc chắn muốn xóa '${categoryName}' không?`" cancelButtonText="Hủy"
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
            categories: [],
            currentPage: 1,
            itemsPerPage: 3,
            totalPages: 0,
            searchQuery: '',
            categoryForm: {
                name: '',
                description: ''
            },
            editing: false,
            currentCategoryId: null,
            showModal: false,
            categoryName: '',
            toastVisible: false,
            toastMessage: '',
            toastType: 'info',  // (info, success, error)
            loading: true,
            sortColumn: '',
        };
    },
    mounted() {
        this.fetchCategories();
        this.debouncedSearch = debounce(this.searchCategories, 300);
    },
    computed: {
        paginatedCategories() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.categories.slice(start, end);
        }
    },
    methods: {
        fetchCategories() {
            this.loading = true;
            axios.get('/categories')
                .then((response) => {
                    this.categories = response.data.data;
                    this.totalPages = Math.ceil(this.categories.length / this.itemsPerPage);
                    this.loading = false;
                })
                .catch((error) => {
                    console.error("Lỗi khi tải thể loại:", error);
                    this.showToast('Không thể tải thể loại!', 'error');
                    this.loading = false;
                });
        },

        changePage(page) {
            if (page > 0 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        searchCategories() {
            if (this.searchQuery.length > 2) {
                axios.get(`/categories/search/${this.searchQuery}`).then((response) => {
                    this.categories = response.data.data;
                });
            } else {
                this.fetchCategories();
            }
        },
        saveCategory() {
            if (this.editing) {
                axios.put(`/categories/${this.currentCategoryId}`, this.categoryForm)
                    .then(() => {
                        this.fetchCategories();
                        this.resetForm();
                        this.showToast('Cập nhật thể loại thành công!', 'success');
                    })
                    .catch(() => {
                        this.showToast('Có lỗi xảy ra khi cập nhật.', 'error');
                    });
            } else {
                axios.post('/categories', this.categoryForm)
                    .then(() => {
                        this.fetchCategories();
                        this.resetForm();
                        this.showToast('Thêm thể loại thành công!', 'success');
                    })
                    .catch(() => {
                        this.showToast('Có lỗi xảy ra khi thêm.', 'error');
                    });
            }
        },

        confirmDeleteAction() {
            axios.delete(`/categories/${this.currentCategoryId}`)
                .then(() => {
                    this.fetchCategories();
                    this.closeModal();
                    this.showToast('Xóa thể loại thành công!', 'success');
                })
                .catch(() => {
                    this.showToast('Có lỗi xảy ra khi xóa.', 'error');
                });
        },
        editCategory(category) {
            this.editing = true;
            this.currentCategoryId = category.id;
            this.categoryForm = { ...category };
            this.scrollToForm(true);
        },
        confirmDelete(category) {
            this.currentCategoryId = category.id;
            this.categoryName = category.name;
            this.showModal = true;
        },
        scrollToForm(isEditing) {
            this.editing = isEditing;
            if (!isEditing) {
                this.resetForm();
            }
            this.$nextTick(() => {
                this.$refs.categoryForm.scrollIntoView({ behavior: 'smooth' });
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
            this.currentCategoryId = null;
            this.categoryName = '';
        },
        resetForm() {
            this.categoryForm = { name: '', description: '' };
            this.editing = false;
            this.currentCategoryId = null;
        },
        sort(column) {
            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            this.sortColumn = column;
            this.categories.sort((a, b) => {
                const valueA = a[column].toLowerCase();
                const valueB = b[column].toLowerCase();
                if (this.sortOrder === 'asc') {
                    return valueA < valueB ? -1 : 1;
                }
                return valueA > valueB ? -1 : 1;
            });
        },
        sortIcon(column) {
            return this.sortColumn === column ? (this.sortOrder === 'asc' ? 'fas fa-chevron-up' : 'fas fa-chevron-down') : '';
        }
    }
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

/* Pagination Styles */
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
