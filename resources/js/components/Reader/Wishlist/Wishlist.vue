<template>
    <div class="wishlist">
        <h2 class="section-title text-center">Danh sách yêu thích của bạn</h2>

        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>

        <ul v-if="wishlist.length > 0">
            <li v-for="item in wishlist" :key="item.id" class="wishlist-item">
                <img :src="`${host}/storage/${item.book.cover_image}`" alt="Bìa sách" class="wishlist-book-cover" />
                <div class="book-details" @click="openDetailModal(item.book.id)">
                    <p class="book-title">
                        <i class="fas fa-heart text-danger"></i> {{ item.book.title }}
                    </p>
                    <p>Tác giả: {{ item.book.author }}</p>
                    <p>Còn lại: <strong>{{ item.book.quantity }}</strong></p>
                    <p>
                        Trạng thái:
                        <strong :class="getStatusClass(getLatestTransactionStatus(item.book.transactions))">
                            {{ getLatestTransactionStatus(item.book.transactions) }}
                        </strong>
                    </p>
                </div>
                <div class="button-group">
                    <button @click="removeFromWishlist(item.book.id)" class="btn btn-outline-danger">
                        Bỏ yêu thích
                    </button>
                    <button @click="openBorrowModal(item.book.id)" class="btn btn-primary"
                        :disabled="!canBorrow(item.book.transactions)">
                        Mượn sách
                    </button>
                </div>
            </li>
        </ul>

        <!-- Thông báo khi không có sách yêu thích -->
        <div v-else class="no-wishlist text-center">
            <p>Hiện bạn chưa có sách nào trong danh sách yêu thích. <router-link to="/reader"> Có thể bạn quan
                    tâm</router-link></p>
        </div>

        <!-- Modal Hiển Thị Thông Tin Sách -->
        <BookDetailModal :bookId="selectedBookId" :isVisible="isModalVisible"
            @update:isVisible="isModalVisible = $event" />

        <!-- Modal Mượn Sách -->
        <BorrowBookModal :isVisible="isBorrowModalVisible" :bookId="selectedBookId"
            @update:isVisible="isBorrowModalVisible = $event" />

        <!-- Toast Notification -->
        <Toast :type="toastType" :visible="isToastVisible" @close="isToastVisible = false">{{ toastMessage }}</Toast>
    </div>
</template>

<script>
import axios from '../../../axios';
import BorrowBookModal from '../Borrow/BorrowBookModal.vue';
import BookDetailModal from '../Borrow/BookDetail.vue';
import Toast from '../../ToastMessage/ToastMessage.vue';
import EventBus from '../../../eventBus';

export default {
    name: "Wishlist",
    components: {
        BorrowBookModal,
        BookDetailModal,
        Toast,
    },
    data() {
        return {
            host: 'http://127.0.0.1:8000',
            wishlist: [],
            isModalVisible: false,
            isBorrowModalVisible: false,
            selectedBookId: null,
            isToastVisible: false,
            toastType: 'info',
            toastMessage: '',
            loading: true,
        };
    },
    methods: {
        async fetchWishlist() {
            this.loading = true;
            try {
                const response = await axios.get('/wishlist');
                if (response.data.success) {
                    this.wishlist = response.data.data;
                }
            } catch (error) {
                console.error('Error fetching wishlist:', error);
                this.showToast('error', 'Không thể tải danh sách yêu thích.');
            } finally {
                this.loading = false;
            }
        },
        async removeFromWishlist(bookId) {
            try {
                const response = await axios.delete(`/wishlist/remove`, {
                    data: { book_id: bookId }
                });
                console.log(response.data);
                if (response.data.success) {
                    this.wishlist = this.wishlist.filter(item => item.book.id !== bookId);
                    this.showToast('success', 'Đã xóa sách khỏi danh sách yêu thích.');
                } else {
                    this.showToast('error', 'Không thể xóa sách khỏi danh sách yêu thích.');
                }
            } catch (error) {
                console.error('Error removing from wishlist:', error);
                this.showToast('error', 'Không thể xóa sách khỏi danh sách yêu thích.');
            }
        },

        openBorrowModal(bookId) {
            this.selectedBookId = bookId;
            this.isBorrowModalVisible = true;
        },
        openDetailModal(bookId) {
            this.selectedBookId = bookId;
            this.isModalVisible = true;
        },
        getStatusClass(status) {
            switch (status) {
                case 'Có sẵn':
                    return 'text-success';
                case 'Đang chờ mượn':
                    return 'text-warning';
                case 'Đang mượn':
                    return 'text-danger';
                case 'Đang chờ trả':
                    return 'text-info';
                default:
                    return '';
            }
        },
        getLatestTransactionStatus(transactions) {
            // Nếu không có giao dịch nào hoặc trạng thái là cancelled hoặc completed
            if (!transactions || transactions.length === 0 ||
                transactions.some(tx => tx.status === 'cancelled') ||
                transactions.some(tx => tx.status === 'completed')) {
                return 'Có sẵn';
            }
            // Lấy giao dịch gần nhất
            const latestTransaction = transactions[0];
            switch (latestTransaction.status) {
                case 'pending':
                    return 'Đang chờ mượn';
                case 'approved':
                    return 'Đang mượn';
                case 'returned':
                    return 'Đang chờ trả';
                default:
                    return 'Không rõ';
            }
        },
        canBorrow(transactions) {
            const latestStatus = this.getLatestTransactionStatus(transactions);
            return latestStatus === 'Có sẵn' || latestStatus === 'cancelled' || latestStatus === 'completed';
        },
        showToast(type, message) {
            this.toastType = type;
            this.toastMessage = message;
            this.isToastVisible = true;
            setTimeout(() => {
                this.isToastVisible = false;
            }, 1500);
        }
    },
    mounted() {
        this.fetchWishlist();
        EventBus.$on('bookBorrowed', this.fetchWishlist);
    },
    beforeDestroy() {
        EventBus.$off('bookBorrowed', this.fetchWishlist);
    }
};
</script>




<style scoped>
.wishlist {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.no-wishlist {
    font-size: 1.2em;
    color: #888;
    padding: 20px;
}

.section-title {
    font-size: 2em;
    color: var(--primary-color);
    font-weight: bold;
    margin-bottom: 20px;
}

.wishlist-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    padding: 15px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px var(--box-shadow-color);
    transition: transform 0.2s ease-in-out;
}

.wishlist-item:hover {
    transform: scale(1.02);
}

.wishlist-book-cover {
    width: 60px;
    border-radius: 4px;
    transition: transform 0.3s ease-in-out;
}

.wishlist-item:hover .wishlist-book-cover {
    transform: scale(1.1);
}

.book-details {
    flex-grow: 1;
    margin-left: 10px;
}

.book-details p {
    margin: 5px 0;
}

.book-details p:first-child {
    font-weight: bold;
    font-size: 18px;
    color: #333;
}

.book-details p:last-child strong {
    font-weight: 600;
    font-size: 16px;
}

.button-group {
    display: flex;
    gap: 10px;
    /* Tạo khoảng cách giữa các nút */
}

.btn {
    padding: 8px 12px;
    font-size: 12px;
    border-radius: 4px;
    transition: background-color 0.3s, transform 0.2s;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    color: white;
}

.btn-primary:hover {
    background-color: var(--hover-color);
    transform: translateY(-2px);
}

.btn-primary:disabled {
    background-color: var(--primary-color);
    opacity: 0.7;
}

.btn-danger {
    background-color: #dc3545;
    border: none;
    color: white;
}

.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    background: white;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

.text-success {
    color: green;
}

.text-warning {
    color: orange;
}

.text-info {
    color: blue;
}

.text-danger {
    color: red;
}
</style>
