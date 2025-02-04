<template>
    <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <header class="modal-header">
                <h2>{{ title }}</h2>
                <button @click="closeModal" class="btn-close" aria-label="Close">&times;</button>
            </header>
            <main class="modal-body">
                <p>{{ message }}</p>
                <slot></slot> <!-- Thêm slot để chèn nội dung tùy chỉnh -->
            </main>
            <footer class="modal-footer">
                <button @click="closeModal" class="btn btn-secondary">{{ cancelButtonText }}</button>
                <button @click="confirmAction" class="btn btn-danger">{{ confirmButtonText }}</button>
            </footer>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isVisible: {
            type: Boolean,
            required: true,
        },
        title: {
            type: String,
            default: 'Xác nhận',
        },
        message: {
            type: String,
            default: 'Bạn có chắc chắn muốn thực hiện hành động này không?',
        },
        confirmButtonText: {
            type: String,
            default: 'Xác nhận',
        },
        cancelButtonText: {
            type: String,
            default: 'Hủy',
        },
    },
    methods: {
        closeModal() {
            this.$emit('update:isVisible', false);
        },
        confirmAction() {
            this.$emit('confirm');
            this.closeModal();
        },
    },
};
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
    background: #dc3545;
    color: var(--white-color);
    border: none;
}

.btn-danger:hover {
    background: #c82333;
    transform: scale(1.05);
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