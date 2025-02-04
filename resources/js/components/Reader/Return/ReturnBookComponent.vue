<template>
    <div id="return-books" class="return-book-section mt-5">
        <h2 class="text-center section-title">Sách đang mượn</h2>
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>

        <div class="filter-section mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="sortOrder">Sắp xếp theo:</label>
                    <select v-model="sortOption" class="form-select" id="sortOrder" @change="sortBooks">
                        <option value="title">Tiêu đề</option>
                        <option value="author">Tác giả</option>
                        <option value="borrow_date">Ngày mượn</option>
                        <option value="return_date">Hạn trả</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filterOption">Lọc theo:</label>
                    <select v-model="filterOption" class="form-select" id="filterOption" @change="filterBooks">
                        <option value="all">Tất cả</option>
                        <option value="due_soon">Sắp đến hạn</option>
                        <option value="overdue">Quá hạn</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="book-list row">
            <div v-if="filteredBooks.length === 0" class="alert alert-info text-center" role="alert">
                {{ noBooksMessage }}
            </div>
            <div v-for="book in paginatedBooks" :key="book.id" class="col-lg-4 col-md-4 col-sm-6 mb-4">
                <div class="card" :class="{ 'card-highlight': isOverdue(book.return_date) }">
                    <img :src="`${host}/storage/${book.book.cover_image}`" alt="Bìa sách"
                        class="card-img-top img-fluid" />
                    <div class="card-body">
                        <h5 class="card-title">{{ book.book.title }}</h5>
                        <p class="card-text">Tác giả: <strong>{{ book.book.author }}</strong></p>
                        <p class="card-text">Ngày mượn: <span class="text-muted">{{ formatDate(book.borrow_date)
                                }}</span></p>
                        <p class="card-text">Hạn trả: <span :class="{ 'text-danger': isOverdue(book.return_date) }">
                                {{ formatDate(book.return_date) }}</span>
                        </p>
                        <button @click="showExtendModal(book)" class="btn btn-outline-primary">Gia hạn</button>
                        <button @click="showReturnModal(book.id)" class="btn btn-outline-danger">Trả sách</button>
                    </div>

                </div>
            </div>

        </div>

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

        <confirm-modal :isVisible="showModal" :title="'Bạn có chắc muốn trả sách này không ?'"
            @update:isVisible="showModal = $event" @confirm="confirmReturn" />

        <rating-modal :isVisible="showRatingModal" :book="selectedBook" :transaction="selectedTransaction"
            @update:isVisible="showRatingModal = $event" @submitRating="submitRating" />

        <toast-message v-if="toastVisible" :type="toastType" :visible="toastVisible" @close="toastVisible = false">
            {{ toastMessage }}
        </toast-message>
        <confirm-modal :isVisible="showExtendModalFlag" :title="'Chọn số ngày gia hạn cho sách này:'"
            @update:isVisible="showExtendModalFlag = $event" @confirm="confirmExtend">
            <div class="form-group">
                <label for="extraDays">Số ngày gia hạn:</label>
                <select v-model="extraDays" class="form-select" id="extraDays">
                    <option v-for="day in 7" :key="day" :value="day">{{ day }} ngày</option>
                </select>
            </div>
        </confirm-modal>

    </div>
</template>

<script>
import axios from "../../../axios";
import ConfirmModal from "../../Modal/ConfirmationModal.vue";
import ToastMessage from "../../ToastMessage/ToastMessage.vue";
import RatingModal from "../../Reader/Return/RatingModal.vue";
import EventBus from '../../../eventBus';

export default {
    name: "ReturnBooks",
    components: {
        ConfirmModal,
        ToastMessage,
        RatingModal,
    },
    data() {
        return {
            borrowedBooks: [],
            filteredBooks: [],
            currentPage: 1,
            perPage: 3,
            sortOption: "title",
            filterOption: "all",
            noBooksMessage: 'Hiện không có sách nào phù hợp với tiêu chí lọc của bạn.',
            host: "http://127.0.0.1:8000",
            showModal: false,
            selectedBook: {},
            toastVisible: false,
            toastType: 'info',
            loading: false,
            showRatingModal: false,
            toastMessage: '',
            rating: null,
            comment: '',
            transaction: {},
            selectedTransaction: null,
            showExtendModalFlag: false,
            extraDays: 1,

        };
    },
    computed: {
        totalPages() {
            return Math.ceil(this.filteredBooks.length / this.perPage);
        },
        paginatedBooks() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredBooks.slice(start, end);
        },
    },
    mounted() {
        this.fetchBorrowedBooks();
    },
    methods: {
        async fetchBorrowedBooks() {
            try {
                this.loading = true;
                const response = await axios.get('/borrow-transaction/borrowed');
                if (response.data.success) {
                    this.borrowedBooks = response.data.data;
                    this.filteredBooks = [...this.borrowedBooks];
                } else {
                    this.showToast('error', response.data.message);
                }
            } catch (error) {
                this.showToast('error', error.response ? error.response.data.message : error.message);
            } finally {
                this.loading = false;
            }
        },
        showExtendModal(transaction) {
            this.selectedTransaction = transaction;
            this.extraDays = 1; // Mặc định
            this.showExtendModalFlag = true;
        },
        async confirmExtend() {
            if (this.selectedTransaction) {
                try {
                    const response = await axios.put(
                        `/borrow-transaction/${this.selectedTransaction.id}/extend`,
                        { extra_days: this.extraDays }
                    );
                    if (response.data.success) {
                        this.showToast('success', `Gia hạn ${this.extraDays} ngày thành công!`);
                        EventBus.$emit('notificationsUpdated');
                        await this.fetchBorrowedBooks(); // Cập nhật lại danh sách
                    } else {
                        this.showToast('error', response.data.message || 'Gia hạn không thành công.');
                    }
                } catch (error) {
                    this.showToast('error', error.response?.data.error || 'Đã xảy ra lỗi khi gia hạn.');
                } finally {
                    this.showExtendModalFlag = false;
                }
            }
        },
        filterBooks() {
            const today = new Date();
            this.filteredBooks = this.borrowedBooks.filter(book => {
                const returnDate = new Date(book.return_date);
                if (this.filterOption === 'due_soon') {
                    return returnDate >= today && returnDate <= new Date(today.getTime() + 3 * 24 * 60 * 60 * 1000); // Sắp đến hạn trong 3 ngày
                } else if (this.filterOption === 'overdue') {
                    return returnDate < today; // Quá hạn
                }
                return true; // Tất cả
            });
            if (this.filteredBooks.length === 0) {
                this.noBooksMessage = 'Hiện không có sách nào phù hợp với tiêu chí lọc của bạn.';
            } else {
                this.noBooksMessage = '';
            }
        },
        formatDate(date) {
            if (!date) return "Chưa trả";
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return new Date(date).toLocaleDateString('vi-VN', options);
        },
        isOverdue(returnDate) {
            const today = new Date();
            return new Date(returnDate) < today;
        },
        showToast(type, message) {
            this.toastVisible = true;
            this.toastType = type;
            this.toastMessage = message;

            setTimeout(() => {
                this.toastVisible = false;
            }, 2000);
        },
        async confirmReturn() {
            if (this.selectedBook) {
                try {
                    const response = await axios.put(`/borrow-transaction/${this.selectedBook.id}/return`);
                    if (response.data.success) {
                        this.showToast('success', "Bạn đã gửi yêu cầu trả sách thành công!");

                        // Lưu thông tin giao dịch tạm thời để sử dụng cho đánh giá
                        this.transaction = {
                            book: this.selectedBook.book,
                            id: this.selectedBook.id // Lưu ID giao dịch
                        };

                        // Mở modal đánh giá trước khi cập nhật lại danh sách sách
                        this.showRatingModal = true;
                        EventBus.$emit('notificationsUpdated');

                    } else {
                        this.showToast('error', response.data.message || "Đã xảy ra lỗi không xác định.");
                    }
                } catch (error) {
                    this.showToast('error', error.response ? error.response.data.message : error.message);
                    console.log(error.response.data.message);
                } finally {
                    this.showModal = false;
                    await this.fetchBorrowedBooks();
                }
            }
        },
        submitRating() {
            const bookId = this.selectedBook.book?.id;
            const transactionId = this.transaction?.id;

            if (!bookId) {
                this.showToast('error', 'Không thể lấy thông tin sách để đánh giá.');
                return;
            }

            const ratingData = {
                book_id: bookId,
                rating: this.rating,
                comment: this.comment,
                transaction_id: transactionId
            };

            axios.post('/reviews', ratingData)
                .then(response => {
                    this.showToast('success', 'Đánh giá đã được gửi');
                    this.closeModal();
                })
                .catch(error => {
                    this.showToast('error', error.response?.data.message || 'Đã xảy ra lỗi khi gửi đánh giá.');
                });
        },
        resetForm() {
            this.rating = null;
            this.comment = '';
        },
        showReturnModal(bookId) {
            const bookToReturn = this.borrowedBooks.find(book => book.id === bookId);
            this.selectedBook = bookToReturn;
            this.showModal = true;
        },
        showRatingModalAction(bookId) {
            const book = this.filteredBooks.find(b => b.book.id === bookId);
            if (book) {
                this.selectedBook = book;

                // Kiểm tra xem giao dịch có hợp lệ không trước khi gán cho selectedTransaction
                if (book && book.id) {
                    this.selectedTransaction = book;
                    this.showRatingModal = true;
                } else {
                    console.error("Không tìm thấy giao dịch hợp lệ cho sách:", bookId);
                    this.showToast('error', 'Không tìm thấy thông tin giao dịch để đánh giá.');
                }
            } else {
                console.error("Không tìm thấy sách với ID:", bookId);
                this.showToast('error', 'Không tìm thấy thông tin sách để đánh giá.');
            }
        },

        changePage(page) {
            this.currentPage = page;
        },
        sortBooks() {
            const sortBy = this.sortOption;
            this.filteredBooks.sort((a, b) => {
                if (sortBy === 'title') {
                    return a.book.title.localeCompare(b.book.title);
                } else if (sortBy === 'author') {
                    return a.book.author.localeCompare(b.book.author);
                } else if (sortBy === 'borrow_date') {
                    return new Date(a.borrow_date) - new Date(b.borrow_date);
                } else if (sortBy === 'return_date') {
                    return new Date(a.return_date) - new Date(b.return_date);
                }
            });
        },
        closeModal() {
            this.showModal = false;
            this.showRatingModal = false;
            this.selectedBook = {};
            this.transaction = null; // Đặt lại thông tin giao dịch
        }
    },
};
</script>



<style scoped>
@import url("../../../../css/card.css");

.card-highlight {
    background-color: #f1dfdf;
    border-color: #ed8f99;

}

.return-book-section {
    background-color: var(--light-bg-color);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.filter-section {
    background-color: var(--white-color);
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px var(--box-shadow-color);
}

.text-danger {
    color: red;
    font-weight: bold;
}


.toast-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
    font-size: 16px;
    font-weight: bold;
    border-radius: 4px;
    padding: 8px 14px;
    margin-right: 0;
    transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.btn-outline-primary:hover {
    background: #316259;
    border-color: var(--primary-color);
    color: #fff;
    opacity: 0.8;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
    font-size: 16px;
    font-weight: bold;
    padding: 8px 14px;

    border-radius: 4px;
    transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: #fff;
    opacity: 0.8;
}
</style>
