<template>
    <transition name="fade">
        <div v-show="isVisible" class="modal fade show" style="display: block;" tabindex="-1" role="dialog"
            @click.self="closeModal">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ book.title }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="book.images.length > 0" class="image-gallery">
                            <img :src="`${host}/storage/${selectedImage}`" class="main-image" alt="Bìa sách chính" />
                            <div class="image-grid">
                                <div v-for="(image, index) in book.images" :key="index" class="thumbnail"
                                    :class="{ active: selectedImage === image.image_url }"
                                    @click="selectedImage = image.image_url">
                                    <img :src="`${host}/storage/${image.image_url}`" alt="Ảnh sách" />
                                </div>
                            </div>
                        </div>

                        <div class="book-info row mt-4">
                            <div class="book-detail col-md-6 col-sm-12">
                                <h3 class="text-primary">Thông tin sách</h3>
                                <p><i class="fas fa-user"></i> <strong>Tác giả:</strong> {{ book.author }}</p>
                                <p><i class="fas fa-calendar-alt"></i> <strong>Năm phát hành:</strong> {{
                                    book.published_year }}</p>
                                <p><i class="fas fa-info-circle"></i> <strong>Mô tả:</strong></p>
                                <p v-if="isLongDescription && !showFullDescription">
                                    {{ shortDescription }}
                                    <button class="btn-show-more" @click="showFullDescription = true">Xem thêm</button>
                                </p>
                                <p v-else-if="showFullDescription">{{ book.description }}</p>

                                <p><i class="fas fa-cubes"></i> <strong>Số lượng còn lại:</strong> {{ book.quantity }}
                                </p>
                                <p><i class="fas fa-money-bill-wave"></i> <strong>Giá:</strong>
                                    <span class="book-price">{{ formatCurrency(book?.price) }}</span>
                                </p>
                                <p><i class="fa-solid fa-location-crosshairs"></i> <strong>Vị trí:</strong> <span
                                        class="text-info">{{
                                            book?.location }}</span></p>

                            </div>

                            <div class="borrowed-info col-md-6 col-sm-12">
                                <h3 class="text-info">Lịch sử mượn của bạn</h3>
                                <div v-if="completedTransactions.length > 0">
                                    <ul class="transaction-list">
                                        <li v-for="transaction in completedTransactions" :key="transaction.id"
                                            class="transaction-item">
                                            <p><strong>Mã giao dịch:</strong> {{ transaction.id }}</p>
                                            <p><strong>Ngày mượn:</strong> {{ formatDate(transaction.borrow_date) }}</p>
                                            <p><strong>Ngày trả:</strong> {{ formatDate(transaction.return_date) }}</p>
                                            <p><strong>Trạng thái:</strong>
                                                <span class="text-success"> Hoàn tất</span>
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <p>Bạn chưa có giao dịch hoàn thành nào.</p>
                                </div>
                            </div>
                        </div>

                        <div class="reviews mt-4">
                            <h3 class="text-info">Các đánh giá</h3>
                            <div v-if="book.reviews.length > 0">
                                <ul class="review-list">
                                    <li v-for="review in book.reviews" :key="review.id" class="review-item">
                                        <div class="review-rating">
                                            <span class="rating">{{ review.rating }} ★</span>
                                            <span class="review-date"><small><em>{{ formatDate(review.created_at)
                                                        }}</em></small></span>
                                        </div>
                                        <p class="review-comment">{{ review.comment }}</p>
                                    </li>
                                </ul>
                            </div>
                            <div v-else>
                                <p>Chưa có đánh giá nào cho sách này.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import axios from '../../../axios';
import EventBus from '../../../eventBus';

export default {
    props: {
        bookId: {
            type: Number,
            required: true
        },
        isVisible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            book: {
                images: [],
                user_transactions: [],
                reviews: []
            },
            selectedImage: null,
            host: "http://127.0.0.1:8000",
            showFullDescription: false,
        };
    },
    watch: {
        bookId: {
            immediate: true,
            handler() {
                if (this.bookId && this.isVisible) {
                    this.fetchBookDetail();
                }
            }
        },
        isVisible(newVal) {
            if (newVal && this.bookId) {
                this.fetchBookDetail();
            }
        },
    },
    computed: {
        completedTransactions() {
            const completed = this.book.user_transactions.filter(transaction => {
                return transaction.status === 'completed';
            });
            return completed.sort((a, b) => b.id - a.id);
        },
        shortDescription() {
            const maxLength = 100;
            if (this.book.description) {
                return this.book.description.length > maxLength
                    ? this.book.description.slice(0, maxLength) + '...'
                    : this.book.description;
            }
            return '';
        },
        isLongDescription() {
            return this.book.description && this.book.description.length > 100;
        }
    },
    mounted() {
        EventBus.$on('borrowRequestCancelled', () => {
            this.fetchBookDetail();
        });
        EventBus.$on('bookBorrowed', () => {
            this.fetchBookDetail();
        });
    },
    methods: {
        async fetchBookDetail() {
            try {
                this.book = {
                    images: [],
                    user_transactions: [],
                    reviews: [],
                    description: '',
                };

                const response = await axios.get(`/books/${this.bookId}/details`);

                if (response.data.success) {
                    this.book = response.data.data.book;
                    this.book.user_transactions = response.data.data.user_transactions || [];
                    this.book.reviews = response.data.data.book.reviews || [];
                    if (this.book.images.length > 0) {
                        this.selectedImage = this.book.images[0].image_url;
                    }
                }
            } catch (error) {
                console.error("Error fetching book details:", error);
            }
        },

        closeModal() {
            this.$emit('update:isVisible', false);
        },

        formatDate(date) {
            if (!date) return "Chưa trả";
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return new Date(date).toLocaleDateString('vi-VN', options);
        },
        formatCurrency(amount) {
            if (isNaN(amount) || amount === null) {
                return '';
            }

            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        },
    },
    beforeDestroy() {
        EventBus.$off('bookStatusUpdated', this.fetchBookDetails);
    },
};
</script>

<style scoped>
.modal.show.fade {
    animation: fadeInScale 0.3s ease forwards;

}



.modal-content {
    animation: fadeInScale 0.3s ease forwards;
    transform: scale(0.8);
    box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3), -3px -3px 6px rgba(0, 0, 0, 0.3);
}

.modal.show.fade {
    animation: fadeInScale 0.3s ease forwards;
}

.modal-header {
    background-color: var(--primary-color);
    color: white;
}

.modal-body {
    background-color: var(--light-bg-color);

}

.book-price {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-left: 10px;
}

.book-detail p {
    margin-bottom: 10px;
    font-size: 1rem;
    line-height: 1.6;
}

.book-detail h3 {
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.book-info {
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.text-primary {
    color: var(--primary-color) !important;
    font-size: 1.6rem !important;
}

.text-info {
    color: var(--secondary-color) !important;
}

.btn-show-more {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 5px;
    cursor: pointer;
    transition: filter 0.3s ease;
}

.btn-show-more:is(:hover, :focus) {
    filter: brightness(120%);
}


.main-image {
    width: 100%;
    object-fit: cover;
    width: 100%;
    max-height: 360px;
    object-fit: contain;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, border 0.3s;
}

.main-image:hover {
    transform: scale(1.05);
    filter: brightness(110%);
}

/* Grid ảnh */
.image-grid {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.thumbnail {
    width: 60px;
    height: 60px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 0.3s, border 0.3s;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}

.thumbnail.active {
    border-color: var(--primary-color);
}

.thumbnail:hover {
    transform: scale(1.05);
}

/* Đánh giá */
.review-item {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    transition: box-shadow 0.3s ease, transform 0.2s;
}

.review-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: scale(1.02);
}
</style>
