<template>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <h1 class="text-center mb-4 main-heading">Quản lý Sách</h1>
                <!-- Loading Spinner -->
                <div v-if="loading" class="text-center my-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Đang tải dữ liệu...</p>
                </div>
                <!--Button Add Sách -->
                <div class="d-flex justify-content-end mb-3">
                    <button @click="scrollToForm(false)" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Thêm
                    </button>
                </div>
                <!-- Form tìm kiếm -->
                <div class="mb-4">
                    <input type="text" v-model="searchQuery" @input="debouncedSearch" class="form-control"
                        placeholder="Tìm kiếm sách..." />
                </div>

                <!-- Danh sách Sách -->
                <div v-if="books.length" class="book-table mb-4">
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th @click="sort('id')">ID <i :class="sortIcon('id')"></i></th> <!-- Sắp xếp theo ID -->
                                <th>Hình ảnh</th>
                                <th @click="sort('title')">Tiêu đề <i :class="sortIcon('title')"></i></th>
                                <th @click="sort('author')">Tác giả <i :class="sortIcon('author')"></i></th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="book in paginatedBooks" :key="book.id" @click="showBookDetails(book)">
                                <td>{{ book.id }}</td>
                                <td>
                                    <!-- Display the book cover and an edit icon to change the cover -->
                                    <div class="book-cover-container">
                                        <img :src="getBookCoverUrl(book.cover_image)" alt="Book Cover"
                                            class="book-cover-image" />
                                        <i class="fas fa-camera edit-cover-icon" @click.stop="changeCover(book)"></i>
                                        <i class="fas fa-images add-images-icon" @click.stop="addImages(book)"></i>
                                    </div>
                                </td>
                                <td class="book-title">{{ book.title }}</td>
                                <td class="book-author">{{ book.author }}</td>
                                <td>
                                    <button @click.stop="editBook(book)" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <button @click.stop="confirmDelete(book)" class="btn btn-outline-danger btn-sm">
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
                    Không tìm thấy sách nào.
                </div>

                <!-- Form thêm/sửa Sách -->
                <form @submit.prevent="saveBook" class="mt-4 p-4 form-container" ref="formContainer">
                    <h2 class="form-heading">{{ editing ? 'Cập nhật' : 'Thêm' }} Sách</h2>
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            <i class="fas fa-book"></i> Tiêu đề:
                        </label>
                        <input type="text" id="title" v-model="bookForm.title" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">
                            <i class="fas fa-user"></i> Tác giả:
                        </label>
                        <input type="text" id="author" v-model="bookForm.author" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="publisher" class="form-label">
                            <i class="fas fa-building"></i> Nhà xuất bản:
                        </label>
                        <select id="publisher" v-model="bookForm.publisher_id" class="form-control form-select"
                            required>
                            <option v-for="publisher in publishers" :key="publisher.id" :value="publisher.id">
                                {{ publisher.name }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">
                            <i class="fas fa-dollar-sign"></i> Giá:
                        </label>
                        <input type="number" id="price" v-model="bookForm.price" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="initial_quantity" class="form-label">
                            <i class="fas fa-box"></i> Số lượng ban đầu:
                        </label>
                        <input type="number" id="initial_quantity" v-model="bookForm.initial_quantity"
                            class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">
                            <i class="fas fa-box"></i> Số lượng hiện có:
                        </label>
                        <input type="number" id="quantity" v-model="bookForm.quantity" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="published_year" class="form-label">
                            <i class="fas fa-calendar"></i> Năm xuất bản:
                        </label>
                        <input type="number" id="published_year" v-model="bookForm.published_year" class="form-control"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-pencil-alt"></i> Mô tả:
                        </label>
                        <textarea id="description" v-model="bookForm.description" class="form-control" rows="4"
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">
                            <i class="fas fa-tag"></i> Trạng thái:
                        </label>
                        <select id="status" v-model="bookForm.status" class="form-select form-control" required>
                            <option value="available">Có sẵn</option>
                            <option value="borrowed">Đã mượn</option>
                            <option value="reserved">Đã đặt</option>
                            <option value="not_available">Không có sẵn</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Vị trí:
                        </label>
                        <input type="text" id="location" v-model="bookForm.location" class="form-control" required />
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Thể loại</label>
                        <select id="category" v-model="bookForm.category_id" class="form-control form-select" required>
                            <option value="" disabled>Chọn thể loại</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <!-- Hiển thị tên thể loại đã chọn -->
                        <p v-if="bookForm.category_name" class="mt-2 text-muted">Thể loại hiện tại: {{
                            bookForm.category_name }}</p>
                    </div>






                    <!-- Chỉ hiển thị trường "Hình ảnh bìa" khi không phải là chế độ chỉnh sửa -->
                    <div v-if="!editing" class="mb-3">
                        <label for="cover_image" class="form-label">
                            <i class="fas fa-image"></i> Hình ảnh bìa:
                        </label>
                        <input type="file" id="cover_image" @change="handleImageUpload" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">{{ editing ? 'Cập nhật' : 'Thêm' }}
                        Sách</button>
                </form>
            </div>
        </div>

        <!-- ToastMessage -->
        <ToastMessage :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </ToastMessage>

        <!-- Modal xác nhận-->
        <ConfirmationModal :isVisible="showModal" title="Xác nhận Xóa"
            :message="`Bạn có chắc chắn muốn xóa sách '${bookTitle}' không?`" cancelButtonText="Hủy"
            confirmButtonText="Xóa" @update:isVisible="showModal = $event" @confirm="confirmDeleteAction" />

        <!-- Modal Chi Tiết Sách -->
        <div v-if="showDetailsModal" @click.self="closeDetailsModal"
            :class="['modal-overlay', { 'fade-out': isFadingOut }]">
            <div v-if="!selectedBook" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Đang tải thông tin...
            </div>

            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ selectedBook?.title }}</h2>
                    <a class="icon-close" @click="closeDetailsModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-user me-2"></i><strong>Tác giả:</strong> {{ selectedBook?.author }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-building me-2"></i><strong>Nhà xuất bản:</strong> {{
                                selectedBook?.publisher_name }}
                        </li>
                        <li class="list-group-item">
                            <i class="fa-solid fa-money-check-dollar me-2"></i><strong>Giá:</strong> {{
                                formatCurrency(selectedBook?.price) }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-box me-2"></i><strong>Số lượng ban đầu:</strong> {{
                                selectedBook?.initial_quantity }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-box-open me-2"></i><strong>Số lượng hiện có:</strong> {{
                                selectedBook?.quantity }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-calendar-alt me-2"></i><strong>Năm xuất bản:</strong> {{
                                selectedBook?.published_year }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-pencil-alt me-2"></i><strong>Mô tả:</strong> {{ selectedBook?.description
                            }}
                        </li>
                        <li class="list-group-item">
                            <i class="fa-solid fa-location-dot me-2"></i><strong>Vị trí:</strong> {{
                                selectedBook?.location }}
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-tag me-2"></i><strong>Thể loại:</strong> {{ selectedBook?.category }}
                        </li>

                        <!-- Chi tiết giao dịch mượn -->
                        <li class="list-group-item">
                            <h4 class="text-success">Chi tiết giao dịch:</h4>
                            <ul class="list-group mt-2">
                                <!-- If no borrow transactions, show a message -->
                                <li v-if="!selectedBook?.borrow_transactions || selectedBook?.borrow_transactions.length === 0"
                                    class="list-group-item d-flex justify-content-between align-items-center">
                                    <p><i class="fas fa-info-circle me-2 text-warning"></i>Không có giao dịch mượn sách
                                        nào.</p>

                                </li>

                                <li v-for="(transaction, index) in selectedBook?.borrow_transactions"
                                    :key="transaction.id" class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div>
                                                <strong>Mã Giao dịch:</strong> <span class="text-muted">{{
                                                    transaction.id
                                                    }}</span>
                                            </div>
                                            <div>
                                                <strong>ID người mượn</strong> <span class="text-muted">{{
                                                    transaction.user_id
                                                    }}</span>
                                            </div>
                                            <div>
                                                <strong class="me-2">Thời gian mượn: </strong>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-2"></i>{{
                                                        formatDate(transaction.borrow_date) }} - {{
                                                        formatDate(transaction.return_date) }}
                                                </small>
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
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-save" @click="closeDetailsModal">
                        <i class="fas fa-check me-2"></i>Đóng
                    </button>
                </div>
            </div>
        </div>




        <!-- Modal đổi ảnh bìa -->
        <div v-if="showCoverModal" class="modal-overlay" @click.self="closeCoverModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Cập nhật ảnh bìa</h2>
                    <a class="icon-close" @click="closeCoverModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <input type="file" @change="handleCoverChange" />
                </div>
                <div class="modal-footer">
                    <button class="btn btn-save" @click="uploadBookCover">
                        <i class="fas fa-check me-2"></i>Lưu ảnh bìa
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal thêm nhiều ảnh -->
        <div v-if="showImagesModal" class="modal-overlay" @click.self="closeImagesModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Quản lý ảnh</h2>
                    <a class="icon-close" @click="closeImagesModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </a>
                </div>

                <div class="modal-body">
                    <!-- Hiển thị danh sách ảnh đã tải lên -->
                    <div v-if="existingImages.length > 0" class="uploaded-images">
                        <h5>Danh sách ảnh đã tải lên:</h5>
                        <div class="image-grid">
                            <div v-for="(image, index) in existingImages" :key="index" class="image-item">
                                <img :src="getImageUrl(image)" alt="Book Image" class="uploaded-image" />
                                <button @click="deleteImage(image)" class="btn btn-danger btn-sm mt-2">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <p>Chưa có ảnh nào được tải lên.</p>
                    </div>

                    <!-- Thêm ảnh mới -->
                    <h5 class="mt-4">Thêm ảnh mới:</h5>
                    <input type="file" @change="handleImageChange" multiple />
                </div>

                <div class="modal-footer">
                    <button class="btn btn-save" @click="uploadImages">
                        <i class="fas fa-check me-2"></i>Lưu ảnh
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
            books: [],
            users: [],
            currentPage: 1,
            itemsPerPage: 3,
            totalPages: 0,
            publishers: [],
            categories: [],
            searchQuery: '',
            selectedBook: null,
            showCoverModal: false,
            coverFile: null,
            currentBookId: null,
            showImagesModal: false,
            existingImages: [],
            selectedImages: [],
            selectedBookForImages: {},
            bookForm: {
                title: '',
                author: '',
                publisher_id: null,
                price: '',
                initial_quantity: '',
                quantity: '',
                published_year: '',
                description: '',
                status: 'available',
                location: '',
                category_id: null,
                category_name: '',

            },
            showDetailsModal: false,
            isFadingOut: false,
            editing: false,
            showModal: false,
            sortColumn: 'title',
            sortOrder: 'asc',
            bookTitle: '',
            toastVisible: false,
            toastMessage: '',
            toastType: 'info',  // (info, success, error)
            loading: true,
        };
    },
    mounted() {
        this.fetchCategories()
        this.fetchPublishers()
            .then(() => this.fetchBooks());

        this.debouncedSearch = debounce(this.searchBooks, 300);
    },
    watch: {
        showImagesModal(newVal) {
            if (newVal) {
                this.loadExistingImages();
            }
        }
    },
    computed: {
        paginatedBooks() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.books.slice(start, end);
        },


    },
    methods: {
        fetchBooks() {
            this.loading = true;
            axios.get('/books')
                .then(response => {
                    this.books = response.data.data.map(book => ({
                        ...book,
                        publisher_name: this.publishers.find(p => p.id === book.publisher_id)?.name || 'Chưa có',
                        category_name: this.categories.find(c => c.id === book.category_id)?.name || 'Không xác định',
                        cover_image_url: this.getBookCoverUrl(book.cover_image)
                    }));
                    this.totalPages = Math.ceil(this.books.length / this.itemsPerPage);
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi lấy danh sách sách:', error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        async fetchCategories() {
            try {
                const response = await axios.get('/categories');
                this.categories = response.data.data;
            } catch (error) {
                console.error("Có lỗi khi lấy danh mục:", error);
            }
        },
        changePage(page) {
            if (page > 0 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        addBook() {
            this.scrollToForm();
            const formData = new FormData();

            for (const key in this.bookForm) {
                if (this.bookForm[key]) {
                    formData.append(key, this.bookForm[key]);
                }
            }

            axios.post('/books', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then((response) => {
                    this.fetchBooks();
                    this.resetForm();
                    this.showToast('Sách đã được thêm thành công!', 'success');
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi thêm sách:', error);
                    this.showToast('Có lỗi xảy ra khi thêm sách.', 'error');
                });
        },
        updateBook() {

            const url = `/books/${this.currentBookId}`;
            const formData = new FormData();

            // Các trường cần update
            const fieldsToInclude = ['title', 'author', 'publisher_id', 'price', 'initial_quantity', 'quantity', 'published_year', 'status', 'description', 'location', 'category_id'];

            for (const key of fieldsToInclude) {
                if (this.bookForm[key]) {
                    formData.append(key, this.bookForm[key]);
                }
            }
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then((response) => {
                    this.fetchBooks();
                    this.resetForm();
                    this.showToast('Sách đã được cập nhật thành công!', 'success');
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi cập nhật sách:', error);
                    this.showToast('Có lỗi xảy ra khi cập nhật sách.', 'error');
                });
        },
        saveBook() {
            if (this.editing) {
                this.updateBook();
            } else {
                this.addBook();
            }
        }
        ,
        fetchPublishers() {
            return axios.get('/publishers')
                .then(response => {
                    this.publishers = response.data.data;
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi lấy danh sách nhà xuất bản:', error);
                });
        },
        searchBooks() {
            if (this.searchQuery.length > 2) {
                axios.get(`/books/search/${this.searchQuery}`)
                    .then(response => {
                        this.books = response.data.data.map(book => ({
                            ...book,
                            publisher_name: this.publishers.find(p => p.id === book.publisher_id)?.name || 'Chưa có'
                        }));
                    })
                    .catch(error => {
                        console.error('Có lỗi xảy ra khi tìm kiếm sách:', error);
                    });
            } else {
                this.fetchBooks();
            }
        },
        changeCover(book) {
            this.currentBookId = book.id;
            this.showCoverModal = true;
        },
        handleCoverChange(event) {
            this.coverFile = event.target.files[0];
        },
        handleImageChange(event) {
            this.selectedImages = Array.from(event.target.files);
        },
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.bookForm.cover_image = file;
            }
        },
        closeImagesModal() {
            this.showImagesModal = false;
        },
        async loadExistingImages() {
            try {
                const response = await axios.get(`/books/${this.selectedBookForImages.id}/book_images`);
                if (response.data.success) {
                    this.existingImages = response.data.images;
                }
            } catch (error) {
                this.showToast("Sách này vẫn chưa có ảnh bổ sung.", "info");
            }
        },
        async deleteImage(image) {
            try {
                const response = await axios.delete(`/books/${this.selectedBookForImages.id}/${image}`);
                if (response.data.success) {
                    this.showToast("Ảnh đã được xóa.", "success");
                    this.existingImages = this.existingImages.filter(img => img !== image);
                } else {
                    this.showToast(response.data.message, "error");
                }
            } catch (error) {
                this.showToast("Đã xảy ra lỗi khi xóa ảnh.", "error");
            }
        },
        getBookCoverUrl(imageName) {
            return imageName ? `http://127.0.0.1:8000/storage/${imageName}` : 'http://127.0.0.1:8000/storage/book_covers/book_default.png';
        },
        getImageUrl(image) {
            return image ? `http://127.0.0.1:8000/storage/${image}` : 'http://127.0.0.1:8000/storage/book_covers/book_default.png';
        },
        editBook(book) {
            this.currentBookId = book.id;
            this.bookForm = {
                ...book,
            };

            // Tìm và thiết lập tên thể loại
            const selectedCategory = this.categories.find(c => c.id === book.category_id);
            if (selectedCategory) {
                this.bookForm.category_name = selectedCategory.name;
            }

            this.editing = true;
            this.scrollToForm();
        },
        confirmDelete(book) {
            this.currentBookId = book.id;
            this.bookTitle = book.title;
            this.showModal = true;
        },
        showToast(message, type = 'info') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;

            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        },
        uploadBookCover() {
            if (!this.coverFile) {
                return;
            }

            const formData = new FormData();
            formData.append('cover_image', this.coverFile);

            axios.post(`/books/${this.currentBookId}/cover`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    this.showToast('Ảnh bìa được cập nhật thành công!', 'success');
                    this.fetchBooks();
                    this.closeCoverModal();
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi upload ảnh bìa:', error);
                });
        },
        addImages(book) {
            this.selectedBookForImages = book;
            this.existingImages = [];
            this.showImagesModal = true;
        },
        closeImagesModal() {
            this.showImagesModal = false;
            this.selectedImages = [];
        },
        scrollToForm() {
            const formContainer = this.$refs.formContainer;
            if (formContainer) {
                formContainer.scrollIntoView({ behavior: 'smooth' });
            }
        },
        async uploadImages() {
            if (!this.selectedImages.length) {
                this.showToast("Vui lòng chọn ít nhất một ảnh.", "error");
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < this.selectedImages.length; i++) {
                formData.append('images[]', this.selectedImages[i]);
            }

            try {
                const response = await axios.post(
                    `/books/${this.selectedBookForImages.id}/book_images`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    }
                );
                if (response.data.success) {
                    this.showToast("Ảnh đã được tải lên thành công.", "success");
                } else {
                    this.showToast(response.data.message, "info");
                }
            } catch (error) {
                this.showToast("Đã xảy ra lỗi. Có thể tên ảnh đã tồn tại!", "info");
                console.error('Lỗi khi tải ảnh', error);
            } finally {
                this.closeImagesModal();
            }
        },
        closeCoverModal() {
            this.showCoverModal = false;
            this.coverFile = null;
        },
        confirmDeleteAction() {
            axios.delete(`/books/${this.currentBookId}`)
                .then(() => {
                    this.fetchBooks();
                    this.closeModal();
                    this.showToast('Sách đã được xóa thành công!', 'success');
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra khi xóa sách:', error);
                    this.showToast('Có lỗi xảy ra khi xóa sách.', 'error');
                });
        },
        showBookDetails(book) {
            this.selectedBook = book;
            this.showDetailsModal = true;
        },
        closeDetailsModal() {
            this.isFadingOut = true;
            setTimeout(() => {
                this.selectedBook = null;
                this.showDetailsModal = false;
                this.isFadingOut = false;
            }, 300);
        },
        closeModal() {
            this.showModal = false;
            this.currentBookId = null;
            this.bookTitle = '';
            this.resetForm();
        },
        resetForm() {
            this.bookForm = { title: '', author: '', publisher_id: null, price: '', initial_quantity: '', quantity: '', published_year: '', status: 'available' };
            this.editing = false;
            this.currentBookId = null;
        },
        sort(column) {
            if (this.sortColumn === column) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
            this.books.sort((a, b) => {
                const valueA = a[column];
                const valueB = b[column];
                if (typeof valueA === 'string') {
                    return this.sortOrder === 'asc' ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
                } else {
                    return this.sortOrder === 'asc' ? valueA - valueB : valueB - valueA;
                }
            });
        },

        sortIcon(column) {
            if (this.sortColumn === column) {
                return this.sortOrder === 'asc' ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
            }
            return 'fa fa-sort';
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
        formatCurrency(amount) {
            if (isNaN(amount) || amount === null) {
                return '';
            }

            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        },
        formatDate(date) {
            if (!date) return '';
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(date).toLocaleDateString('vi-VN', options);
        },
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

.book-table tbody tr {
    transition: background-color 0.3s ease;
}

.book-table tbody tr:hover {
    cursor: pointer;

}

.book-cover-image {
    width: 100px;
    height: auto;
    border-radius: 4px;
}

.book-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}

.book-author {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100px;
}



.book-cover-container {
    position: relative;
    display: inline-block;
}

.book-cover-image {
    width: 80px;
    height: auto;
}

.edit-cover-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    font-size: 1.2rem;
    color: var(--primary-color);
    cursor: pointer;
}

.add-images-icon {
    position: absolute;
    bottom: 5px;
    left: 5px;
    font-size: 1.2rem;
    color: var(--primary-color);
    cursor: pointer;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 15px;
}

.image-item {
    text-align: center;
    box-shadow: 4px 4px 8px #ccc;
}

.uploaded-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.uploaded-image:hover {
    transform: scale(1.1);
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

/* Modal Styles */
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
    padding: 2rem;
    animation: fadeIn 0.3s ease;
    position: relative;
    max-height: 568px;
    overflow-y: scroll;
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
    font-size: 1.1rem;
    color: #495057;
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
        transform: scale(0.8);
    }
}

.fade-out {
    animation: fadeOut 0.3s ease;
}


.btn-danger {
    background: #dc3545;
    color: var(--white-color);
    border: none;
}

.btn-danger:hover {
    background: #c82333;
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