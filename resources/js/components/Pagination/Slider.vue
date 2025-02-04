<template>
    <div>
        <!-- SliderComponent -->
        <div class="custom-slider">
            <div class="slides-container">
                <div class="slide" v-for="(slide, index) in slides" :key="index" :class="{
                    active: index === currentSlide,
                    'prev-slide': index === prevSlide,
                    'next-slide': index === nextSlide,
                }">
                    <img :src="'http://127.0.0.1:8000/storage/' + slide.cover_image" :alt="slide.title"
                        @click="openBorrowModal(slide)" />
                    <div class="slide-info">
                        <h3>{{ slide.title }}</h3>
                        <p>{{ slide.author }}</p>
                        <p class="transaction-status" v-if="slide.latestTransaction">
                            Trạng thái: {{ getTransactionStatus(slide.latestTransaction.status) }}
                        </p>
                        <p v-else>Chưa có giao dịch nào.</p>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button @click="prev" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button @click="next" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- ToastMessage for notifications -->
        <ToastMessage v-if="showToast" :type="toastType" :visible="showToast" @close="showToast = false">
            {{ toastMessage }}
        </ToastMessage>

        <!-- Borrow Modal -->
        <div v-if="selectedBook" class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mượn sách: {{ selectedBook.title }}</h5>
                        <button type="button" class="btn-close" @click="closeBorrowModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="borrowBook">
                            <div class="mb-3">
                                <label for="borrowDate" class="form-label">Ngày mượn</label>
                                <input type="date" class="form-control" v-model="borrowForm.borrow_date"
                                    @input="checkFormValidity" required>
                            </div>
                            <div class="mb-3">
                                <label for="returnDate" class="form-label">Ngày trả</label>
                                <input type="date" class="form-control" v-model="borrowForm.return_date"
                                    @input="checkFormValidity" required>
                            </div>
                            <div v-if="dateError" class="text-danger">{{ dateError }}</div>
                            <button type="submit" class="btn btn-primary"
                                :disabled="!isFormValid || loading || !canBorrowBook">
                                <span v-if="loading" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                <span v-else><i class="fas fa-check"></i> Xác nhận mượn</span>
                            </button>
                            <div v-if="!canBorrowBook" class="text-danger text-center">Không thể mượn sách. (Có thể yêu
                                cầu đang được duyệt hoặc bạn chưa trả sách này!)</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrop for modal -->
        <div v-if="selectedBook" class="modal-backdrop fade show"></div>
    </div>
</template>

<script>
import axios from '../../axios';
import EventBus from '../../eventBus';
import ToastMessage from '../ToastMessage/ToastMessage.vue';

export default {
    name: "SliderComponent",
    components: {
        ToastMessage
    },
    data() {
        return {
            slides: [],
            currentSlide: 0,
            selectedBook: null,
            borrowForm: {
                borrow_date: '',
                return_date: ''
            },
            dateError: '',
            showToast: false,
            toastMessage: '',
            toastType: 'info',
            loading: false,
        };
    },
    async created() {
        await this.fetchBooks();
        EventBus.$on('borrowRequestCancelled', this.fetchBooks);
        EventBus.$on('bookBorrowed', this.fetchBooks);
    },
    beforeDestroy() {
        EventBus.$off('borrowRequestCancelled', this.fetchBooks);
        EventBus.$off('bookBorrowed', this.fetchBooks);
    },
    computed: {
        availableSlides() {
            return this.slides.filter(book => book.status === 'available');
        },
        isFormValid() {
            const { borrow_date, return_date } = this.borrowForm;
            const today = new Date().toISOString().split('T')[0];
            const isBorrowDateValid = borrow_date && borrow_date >= today;
            const isReturnDateValid = return_date && return_date > borrow_date;

            this.dateError = '';

            if (borrow_date && !isBorrowDateValid) {
                this.dateError = 'Ngày mượn không được trong quá khứ.';
                return false;
            }
            if (borrow_date && return_date && !isReturnDateValid) {
                this.dateError = 'Ngày trả phải sau ngày mượn.';
                return false;
            }

            return true;
        },
        prevSlide() {
            return (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        },
        nextSlide() {
            return (this.currentSlide + 1) % this.slides.length;
        },
        canBorrowBook() {
            const latestTransaction = this.selectedBook?.latestTransaction;
            // Kiểm tra nếu có giao dịch, nếu không thì cho phép mượn
            return !latestTransaction ||
                latestTransaction.status === 'cancelled' ||
                latestTransaction.status === 'completed';
        },
    },
    methods: {
        fetchBooks() {
            axios.get('/books/my-books')
                .then(response => {
                    if (response.data.success) {
                        this.slides = response.data.data.slice(0, 5).map(book => {
                            // Lấy giao dịch gần nhất
                            const transactions = book.borrow_transactions;
                            const latestTransaction = transactions[Object.keys(transactions).pop()];

                            return {
                                ...book,
                                latestTransaction // Thêm giao dịch gần nhất vào book
                            };
                        });
                    }
                })
                .catch(error => {
                    console.error("Error fetching books:", error);
                });
        },
        getRandomBooks(books, count) {
            if (books.length <= count) {
                return books;
            }
            return books.sort(() => 0.5 - Math.random()).slice(0, count);
        },
        getTransactionStatus(status) {
            switch (status) {
                case 'completed':
                    return 'Có sẵn';
                case 'cancelled':
                    return 'Có sẵn';
                case 'pending':
                    return 'Đang chờ duyệt';
                case 'approved':
                    return 'Đã duyệt';
                case 'returned':
                    return 'Đang chờ duyệt trả';
                default:
                    return 'Trạng thái không xác định';
            }
        },
        openBorrowModal(book) {
            const today = new Date().toISOString().split('T')[0];
            this.selectedBook = book;
            this.borrowForm = { borrow_date: today, return_date: '' };
            this.dateError = '';
            this.checkFormValidity();
        },

        closeBorrowModal() {
            this.selectedBook = null;
            this.borrowForm = { borrow_date: '', return_date: '' };
        },

        checkFormValidity() {
            this.dateError = ''; // Reset error message
        },

        async borrowBook() {
            if (!this.canBorrowBook) {
                this.toastMessage = 'Bạn không thể mượn sách này do trạng thái giao dịch.';
                this.toastType = 'error';
                this.showToast = true;
                this.autoHideToast();
                return;
            }
            this.loading = true;
            try {
                const response = await axios.post('/borrow-transaction', {
                    book_id: this.selectedBook.id,
                    borrow_date: this.borrowForm.borrow_date,
                    return_date: this.borrowForm.return_date
                });
                if (response.data.success) {
                    this.toastMessage = response.data.message;
                    this.toastType = 'success';
                    this.showToast = true;
                    this.autoHideToast();
                    EventBus.$emit('bookBorrowed');

                    this.closeBorrowModal();
                } else {
                    this.toastMessage = response.data.message;
                    this.toastType = 'error';
                    this.showToast = true;
                    this.autoHideToast();
                }
            } catch (error) {
                console.error('Lỗi khi mượn sách:', error);
                this.toastMessage = 'Không thể thực hiện mượn sách, vui lòng thử lại!';
                this.toastType = 'error';
                this.showToast = true;
                this.autoHideToast();
            } finally {
                this.loading = false;
            }
        },
        autoHideToast() {
            setTimeout(() => {
                this.showToast = false;
            }, 2000);
        },

        next() {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        },

        prev() {
            this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        }
    }
};
</script>








<style scoped>
.custom-slider {
    position: relative;
    width: 90%;
    height: 500px;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto;
    border-radius: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    background-image: url("../../../images/background-login.jpg");
    filter: brightness(90%);
}

.slides-container {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    width: 100%;
    height: 100%;
    perspective: 1000px;
}

.slide {
    position: absolute;
    transition: transform 0.7s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.7s ease;
    transform: scale(0.7) rotateY(-20deg);
    opacity: 0.5;
    z-index: 1;
    width: 300px;
    height: 400px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.slide.active {
    transform: scale(1.2) rotateY(0);
    opacity: 1;
    z-index: 3;
}

.slide.prev-slide {
    transform: translateX(-200px) scale(0.9) rotateY(-15deg);
    z-index: 2;
    opacity: 0.8;
}

.slide.next-slide {
    transform: translateX(200px) scale(0.9) rotateY(15deg);
    z-index: 2;
    opacity: 0.8;
}

img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

img:hover {
    transform: scale(1.05);
}

.slide-info {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    text-align: center;
    background-color: rgba(0, 0, 0, 0.6);
    padding: 10px;
    border-radius: 10px;
    width: 80%;
    font-size: 1.2rem;
}

.slider-controls {
    display: flex;
    justify-content: space-between;
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    padding: 0 20px;
    z-index: 10;
}

.slider-controls button {
    background-color: #52C2DB;
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.slider-controls button:hover {
    background-color: #35a9c3;

}

/* Modal styles */

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

button.btn-primary {
    background-color: var(--primary-color);
    border: none;
    padding: 10px 20px;
    border-radius: 50px;
    color: white;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button.btn-primary:hover {
    background-color: var(--primary-color);
    opacity: 0.9;

}

button.btn-close {
    background-color: transparent;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    color: #333;
    position: absolute;
    top: 10px;
    right: 10px;
}

input[type="date"] {
    border-radius: 5px;
    border: 1px solid #ccc;
    padding: 10px;
    width: 100%;
    transition: border-color 0.3s ease;
}

input[type="date"]:focus {
    border-color: var(--primary-color);
    outline: none;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}


.modal-header h5 {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}
</style>
