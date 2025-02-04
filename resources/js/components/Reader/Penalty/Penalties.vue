<template>
    <div class="penalties">
        <h2 class="section-title">Lịch sử phạt</h2>
        <div v-if="error" class="error">
            <p>Error loading penalties: {{ error }}</p>
        </div>
        <!-- Thông báo khi không có lịch sử phạt -->
        <div v-if="penalties.length === 0 && !error" class="alert alert-info">
            <p class="text-center pt-3">Hiện tại không có lịch sử phạt nào.</p>
        </div>
        <ul v-else>
            <li v-for="penalty in penalties" :key="penalty.id" class="penalty-item">
                <div class="penalty-header">
                    <p class="highlight">Mức phạt: <i class="fa-solid fa-coins"></i> {{
                        formatCurrency(penalty.amount) }}</p>
                    <p class="highlight">Hạn trả đến: <i class="fas fa-calendar-alt"></i> {{
                        formatDate(penalty.due_date) }}</p>
                </div>
                <div class="penalty-body">
                    <p><strong>Lý do phạt:</strong> <i class="fas fa-exclamation-triangle"></i> <span class="reason">{{
                        penalty.reason }}</span></p>
                    <p><strong>Mượn lúc:</strong> <i class="fas fa-clock"></i> {{
                        formatDate(penalty.borrow_transaction.borrow_date) }}</p>
                    <p><strong>Trả lúc:</strong> <i class="fas fa-clock"></i> {{
                        formatDate(penalty.borrow_transaction.return_date) }}</p>
                    <p
                        :class="{ 'status-returned': penalty.borrow_transaction.status === 'returned', 'status-completed': penalty.borrow_transaction.status === 'completed' }">
                        Trạng thái hiện tại: <i class="fas fa-info-circle"></i>
                        {{ penalty.borrow_transaction.status === 'returned' ? 'Đang chờ trả (chờ duyệt trả)' : 'Đã trả'
                        }}
                    </p>
                </div>
                <div class="book-info">
                    <img :src="getBookCoverUrl(penalty.borrow_transaction.book.cover_image)" alt="Book Cover"
                        class="book-cover" />
                    <div class="book-details">
                        <p class="book-title"><strong>{{ penalty.borrow_transaction.book.title }}</strong></p>
                        <p class="book-author"><strong>Tác giả:</strong> {{ penalty.borrow_transaction.book.author }}
                        </p>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from '../../../axios';

export default {
    name: "Penalties",
    data() {
        return {
            penalties: [],
            error: null,
        };
    },
    mounted() {
        this.fetchPenalties();
    },
    methods: {
        async fetchPenalties() {
            try {
                const response = await axios.get('/penalties/my-penalties');
                this.penalties = response.data.data;
            } catch (error) {
                this.error = error.response ? error.response.data.error : 'Unknown error';
            }
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        },
        getBookCoverUrl(coverImage) {
            return `http://127.0.0.1:8000/storage/${coverImage}`;
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                timeZone: 'Asia/Ho_Chi_Minh',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
    }
};
</script>

<style scoped>
.penalties {
    padding: 20px;
    border-radius: 10px;
    max-width: 860px;
    margin: auto;
    background-color: #f9f9f9;
}



.penalty-item {
    background-color: #ffffff;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.penalty-item:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}

.penalty-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.highlight {
    color: #d9534f;
    font-weight: bold;
}

.penalty-body {
    margin-bottom: 10px;
    font-size: 16px;
    color: #555;
}

.reason {
    color: #d9534f;
}

.status-returned {
    font-weight: bold;
    color: #f0ad4e;
}

.status-completed {
    font-weight: bold;
    color: #5cb85c;
}

.book-info {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.book-cover {
    width: 80px;
    height: auto;
    margin-right: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.book-title {
    color: #333;
}

.book-author {
    color: #777;

}

.error {
    color: #d9534f;
    font-weight: bold;
    text-align: center;
}
</style>
