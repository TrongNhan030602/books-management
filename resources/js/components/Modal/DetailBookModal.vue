<template>
    <div v-if="isVisible" class="modal fade show" style="display: block; z-index: 1050;" @click.self="closeModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ book.title }}</h5>
                    <button @click="closeModal" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img :src="`${host}/storage/${book.cover_image}`" alt="Cover" class="img-fluid rounded" />
                        </div>
                        <div class="col-md-8">
                            <p><strong>Tác giả:</strong> {{ book.author }}</p>
                            <p><strong>Năm xuất bản:</strong> {{ book.published_year }}</p>
                            <p><strong>Mô tả:</strong> {{ book.description }}</p>
                            <p><strong>Còn lại:</strong> {{ book.quantity }}</p>
                            <p>
                                Trạng thái giao dịch hiện tại: <strong>{{ latestTransactionStatus }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="borrowBook" :disabled="!canBorrow">
                        <i class="fas fa-check me-1"></i>
                        Mượn
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isVisible: Boolean,
        book: Object,
    },
    data() {
        return {
            host: 'http://127.0.0.1:8000',
        };
    },
    computed: {
        latestTransactionStatus() {
            if (this.book && this.book.borrow_transactions && this.book.borrow_transactions.length > 0) {
                const latestTransaction = this.book.borrow_transactions[this.book.borrow_transactions.length - 1];
                switch (latestTransaction.status) {
                    case 'completed':
                        return 'Có thể mượn';
                    case 'approved':
                        return 'Đang mượn';
                    case 'cancelled':
                        return 'Có thể mượn';
                    case 'pending':
                        return 'Đang chờ mượn';
                    case 'returned':
                        return 'Đang chờ trả';
                    default:
                        return 'Không xác định';
                }
            }
            return 'Chưa có giao dịch';
        },
        canBorrow() {
            // Điều kiện cho phép mượn sách
            const lastTransaction = this.book.borrow_transactions && this.book.borrow_transactions.length > 0
                ? this.book.borrow_transactions[this.book.borrow_transactions.length - 1]
                : null;

            return !lastTransaction || lastTransaction.status === 'completed' || lastTransaction.status === 'cancelled';
        }
    },
    watch: {
        book: {
            handler(newBook) {
            },
            immediate: true,
        }
    },
    methods: {
        closeModal() {
            this.$emit('update:isVisible', false);
        },
        borrowBook() {
            this.openBorrowModal();
            this.closeModal();
        },
        openBorrowModal() {
            this.$emit('open-borrow-modal', this.book.id);
        }
    },
};
</script>









<style scoped>
.modal-content {
    border-radius: 8px;
    box-shadow: 0 4px 20px var(--box-shadow-color);
}

.modal-body img {
    border-radius: 8px;
    transition: transform 0.3s;
}

.modal-body img:hover {
    transform: scale(1.05);
}

.modal-header {
    background-color: var(--primary-color);
    color: var(--white-color);
}

.modal-footer {
    background-color: var(--light-bg-color);
}

.modal-body p {
    margin: 10px 0;
    line-height: 1.6;
}

.btn-close {
    background-color: transparent;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    color: #333;
    position: absolute;
    top: 15px;
    right: 20px;
    transition: color 0.3s ease;
}

.btn-close:hover {
    filter: brightness(120%);
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
</style>