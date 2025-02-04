<template>
    <div class="recommendation-section mt-5">
        <h2 class="pb-2 text-success">Đề xuất cho bạn</h2>
        <div class="recommendation-list row">
            <div v-for="book in recommendedBooks" :key="book.id" class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="book-card" @click="openBookModal(book.id)">
                    <img :src="`${host}/storage/${book.cover_image}`" alt="Book cover" class="book-cover img-fluid" />
                    <div class="book-info">
                        <h5 class="book-title">{{ book.title }}</h5>
                        <p>Tác giả: {{ book.author }}</p>
                        <button class="btn btn-primary mt-2" @click.stop="checkBorrowEligibility(book.id)"
                            :disabled="!isBorrowable(book)">
                            Mượn sách
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Chi tiết sách -->
        <BookDetailModal :bookId="selectedBookId" :isVisible="isDetailModalVisible"
            @update:isVisible="isDetailModalVisible = false" />

        <!-- Modal Mượn sách -->
        <BorrowBookModal :bookId="selectedBookId" :isVisible="isBorrowModalVisible"
            @update:isVisible="isBorrowModalVisible = false" />
    </div>
</template>

<script>
import axios from '../../../axios';
import EventBus from '../../../eventBus.js';

import BookDetailModal from '../../Reader/Borrow/BookDetail.vue';
import BorrowBookModal from '../../Reader/Borrow/BorrowBookModal.vue';

export default {
    name: "RecommendationComponent",
    components: {
        BookDetailModal,
        BorrowBookModal,
    },
    data() {
        return {
            recommendedBooks: [],
            host: "http://127.0.0.1:8000",
            selectedBookId: null,
            isDetailModalVisible: false,
            isBorrowModalVisible: false,
            userId: null,
        };
    },
    mounted() {
        this.fetchUserId();
        EventBus.$on('borrowRequestCancelled', this.fetchRecommendedBooks);
        EventBus.$on('bookBorrowed', this.fetchRecommendedBooks);
    },
    beforeDestroy() {
        EventBus.$off('borrowRequestCancelled', this.fetchRecommendedBooks);
        EventBus.$off('bookBorrowed', this.fetchRecommendedBooks);
    },
    methods: {
        async fetchUserId() {
            try {
                const response = await axios.get('/account/me');
                this.userId = response.data.id;
                this.fetchRecommendedBooks();
            } catch (error) {
                console.error("Lỗi khi lấy thông tin người dùng:", error);
            }
        },

        async fetchRecommendedBooks() {
            if (!this.userId) return;
            try {
                const recommendationsResponse = await axios.get(`http://127.0.0.1:5000/recommendations/${this.userId}`);
                const recommendations = recommendationsResponse.data;

                const bookDetailsPromises = recommendations.map(rec =>
                    axios.get(`/books/${rec}/details`)  // Đảm bảo đường dẫn API đúng
                );
                const bookDetailsResponses = await Promise.all(bookDetailsPromises);

                this.recommendedBooks = bookDetailsResponses.map(response => {
                    const bookData = response.data.data.book;

                    // Lấy trạng thái giao dịch gần nhất
                    const otherTransactions = response.data.data.other_transactions || [];
                    bookData.userLastTransactionStatus = otherTransactions.length > 0
                        ? otherTransactions[otherTransactions.length - 1].status
                        : null;

                    return bookData;
                });
            } catch (error) {
                console.error("Lỗi khi tải sách đề xuất:", error);
            }
        },

        openBookModal(bookId) {
            this.selectedBookId = bookId;
            this.isDetailModalVisible = true;
        },
        checkBorrowEligibility(bookId) {
            const book = this.recommendedBooks.find(b => b.id === bookId);

            if (!book.userLastTransactionStatus ||
                book.userLastTransactionStatus === 'completed' ||
                book.userLastTransactionStatus === 'cancelled') {
                this.openBorrowModal(bookId);
            } else {
                alert("Bạn không thể mượn sách vì giao dịch gần nhất không hoàn thành.");
            }
        },
        isBorrowable(book) {
            const isTransactionValid = !book.userLastTransactionStatus ||
                book.userLastTransactionStatus === 'completed' ||
                book.userLastTransactionStatus === 'cancelled';
            const isQuantityAvailable = book.quantity > 0;
            return isTransactionValid && isQuantityAvailable;
        },
        openBorrowModal(bookId) {
            this.selectedBookId = bookId;
            this.isBorrowModalVisible = true;
        },
    }
};
</script>



<style scoped>
.recommendation-section {
    background-color: var(--light-bg-color);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.recommendation-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.book-card {
    background-color: var(--white-color);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    padding: 20px;
    height: 100%;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.book-cover {
    max-height: 200px;
    object-fit: cover;
    margin-bottom: 15px;
    border-radius: 8px;
}

.book-info {
    flex-grow: 1;
    padding: 10px 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.book-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--primary-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.book-info p {
    font-size: 0.9rem;
    color: var(--text-color);
    margin: 5px 0;
}

.btn {
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 15px;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.btn:hover {
    filter: brightness(110%);
    background-color: var(--primary-color);
}

.btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: var(--primary-color);
}
</style>
