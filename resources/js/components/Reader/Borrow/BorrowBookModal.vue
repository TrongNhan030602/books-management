<template>
    <div v-if="isVisible" class="modal-backdrop" @click.self="closeModal">
        <div class="modal" :class="{ show: isVisible, hide: isFadingOut }">
            <div class="modal-header">
                <h5 class="modal-title">Mượn sách</h5>
                <button type="button" class="btn-close" @click="closeModal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- ToastMessage thông báo -->
                <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
                    {{ alert.message }}
                </ToastMessage>

                <form @submit.prevent="submitForm">
                    <div class="mb-3">
                        <label for="borrow_date" class="form-label">Ngày mượn</label>
                        <input type="date" id="borrow_date" v-model="form.borrow_date" class="form-control"
                            :class="{ 'is-invalid': errors.borrow_date }" />
                        <div v-if="errors.borrow_date" class="invalid-feedback">{{ errors.borrow_date }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="return_date" class="form-label">Ngày trả</label>
                        <input type="date" id="return_date" v-model="form.return_date" class="form-control"
                            :class="{ 'is-invalid': errors.return_date }" />
                        <div v-if="errors.return_date" class="invalid-feedback">{{ errors.return_date }}</div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from '../../../axios';
import EventBus from '../../../eventBus';
import ToastMessage from '../../ToastMessage/ToastMessage.vue';

export default {
    name: 'BorrowBookModal',
    components: {
        ToastMessage
    },
    props: {
        isVisible: Boolean,
        bookId: Number
    },
    data() {
        return {
            form: {
                book_id: null,
                borrow_date: new Date().toISOString().split('T')[0],
                return_date: '',
            },
            isFadingOut: false,
            errors: {},
            alert: {
                visible: false,
                message: '',
                type: 'info' // 'success', 'error', 'info'
            }
        };
    },
    watch: {
        bookId(newVal) {
            this.form.book_id = newVal;
        },
        'form.return_date'(newVal) {
            if (new Date(newVal) <= new Date(this.form.borrow_date)) {
                this.errors.return_date = 'Ngày trả phải lớn hơn ngày mượn.';
            } else {
                this.errors.return_date = '';
            }
        }
    },
    methods: {
        closeModal() {
            this.resetForm();
            this.isFadingOut = true;
            setTimeout(() => {
                this.$emit('update:isVisible', false);
                this.isFadingOut = false;
            }, 300);
        },
        resetForm() {
            this.form = {
                book_id: this.bookId,
                borrow_date: new Date().toISOString().split('T')[0],
                return_date: '',
            };
            this.errors = {};
            this.alert = {
                visible: false,
                message: '',
                type: 'info',
            };
        },

        validateForm() {
            this.errors = {};
            if (!this.form.borrow_date) {
                this.errors.borrow_date = 'Ngày mượn là bắt buộc.';
            } else if (new Date(this.form.borrow_date) < new Date().setHours(0, 0, 0, 0)) {
                this.errors.borrow_date = 'Ngày mượn không được trước ngày hôm nay.';
            }
            if (!this.form.return_date) {
                this.errors.return_date = 'Ngày trả là bắt buộc.';
            } else if (new Date(this.form.return_date) <= new Date(this.form.borrow_date)) {
                this.errors.return_date = 'Ngày trả phải lớn hơn ngày mượn.';
            }
        },
        async submitForm() {
            this.validateForm();
            if (Object.keys(this.errors).length) return;

            try {
                if (!this.form.book_id) {
                    this.showAlert('Không tìm thấy ID sách.', 'error');
                    return;
                }
                const response = await axios.post('/borrow-transaction', this.form);

                if (response.data.success) {
                    this.showAlert(response.data.message, 'success');
                    // Phát sự kiện đã mượn
                    EventBus.$emit('bookBorrowed');
                    setTimeout(() => {
                        this.closeModal();
                    }, 1000);

                } else {
                    this.showAlert('Không thể mượn sách: ' + response.data.error, 'error');
                }
            } catch (error) {
                console.error('Lỗi khi gọi API:', error);
                if (error.response) {
                    this.showAlert('Đã xảy ra lỗi khi mượn sách: ' + (error.response.data.error || 'Lỗi không xác định'), 'error');
                } else if (error.request) {
                    this.showAlert('Đã xảy ra lỗi khi mượn sách: Không nhận được phản hồi từ server', 'error');
                } else {
                    this.showAlert('Đã xảy ra lỗi khi mượn sách: ' + error.message, 'error');
                }
            }
        }
        ,

        showAlert(message, type = 'info') {
            this.alert.message = message;
            this.alert.type = type;
            this.alert.visible = true;
            console.log('Alert:', message);
            setTimeout(() => {
                this.alert.visible = false;
            }, 3000);
        }
    },

};
</script>





<style scoped>
@import url("../../../../css/form.css");

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.modal {
    background: var(--white-color);
    border-radius: 8px;
    box-shadow: 0 4px 8px var(--box-shadow-color);
    width: 90%;
    max-width: 500px;
    padding: 20px;
    position: relative;
    color: var(--text-color);
    font-family: var(--font-family-primary);
    display: block;
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal.show {
    opacity: 1;
    transform: scale(1);
}

.modal.hide {
    opacity: 0;
    transform: scale(0.95);
}

.modal.show {
    animation: fadeInScale 0.3s ease forwards;
}

.modal.hide {
    animation: fadeOutScale 0.3s ease forwards;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeOutScale {
    from {
        opacity: 1;
        transform: scale(1);
    }

    to {
        opacity: 0;
        transform: scale(0.95);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--input-border-color);
    padding-bottom: 15px;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    color: var(--secondary-color);
    text-align: center;
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--secondary-color);
}

.modal-body {
    padding: 15px 0;
}

.modal-body .form-control {
    background: var(--light-bg-color);
    border: 1px solid var(--input-border-color);
    padding: 12px;
    font-size: 16px;
    border-radius: 6px;
}

.modal-body .form-control:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 8px rgba(48, 149, 217, 0.2);
}

.modal-body .form-label {
    font-weight: var(--font-weight-medium);
    color: var(--text-color);
}

.modal-body .btn-primary {
    background: var(--secondary-color);
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: var(--font-weight-bold);
    border-radius: 6px;
    width: 100%;
    transition: background 0.3s ease;
}

.modal-body .btn-primary:hover {
    background: var(--hover-color);
    cursor: pointer;
}

.is-invalid {
    border-color: var(--error-color);
}

.invalid-feedback {
    color: var(--error-color);
}
</style>
