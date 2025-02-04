<template>
    <div v-if="isVisible" class="modal-backdrop" @click.self="closeModal">
        <div class="modal" :class="{ show: isVisible, hide: isFadingOut }">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật Avatar</h5>
                <button type="button" class="btn-close" @click="closeModal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- ToastMessage thông báo -->
                <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
                    {{ alert.message }}
                </ToastMessage>

                <!-- Form cập nhật avatar -->
                <form @submit.prevent="submitForm">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Chọn hình ảnh:</label>
                        <input type="file" id="avatar" @change="handleFileChange" />
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="Avatar Preview" class="img-thumbnail" />
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
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
    name: 'UpdateAvatarModal',
    components: {
        ToastMessage
    },
    props: {
        isVisible: Boolean,
    },
    data() {
        return {
            imageFile: null,
            imagePreview: null,
            alert: {
                visible: false,
                message: '',
                type: 'info' // 'success', 'error', 'info'
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
            }, 300);
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageFile = file;
                this.imagePreview = URL.createObjectURL(file);
            }
        },
        async submitForm() {
            if (!this.imageFile) {
                this.showAlert('Vui lòng chọn một hình ảnh.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', this.imageFile);

            try {
                await axios.post('/account/avatar', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                this.showAlert('Avatar đã được cập nhật thành công!', 'success');
                this.$emit('avatar-updated');
                setTimeout(() => {
                    this.closeModal();
                }, 1000);
            } catch (error) {
                console.error('Failed to update avatar:', error);
                this.showAlert('Đã xảy ra lỗi khi cập nhật avatar.', 'error');
            }
        },
        showAlert(message, type = 'info') {
            this.alert.message = message;
            this.alert.type = type;
            this.alert.visible = true;
            setTimeout(() => {
                this.alert.visible = false;
            }, 3000);
        }
    },
};
</script>





<style scoped>
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
    color: var(--secondary-color);
}

.modal-body {
    padding: 15px 0;
}

.modal-body .form-label {
    font-weight: var(--font-weight-medium);
    color: var(--text-color);
}

.modal-body input[type="file"] {
    font-family: var(--font-family-primary);
    font-size: 16px;
    border: 1px solid var(--input-border-color);
    border-radius: 6px;
    padding: 10px;
    background: var(--light-bg-color);
}

.modal-body .img-thumbnail {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    box-shadow: 0 4px 8px var(--box-shadow-color);
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

/* Optional: Add margin to form elements for better spacing */
.modal-body .mb-3 {
    margin-bottom: 1rem;
}
</style>
