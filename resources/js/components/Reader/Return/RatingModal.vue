<template>
    <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <header class="modal-header">
                <h2>Đánh Giá Sách</h2>
                <button @click="closeModal" class="btn-close" aria-label="Close">&times;</button>
            </header>
            <main class="modal-body">
                <p> <strong>{{ book.book.title }}</strong></p>

                <div class="rating-container">
                    <label for="rating" class="">Vui lòng chọn đánh giá</label>
                    <div class="star-rating">
                        <i v-for="n in 5" :key="n" class="star" :class="{ 'active': n <= rating }"
                            @click="setRating(n)">&#9733;</i> <!-- Sử dụng ký tự ngôi sao -->
                    </div>
                </div>

                <div class="comment-section">
                    <label class="comment-label" for="comment">Nhận xét:</label>
                    <textarea class="comment-textarea" v-model="comment" id="comment" rows="3"
                        placeholder="Nhập nhận xét của bạn..."></textarea>
                </div>
                <Toast :type="toastType" :visible="toastVisible" @close="toastVisible = false">
                    {{ toastMessage }}
                </Toast>
            </main>
            <footer class="modal-footer">
                <button @click="closeModal" class="btn btn-secondary">Hủy</button>
                <button @click="submitRating" class="btn btn-danger">Gửi Đánh Giá</button>
            </footer>
        </div>
    </div>
</template>

<script>
import axios from '../../../axios';
import Toast from '../../ToastMessage/ToastMessage.vue'; // Đảm bảo bạn nhập đúng đường dẫn

export default {
    name: "RatingModal",
    components: {
        Toast
    },
    props: {
        isVisible: {
            type: Boolean,
            required: true,
        },
        book: {
            type: Object,
            required: true,
        },
        transaction: {
            type: Object,
            default: () => ({}),
        }
    },
    data() {
        return {
            rating: 0, // Giá trị mặc định là 0 (chưa đánh giá)
            comment: '',
            toastVisible: false,
            toastMessage: '',
            toastType: 'info',
            loading: false,
        };
    },
    methods: {
        closeModal() {
            this.rating = 0;
            this.comment = '';
            this.toastVisible = false;
            this.$emit('update:isVisible', false);
        },

        setRating(n) {
            this.rating = n;
        },

        submitRating() {
            if (!this.rating || !this.comment) {
                this.showToast('Vui lòng chọn mức đánh giá và nhập nhận xét', 'error');
                return;
            }
            if (!this.book || !this.book.book) {
                console.error("Đối tượng book không hợp lệ, không thể lấy ID sách.");
                this.showToast('Không thể lấy thông tin sách để đánh giá.', 'error');
                return;
            }
            const bookId = this.book.book.id;
            this.loading = true;

            if (!bookId) {
                console.error("Không tìm thấy book_id, vui lòng kiểm tra lại.");
                this.showToast('Không thể lấy thông tin sách để đánh giá.', 'error');
                return;
            }

            const ratingData = {
                book_id: bookId,
                rating: this.rating,
                comment: this.comment,
            };

            axios.post('/reviews', ratingData) // Chỉnh sửa endpoint
                .then(response => {
                    this.showToast('Đánh giá đã được gửi thành công!', 'success');
                    this.closeModal();
                })
                .catch(error => {
                    console.error('Lỗi từ server:', error.response.data);
                    this.showToast(error.response.data.message || 'Đã xảy ra lỗi khi gửi đánh giá.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        showToast(message, type) {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        }
    }
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    backdrop-filter: blur(5px);
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

.modal-header h2 {
    margin: 0;
    font-size: 1.6rem;
    color: var(--secondary-color);
    font-weight: bold;
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--secondary-color);
    transition: color 0.3s ease;
}

.btn-close:hover {
    color: var(--hover-color);
}

.modal-body {
    font-size: 16px;
    color: var(--text-color);
    text-align: center;
}

/* Comment Section Styling */
.comment-section {
    margin-top: 20px;
    text-align: left;
}

.comment-label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.comment-textarea {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid var(--secondary-color);
    transition: border-color 0.3s ease;
}

.comment-textarea:focus {
    outline: none;
    border-color: var(--hover-color);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
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
    transform: scale(1.05);
}

.btn-danger {
    background: var(--primary-color);
    color: var(--white-color);
    border: none;
}

.btn-danger:hover {
    background: var(--primary-color);
    transform: scale(1.05);
    opacity: 0.9;
}

.rating-container {
    margin: 15px 0;
}

.star-rating {
    display: flex;
    justify-content: center;
}

.star {
    font-size: 30px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.3s;
}

.star-rating:hover .star {
    color: #ffcc00;
}

.star:hover~.star {
    color: #ccc;
}

.star.active {
    color: #ffcc00;
}

.star:hover {
    color: #ffcc00;
}

.star:hover~.star {
    color: #ccc;
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
