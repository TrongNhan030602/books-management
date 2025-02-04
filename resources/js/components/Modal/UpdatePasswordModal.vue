<template>
    <div v-if="isVisible" class="modal-backdrop" @click.self="closeModal">
        <div class="modal" :class="{ show: isVisible, hide: isFadingOut }">
            <div class="modal-header">
                <h5 class="modal-title">Đổi Mật Khẩu</h5>
                <button type="button" class="btn-close" @click="closeModal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Alert thông báo -->
                <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
                    {{ alert.message }}
                </ToastMessage>
                <!-- Form đổi mật khẩu -->
                <form @submit.prevent="submitForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" id="currentPassword" v-model="form.current_password" class="form-control"
                            :class="{ 'is-invalid': errors.current_password }" />
                        <div v-if="errors.current_password" class="invalid-feedback">{{ errors.current_password }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Mật khẩu mới</label>
                        <input type="password" id="newPassword" v-model="form.new_password" class="form-control"
                            :class="{ 'is-invalid': errors.new_password }" />
                        <div v-if="errors.new_password" class="invalid-feedback">{{ errors.new_password }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirmPassword" v-model="form.new_password_confirmation"
                            class="form-control" :class="{ 'is-invalid': errors.new_password_confirmation }" />
                        <div v-if="errors.new_password_confirmation" class="invalid-feedback">
                            {{ errors.new_password_confirmation }}
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>



<script>
import axios from '../../axios';
import ToastMessage from '../ToastMessage/ToastMessage.vue';

export default {
    name: 'UpdatePasswordModal',
    components: {
        ToastMessage
    },
    props: {
        isVisible: Boolean,
    },
    data() {
        return {
            form: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
            errors: {},
            alert: {
                visible: false,
                message: '',
                type: 'info', // 'success', 'error', 'info'
            },
            isFadingOut: false
        };
    },
    methods: {
        closeModal() {
            this.isFadingOut = true;
            setTimeout(() => {
                this.$emit('update:isVisible', false);
                this.isFadingOut = false;
            }, 300); // Thời gian phải trùng khớp với thời gian của animation
        },
        validateForm() {
            this.errors = {};
            if (!this.form.current_password) this.errors.current_password = 'Mật khẩu hiện tại là bắt buộc.';
            if (!this.form.new_password) this.errors.new_password = 'Mật khẩu mới là bắt buộc.';
            if (!this.form.new_password_confirmation) this.errors.new_password_confirmation = 'Xác nhận mật khẩu là bắt buộc.';
            else if (this.form.new_password !== this.form.new_password_confirmation) {
                this.errors.new_password_confirmation = 'Xác nhận mật khẩu không khớp.';
            }
        },
        async submitForm() {
            this.validateForm();
            if (Object.keys(this.errors).length) return;

            try {
                await axios.put('/account/update-password', {
                    current_password: this.form.current_password,
                    new_password: this.form.new_password,
                    new_password_confirmation: this.form.new_password_confirmation,
                });

                // Thông báo đã đổi mật khẩu
                this.showAlert('Mật khẩu đã cập nhật! Vui lòng đăng nhập lại để tiếp tục.', 'success');
                setTimeout(() => {
                    localStorage.removeItem('access_token');
                    this.$router.push({ name: 'Login' });
                }, 3000);

            } catch (error) {
                console.error('Failed to update password:', error);
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.showAlert('Đã xảy ra lỗi. Mật khẩu hiện tại chưa đúng.', 'error');
                }
            }
        },
        showAlert(message, type = 'info') {
            this.alert.message = message;
            this.alert.type = type;
            this.alert.visible = true;
            setTimeout(() => {
                this.alert.visible = false;
            }, 3000); // Thời gian hiển thị thông báo là 3 giây
        }
    }
};
</script>





<style scoped>
@import url("../../../css/form.css");

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
    z-index: 1000;
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

.modal.show {
    animation: fadeInScale 0.3s ease forwards;
}

.modal.hide {
    animation: fadeOutScale 0.3s ease forwards;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--input-border-color);
    padding-bottom: 15px;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: var(--font-weight-bold);
    color: var(--secondary-color);
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-color);
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
