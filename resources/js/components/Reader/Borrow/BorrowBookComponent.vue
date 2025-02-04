<template>
    <div class="book-list-section mt-5 container">
        <h2 class="text-center section-title">Danh sách Sách</h2>
        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>

        <!-- Sorting and Filtering Section -->
        <div class="filter-section mb-4 row">
            <div class="col-md-6">
                <label for="sortOrder">Sắp xếp theo:</label>
                <select v-model="sortOption" class="form-select mt-1" id="sortOrder" @change="sortBooks">
                    <option value="title">Tiêu đề</option>
                    <option value="author">Tác giả</option>
                    <option value="published_year">Năm phát hành</option>
                    <option value="quantity">Số lượng</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="filterCriteria">Lọc theo:</label>
                <select v-model="filterOption" class="form-select mt-1" id="filterCriteria" @change="filterBooks">
                    <option value="all">Tất cả</option>
                    <option value="available">Có sẵn</option>
                    <option value="reserved">Đang chờ mượn</option>
                    <option value="borrowed">Đang mượn</option>
                    <option value="returned">Đang chờ trả</option>
                    <option value="not_available">Không có sẵn</option>
                </select>
            </div>
        </div>

        <!-- Book List Section -->
        <div class="row book-list">
            <div v-if="filteredBooks.length === 0" class="alert alert-info text-center" role="alert">
                Không có sách nào phù hợp với tiêu chí lọc hiện tại.
            </div>
            <div v-for="book in paginatedBooks" :key="book.id" class="col-lg-4 col-md-6 mb-4">
                <div class="card card shadow-sm" @click="openBookDetail(book.id)">
                    <img :src="`${host}/storage/${book.cover_image}`" alt="Bìa sách" class="card-img-top" />
                    <div class="card-body">
                        <h5 class="card-title">{{ book.title }}</h5>
                        <p class="card-text">Tác giả: <strong>{{ book.author }}</strong></p>
                        <p class="card-text">Số lượng còn lại: <strong>{{ book.quantity }}</strong></p>
                        <p class="card-text">
                            Trạng thái:
                            <strong class="text-success"
                                v-if="(book.status === 'available' && (book.latestTransactionStatus == 'cancelled' || book.latestTransactionStatus == 'completed') || book.latestTransactionStatus == null)">Có
                                sẵn</strong>
                            <strong class="text-warning" v-if="book.latestTransactionStatus === 'pending'">Đang chờ
                                mượn</strong>
                            <strong class="text-warning" v-if="book.latestTransactionStatus === 'returned'">Đang chờ
                                trả</strong>
                            <strong class="text-info" v-if="book.latestTransactionStatus === 'approved'">Đang
                                mượn</strong>
                        </p>


                        <button
                            v-if="(book.latestTransactionStatus === 'cancelled' || book.latestTransactionStatus === null || book.latestTransactionStatus === 'completed') && book.status === 'available'"
                            @click="openBorrowModal(book.id)" class="btn btn-primary">
                            Mượn sách
                        </button>


                        <button v-if="book.status === 'available' && book.latestTransactionStatus === 'pending'"
                            @click="confirmCancelBorrowRequest(book.borrowTransactionId)"
                            class="btn btn-danger w-100">HỦY YÊU CẦU</button>



                        <!-- Nút Yêu Thích -->
                        <button @click="toggleWishlist(book.id)" class="btn"
                            :class="{ 'text-danger': book.isFavorite }">
                            <i :class="book.isFavorite ? 'fas fa-heart' : 'far fa-heart'"></i>
                        </button>
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
        <BookDetail :bookId="selectedBookId" :isVisible="isDetailModalVisible"
            @update:isVisible="isDetailModalVisible = $event" />
        <BorrowBookModal :isVisible="isModalVisible" :bookId="selectedBookId"
            @update:isVisible="isModalVisible = $event" />

        <!-- Modal Xác Nhận Hủy -->
        <ConfirmCancelModal :isVisible="isCancelModalVisible" @update:isVisible="isCancelModalVisible = $event"
            @confirm="cancelBorrowRequest(selectedBorrowTransactionId)" title="Xác Nhận Hủy"
            message="Bạn có chắc chắn muốn hủy yêu cầu mượn sách này?" />
        <!-- Toast Message -->
        <ToastMessage :type="toastType" :visible="isToastVisible" @close="isToastVisible = false">
            {{ toastMessage }}
        </ToastMessage>
    </div>
</template>





<script>
import axios from "../../../axios";
import EventBus from '../../../eventBus';
import BorrowBookModal from './BorrowBookModal.vue';
import ConfirmCancelModal from '../../Modal/ConfirmationModal.vue';
import ToastMessage from '../../ToastMessage/ToastMessage.vue';
import BookDetail from './BookDetail.vue';
import { includes } from "lodash";
export default {
    name: "BookListComponent",
    components: {
        BorrowBookModal,
        ConfirmCancelModal,
        ToastMessage,
        BookDetail,
    },
    data() {
        return {
            books: [],
            sortOption: "title",
            filterOption: "all",
            filteredBooks: [],
            currentPage: 1,
            perPage: 3,
            host: "http://127.0.0.1:8000",
            isModalVisible: false,
            selectedBookId: null,
            isCancelModalVisible: false,
            selectedBorrowTransactionId: null,
            isToastVisible: false,
            toastMessage: '',
            toastType: 'info', // 'success', 'error', 'info'
            unreadNotificationCount: 0,
            loading: true,
            isDetailModalVisible: false,
            isCancelModalDetail: false,
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
        this.fetchBooks();
        EventBus.$on('bookBorrowed', this.handleBookBorrowed);

    },
    methods: {
        async fetchBooks() {
            this.loading = true;
            try {
                // Lấy thông tin người dùng hiện tại
                const userResponse = await axios.get('/account/me');
                const userId = userResponse.data.id;

                // Gọi API để lấy danh sách sách
                const response = await axios.get('/books');
                if (response.data.success) {
                    this.books = [];

                    for (const book of response.data.data) {
                        // Lọc các giao dịch chỉ thuộc về người dùng hiện tại
                        const userTransactions = book.borrow_transactions.filter(tx => tx.user_id === userId);

                        // Lấy giao dịch gần nhất của người dùng hiện tại, nếu có
                        let latestTransactionStatus = userTransactions.length > 0 ? userTransactions[userTransactions.length - 1].status : null;
                        let borrowTransactionId = userTransactions.length > 0 ? userTransactions[userTransactions.length - 1].id : null;

                        // Kiểm tra xem sách có trong wishlist không
                        const isFavorite = await this.checkIfBookInWishlist(book.id);

                        // Thêm sách và trạng thái tương ứng vào danh sách
                        this.books.push({
                            isFavorite,
                            ...book,
                            latestTransactionStatus, // Trạng thái chỉ của người dùng hiện tại
                            borrowTransactionId, // Giao dịch gần nhất của người dùng hiện tại
                        });
                    }

                    // Áp dụng bộ lọc và sắp xếp
                    this.filterBooks();


                } else {
                    this.showToast("Không thể lấy danh sách sách!", 'error');
                }
            } catch (error) {
                console.error("Lỗi khi lấy danh sách sách:", error);
                this.showToast("Có lỗi xảy ra khi lấy danh sách sách.", 'error');
            } finally {
                this.loading = false;
            }
        },
        filterBooks() {
            if (this.filterOption === "all") {
                this.filteredBooks = this.books;
            } else {
                this.filteredBooks = this.books.filter((book) => {
                    // Kiểm tra trạng thái của sách
                    if (this.filterOption === "available") {
                        return book.status === 'available' && (book.latestTransactionStatus === null || book.latestTransactionStatus === 'completed' || book.latestTransactionStatus === 'cancelled');
                    } else if (this.filterOption === "reserved") {
                        return book.latestTransactionStatus === 'pending'; // Đang chờ mượn
                    } else if (this.filterOption === "borrowed") {
                        return book.latestTransactionStatus === 'approved'; // Đã mượn
                    } else if (this.filterOption === "returned") {
                        return book.latestTransactionStatus === 'returned'; // Đang chờ trả
                    } else if (this.filterOption === "not_available") {
                        return book.status !== 'available'; // Không có sẵn
                    }
                    return true; // Mặc định trả về true nếu không phù hợp với các điều kiện trên
                });
            }
            this.sortBooks(); // Áp dụng sắp xếp sau khi lọc
            // Kiểm tra nếu không có sách nào phù hợp
            if (this.filteredBooks.length === 0) {
                this.showToast("Không có sách nào phù hợp với tiêu chí lọc hiện tại.", 'info');
            }
        },

        handleBookBorrowed() {
            this.fetchBooks();
            this.updateUnreadNotifications();
        },
        confirmCancelBorrowRequest(borrowTransactionId) {
            this.selectedBorrowTransactionId = borrowTransactionId;
            this.isCancelModalVisible = true;
            this.isCancelModalDetail = true;
        },
        openBookDetail(bookId) {
            if (!this.isCancelModalDetail) {
                this.selectedBookId = bookId;
                this.isDetailModalVisible = true;
            }
            this.isCancelModalDetail = false;
        },
        async cancelBorrowRequest(borrowTransactionId) {
            try {
                const response = await axios.delete(`/borrow-transaction/cancel/${borrowTransactionId}`);
                if (response.data.success) {
                    this.fetchBooks();
                    this.showToast(response.data.message, 'success');
                    // Phát sự kiện để Slider cập nhật
                    EventBus.$emit('borrowRequestCancelled');
                } else {
                    this.showToast("Lỗi khi hủy yêu cầu: " + response.data.error, 'error');
                }
            } catch (error) {
                console.error("Lỗi khi gọi API:", error);
                this.showToast("Có lỗi xảy ra, vui lòng thử lại sau.", 'error');
            }
        },
        showToast(message, type) {
            this.toastMessage = message;
            this.toastType = type;
            this.isToastVisible = true;

            setTimeout(() => {
                this.isToastVisible = false;
            }, 2000);
        },

        changePage(pageNumber) {
            if (pageNumber >= 1 && pageNumber <= this.totalPages) {
                this.currentPage = pageNumber;
            }
        },
        sortBooks() {
            const sortKey = this.sortOption;
            this.filteredBooks.sort((a, b) => {
                if (sortKey === "published_year") {
                    return a.published_year - b.published_year;
                } else if (sortKey === "quantity") {
                    return a.quantity - b.quantity;
                } else {
                    return a[sortKey].localeCompare(b[sortKey], 'vi');
                }
            });
            this.currentPage = 1;
        },
        openBorrowModal(bookId) {
            this.selectedBookId = bookId;
            this.isModalVisible = true;
            this.isCancelModalDetail = true;
        },
        updateUnreadNotifications() {
            this.unreadNotificationCount += 1;
        },
        async checkIfBookInWishlist(bookId) {
            try {
                const response = await axios.post('/wishlist/check', { book_id: bookId });
                return response.data.success && response.data.exists; // Trả về true hoặc false
            } catch (error) {
                console.error("Lỗi khi kiểm tra sách trong danh sách yêu thích:", error);
                return false; // Nếu có lỗi thì giả định rằng sách không phải trong danh sách yêu thích
            }
        },

        async toggleWishlist(bookId) {
            const book = this.books.find(b => b.id === bookId);
            if (book) {
                this.isCancelModalDetail = true;
                if (book.isFavorite) {
                    await this.removeBookFromWishlist(bookId);
                } else {
                    await this.addBookToWishlist(bookId);
                }
            }
        },

        async addBookToWishlist(bookId) {
            try {
                const response = await axios.post('/wishlist/add', { book_id: bookId });
                if (response.data.success) {
                    const book = this.books.find(b => b.id === bookId);
                    if (book) {
                        book.isFavorite = true; // Cập nhật trạng thái
                        this.showToast(response.data.message, 'success');
                    }
                }
            } catch (error) {
                console.error("Lỗi khi thêm sách vào danh sách yêu thích:", error);
                this.showToast("Có lỗi xảy ra khi thêm sách vào danh sách yêu thích.", 'error');
            }
        },
        async removeBookFromWishlist(bookId) {
            try {
                const response = await axios.delete('/wishlist/remove', { data: { book_id: bookId } });
                if (response.data.success) {
                    const book = this.books.find(b => b.id === bookId);
                    if (book) {
                        book.isFavorite = false; // Cập nhật trạng thái
                        this.showToast(response.data.message, 'success');
                    }
                }
            } catch (error) {
                console.error("Lỗi khi xóa sách khỏi danh sách yêu thích:", error);
                this.showToast("Có lỗi xảy ra khi xóa sách khỏi danh sách yêu thích.", 'error');
            }
        }

    },
    beforeDestroy() {
        EventBus.$off('bookBorrowed', this.handleBookBorrowed);
    }
};
</script>

<style scoped>
@import url("../../../../css/card.css");

.book-list-section {
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


/* Button Styles */
.btn {
    padding: 10px 20px;
    font-size: 1rem;
    border-radius: 8px;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    color: white;
    box-shadow: 0 3px 10px rgba(0, 123, 255, 0.2);
}

.btn-primary:hover {
    background-color: var(--hover-color);
    box-shadow: 0 6px 15px rgba(0, 123, 255, 0.3);
}

.btn-danger {
    background-color: var(--error-color);
    border: none;
    color: white;
    box-shadow: 0 3px 10px rgba(245, 88, 88, 0.2);
}

.btn-danger:hover {
    box-shadow: 0 6px 15px rgba(255, 0, 0, 0.3);
    opacity: 0.9;
}

.btn:disabled {
    background-color: var(--disabled-color);
    cursor: not-allowed;
    box-shadow: none;
}
</style>
