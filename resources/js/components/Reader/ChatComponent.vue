<template>
    <div class="chat-widget">
        <h5>Chat với chúng tôi</h5>
        <div class="chat-messages">
            <div class="message">Chào bạn! Có thể tôi giúp gì cho bạn?</div>
        </div>
        <input type="text" placeholder="Nhập tin nhắn..." />
    </div>
</template>

<style scoped>
.chat-widget {
    display: flex;
    flex-direction: column;
    height: 400px;
    overflow: hidden;
    border: 1px solid var(--primary-color);
    border-radius: 8px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background-color: #f1f1f1;
}

.message {
    background-color: var(--primary-color);
    color: var(--white-color);
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
}

input {
    border: none;
    padding: 10px;
    font-size: 1rem;
    border-top: 1px solid var(--primary-color);
}
</style>
