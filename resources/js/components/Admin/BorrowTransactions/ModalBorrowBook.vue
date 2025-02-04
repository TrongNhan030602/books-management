<template>
    <div class="modal">
        <div class="modal-content">
            <span class="close" @click="$emit('close')">&times;</span>
            <h2>Thêm Mượn Sách</h2>
            <form @submit.prevent="submitBorrow">
                <!-- Add form fields as needed -->
                <button type="submit">Mượn Sách</button>
            </form>
        </div>
    </div>
</template>

<script>
import axios from '../../../axios';

export default {
    name: 'ModalBorrowBook',
    methods: {
        async submitBorrow() {
            try {
                // Add data for borrowing book
                await axios.post('/borrow-transaction', { /* data */ });
                this.$emit('close');
                // Refresh borrow transactions list
            } catch (error) {
                console.error('Error borrowing book:', error);
            }
        },
    },
};
</script>

<style scoped>
/* Styles for Modal */
</style>