<template>
    <div class="modal">
        <div class="modal-content">
            <span class="close" @click="$emit('close')">&times;</span>
            <h2>Hoàn Tất Trả Sách</h2>
            <form @submit.prevent="submitReturn">
                <!-- Add form fields as needed -->
                <button type="submit">Hoàn Tất</button>
            </form>
        </div>
    </div>
</template>

<script>
import axios from '../../../axios';

export default {
    name: 'ModalReturnBook',
    methods: {
        async submitReturn() {
            try {
                // Add data for completing return
                await axios.put('/borrow-transaction/complete-return', { /* data */ });
                this.$emit('close');
                // Refresh return transactions list
            } catch (error) {
                console.error('Error completing return:', error);
            }
        },
    },
};
</script>

<style scoped>
/* Styles for Modal */
</style>