<template>
    <div class="reviews">
        <h2 class="reviews-title section-title">Đánh Giá Của Bạn</h2>
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>
        <!-- Thông báo nếu không có đánh giá nào -->
        <div v-if="reviews.data.length === 0" class="alert alert-info text-center" role="alert">
            Hiện tại bạn chưa có đánh giá nào.
        </div>

        <ul class="list-group" v-else>
            <li v-for="review in reviews.data" :key="review.id"
                class="list-group-item d-flex justify-content-between align-items-center review-item">
                <div>
                    <div class="review-book-info d-flex align-items-center">
                        <img :src="`${host}/storage/${review.book.cover_image}`" alt="Book Cover" class="book-cover" />
                        <p class="review-book-title ms-2">
                            <i class="fas fa-book"></i> {{ review.book.title }}
                            <span class="rating">- Đánh giá: {{ review.rating }} sao</span>
                        </p>
                    </div>
                    <p class="review-comment">{{ review.comment }}</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" @click="editReview(review)">Chỉnh Sửa</button>
                    <button class="btn btn-outline-danger" @click="confirmDeleteReview(review.id)">Xóa</button>
                </div>
            </li>
        </ul>

        <!-- Confirm Modal -->
        <ConfirmModal :isVisible="showConfirmModal" @update:isVisible="showConfirmModal = $event"
            @confirm="deleteReview(selectedReviewId)" title="Xác nhận xóa"
            message="Bạn có chắc chắn muốn xóa đánh giá này không?" cancelButtonText="Hủy" confirmButtonText="Xóa" />

        <!-- Modal cập nhật -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật đánh giá</h5>
                    <button type="button" class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Đánh giá</label>
                        <div class="star-rating">
                            <span v-for="star in [1, 2, 3, 4, 5]" :key="star" class="star"
                                :class="{ active: star <= currentReview.rating }" @click="setRating(star)">
                                ★
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Nhận xét</label>
                        <textarea v-model="currentReview.comment" class="form-control" rows="3"
                            placeholder="Nhập nhận xét của bạn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" @click="updateReview">Cập nhật</button>
                </div>

            </div>
        </div>

        <!-- Toast Notification -->
        <Toast :visible="toastVisible" :type="toastType" @close="toastVisible = false">
            {{ toastMessage }}
        </Toast>
    </div>
</template>



<script>
import axios from '..//../../axios';
import ConfirmModal from '../../Modal/ConfirmationModal.vue';
import Toast from '../../ToastMessage/ToastMessage.vue';
export default {
    name: "Reviews",
    components: {
        ConfirmModal,
        Toast,  // Thêm Toast vào danh sách component
    },
    data() {
        return {
            host: 'http://127.0.0.1:8000',
            reviews: {
                data: [],
                total: 0,
            },
            showModal: false,
            showConfirmModal: false,
            selectedReviewId: null,
            currentReview: {},
            toastVisible: false,
            toastType: 'success',
            toastMessage: '',
            loading: false,

        };
    },
    created() {
        this.fetchUserReviews();
    },
    methods: {
        async fetchUserReviews() {
            try {
                this.loading = true;
                const response = await axios.get(`reviews/my-reviews`);

                if (response.data.success) {
                    this.reviews = response.data.data; // Lưu dữ liệu đánh giá
                    if (this.reviews.data.length === 0) {
                        console.warn("Không có đánh giá nào."); // In ra thông báo cảnh báo
                    }
                } else {
                    console.error("Lỗi khi lấy danh sách đánh giá:", response.data.message);
                }
            } catch (error) {
                console.error("Lỗi khi gọi API:", error);
            } finally {
                this.loading = false;
            }
        },

        editReview(review) {
            this.currentReview = { ...review };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.currentReview = {};
        },
        setRating(star) {
            this.currentReview.rating = star;
        },
        async updateReview() {
            try {
                await axios.put(`reviews/my-reviews/${this.currentReview.id}`, {
                    rating: this.currentReview.rating,
                    comment: this.currentReview.comment,
                });
                this.fetchUserReviews();
                this.closeModal();
                this.showToast('success', 'Cập nhật đánh giá thành công!');  // Hiển thị Toast
            } catch (error) {
                console.error("Lỗi khi cập nhật đánh giá:", error);
                this.showToast('error', 'Có lỗi xảy ra khi cập nhật đánh giá!');
            }
        },
        confirmDeleteReview(reviewId) {
            this.selectedReviewId = reviewId;
            this.showConfirmModal = true;
        },
        async deleteReview(reviewId) {
            try {
                await axios.delete(`reviews/my-reviews/${reviewId}`);
                this.fetchUserReviews();
                this.showToast('success', 'Đánh giá đã được xóa thành công!');  // Hiển thị Toast
            } catch (error) {
                console.error("Lỗi khi xóa đánh giá:", error);
                this.showToast('error', 'Có lỗi xảy ra khi xóa đánh giá!');
            }
        },
        showToast(type, message) {
            this.toastType = type;
            this.toastMessage = message;
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;

            }, 2000);
        }
    },
};
</script>


<style scoped>
.reviews {
    padding: 20px;
    background-color: var(--light-background);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.list-group {
    margin-bottom: 20px;
}

.review-item {
    transition: transform 0.2s;
}

.review-item:hover {
    transform: scale(1.02);
    background-color: var(--hover-background);
}

.review-book-title {
    font-weight: bold;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: var(--white-color);
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
    width: 500px;
    max-width: 600px;
    position: relative;
    padding: 20px;
    animation: modalIn 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid var(--secondary-color);
    padding-bottom: 15px;
    margin-bottom: 20px;
    background: var(--light-bg-color);
    border-radius: 12px 12px 0 0;
}

.modal-header h5 {
    margin: 0;
    color: var(--secondary-color);
    font-weight: bold;
}

.modal-body {
    font-size: 16px;
    color: var(--text-color);
    text-align: left;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
}

.modal-footer .btn {
    margin-left: 10px;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 6px;
    transition: background 0.3s ease, transform 0.3s ease;
}

.btn-secondary {
    background: #6c757d;
    color: var(--white-color);
    border: none;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-primary {
    background: var(--primary-color);
    color: var(--white-color);
    border: none;
}

.btn-primary:hover {
    background: var(--primary-color);
    opacity: 0.9;
}

.btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
    padding: 6px 12px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 4px;
    transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    margin-right: 8px;
}

.btn-outline-primary:hover {
    background: #316259;
    border-color: var(--primary-color);
    color: #fff;
    opacity: 0.8;
}

.rating {
    font-style: italic;
    color: var(--secondary-color);
}

.review-comment {
    margin-top: 5px;
    color: var(--text-color);
}

.book-cover {
    width: 50px;
    height: auto;
    border-radius: 4px;
    margin-right: 10px;
}

/* Styles cho ngôi sao */
.star {
    font-size: 30px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.3s;
}

.star.active {
    color: #ffcc00;
}

.star:hover {
    color: #ffcc00;
}

/* Animation */
@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
