<template>
    <div class="reader-layout">
        <!-- Phần Header -->
        <HeaderReader />

        <!-- Layout chính với Slider, Aside và Content Area -->
        <div class="main-layout container-fluid">
            <div class="row">
                <!-- Phần Slider -->
                <section class="col-lg-12 slider-section mb-4">
                    <SliderComponent />
                </section>
            </div>

            <div class="row">
                <!-- Phần Aside với các liên kết nhanh -->
                <aside class="col-lg-3">
                    <AsideComponent />
                </aside>

                <!-- Phần Content chính -->
                <main class="col-lg-9">
                    <router-view />
                    <!-- Phần Gợi ý sách -->
                    <RecommendationComponent />
                </main>
            </div>
        </div>

        <!-- Phần Chat Widget -->
        <div class="chat-widget" :class="{ 'chat-widget-visible': isChatVisible }">
            <div class="chat-header">
                <h5>Chat với chúng tôi</h5>
                <button class="btn-close" @click="toggleChat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <div v-for="message in messages" :key="message.id" class="message">
                    <div class="message-header">
                        <strong v-if="message.user">{{ message.user.first_name }}:</strong>
                        <strong v-else>Khách:</strong>
                        <span class="message-time" v-if="message.created_at">{{ formatDate(message.created_at) }}</span>
                    </div>
                    <p>{{ message.message }}</p>
                </div>


                <div v-if="isSending" class="loading-spinner"></div>
            </div>
            <div class="chat-input">
                <input v-model="newMessage" placeholder="Gửi tin nhắn..." @keydown.enter="sendMessage" />
                <button @click="sendMessage" :disabled="isSending">Gửi</button>
            </div>
        </div>

        <!-- Nút Icon Chat -->
        <button class="chat-icon-btn" @click="toggleChat">
            <i class="fas fa-comments"></i>
        </button>

        <!-- Phần Footer -->
        <FooterComponent />
    </div>
</template>

<script>
import HeaderReader from "../Pagination/HeaderReader.vue";
import FooterComponent from "../Pagination/Footer.vue";
import SliderComponent from "../Pagination/Slider.vue";
import AsideComponent from "../Pagination/AsideComponent.vue";
import RecommendationComponent from "../Reader/Recommendation/RecommendationComponent.vue";
import axios from "../../axios";
import Echo from "../../echo";

export default {
    name: "ReaderLayout",
    components: {
        HeaderReader,
        FooterComponent,
        SliderComponent,
        AsideComponent,
        RecommendationComponent,
    },
    data() {
        return {
            isChatVisible: false,
            messages: [],
            newMessage: "",
            isSending: false,
            roomId: null,
        };
    },
    created() {
        const savedRoomId = localStorage.getItem("roomId");
        if (savedRoomId) {
            this.roomId = savedRoomId;
            this.fetchMessages();
            this.listenForMessages();
        }
    },
    methods: {
        toggleChat() {
            this.isChatVisible = !this.isChatVisible;
            if (this.isChatVisible && !this.roomId) {
                this.createRoom();
            } else if (this.isChatVisible) {
                this.fetchMessages();
            }
        },
        formatDate(date) {
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            const formattedDate = new Date(date).toLocaleDateString('vi-VN', options);
            return formattedDate;
        },
        async createRoom() {
            try {
                const response = await axios.post('/chats/private');
                if (response.data.success && response.data.data.chatRoom) {
                    this.roomId = response.data.data.chatRoom.id;
                    localStorage.setItem("roomId", this.roomId);
                    this.fetchMessages();
                    this.listenForMessages();
                }
            } catch (error) {
                console.error("Lỗi khi tạo phòng chat:", error);
            }
        },
        async sendMessage() {
            if (this.newMessage.trim() === "") return;
            this.isSending = true;
            try {
                const response = await axios.post(`/chats/${this.roomId}/message`, { message: this.newMessage });
                if (response.data.success && response.data.data.chat) {
                    this.messages.push(response.data.data.chat);
                    this.newMessage = "";
                    this.scrollToBottom();
                }
            } catch (error) {
                console.error("Lỗi khi gửi tin nhắn:", error);
            } finally {
                this.isSending = false;
            }
        },
        async fetchMessages() {
            if (!this.roomId) return;

            try {
                const response = await axios.get(`/chats/${this.roomId}/messages`);
                console.log(response.data); // Kiểm tra xem dữ liệu trả về có đúng không
                if (response.data.success) {
                    this.messages = response.data.data;
                } else {
                    console.error("Không thể lấy tin nhắn:", response.data.message);
                }
            } catch (error) {
                console.error("Lỗi khi lấy tin nhắn:", error);
            }
        },
        listenForMessages() {
            Echo.channel(`chat-room.${this.roomId}`)
                .listen('MessageSent', (event) => {
                    console.log(event.chat);  // Kiểm tra tin nhắn nhận được
                    if (event.chat.user) {
                        this.messages.push(event.chat);
                    } else {
                        console.warn("Không có thông tin người dùng trong tin nhắn.");
                    }
                    this.scrollToBottom();
                });
        },
        scrollToBottom() {
            const chatBody = this.$el.querySelector('.chat-body');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        },
    },
};
</script>

<style scoped>
.chat-widget {
    position: fixed;
    bottom: 30px;
    right: 68px;
    width: 340px;
    background: linear-gradient(to bottom right, #fff, #f5f5f5);
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    transition: all 0.3s ease;
    z-index: 5000;
    max-height: 500px;
    overflow: hidden;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.chat-widget-visible {
    display: flex !important;
    flex-direction: column;
    height: 100%;
}

.message-time {
    font-size: 12px;
    color: #888;
    margin-left: 10px;
}

.chat-header {
    background-color: var(--primary-color);
    color: #fff;
    padding: 12px 15px;
    font-size: 16px;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
}

.chat-body {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    font-size: 14px;
    background-color: #f9f9f9;
    max-height: calc(100% - 75px);
    position: relative;
}

.chat-body .message {
    margin-bottom: 12px;
    display: flex;
    flex-direction: column;
    opacity: 0;
    animation: messageIn 0.3s ease-out forwards;
}

@keyframes messageIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.message strong {
    font-weight: 600;
    color: #333;
}

.chat-input {
    padding: 12px 15px;
    display: flex;
    align-items: center;
    background-color: #fff;
    border-top: 1px solid #ddd;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
}

.chat-input input:focus {
    border-color: var(--primary-color);
}

.chat-input button {
    padding: 10px 18px;
    margin-left: 12px;
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.chat-input button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.chat-icon-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 50%;
    padding: 15px;
    font-size: 24px;
    cursor: pointer;
    z-index: 10000;
}

.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
