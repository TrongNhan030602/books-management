<template>
    <div class="chat-container container">
        <!-- Phần liệt kê các phòng -->
        <div class="room-selection text-center">
            <h2 class="mb-4 main-heading">Chọn phòng để Chat</h2>
            <ul class="list-group mt-3">
                <li v-for="room in rooms" :key="room.id" class="list-group-item list-group-item-action"
                    @click="joinRoom(room.id)">
                    <i class="fas fa-comments text-success"></i> {{ room.name }}
                </li>
            </ul>
        </div>

        <!-- Phần chat -->
        <div v-if="roomId" class="chat-room border rounded">
            <div class="chat-header d-flex justify-content-between align-items-center p-3 text-white">
                <h3 class="mb-0">Chat với {{ roomName }}</h3>
                <button @click="leaveRoom" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Rời phòng
                </button>
            </div>

            <div class="messages p-3" ref="messagesContainer">
                <div v-for="message in messages" :key="message.id" class="message p-2 mb-2 rounded">
                    <div class="message-content">
                        <strong>{{ message.user.first_name || 'Unknown' }}:</strong>
                        <p class="mb-0">{{ message.message }}</p>
                    </div>
                    <small class="text-muted message-time">{{ formatTime(message.created_at) }}</small>
                </div>
            </div>

            <div class="input-container p-3 d-flex">
                <input v-model="newMessage" @keydown.enter="sendMessage" class="form-control"
                    placeholder="Nhập tin nhắn..." />
                <button @click="sendMessage" :disabled="isSending" class="btn-send ms-2">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from '../../axios';
import Echo from '../../echo';

export default {
    data() {
        return {
            roomId: null,
            roomName: '',
            newMessage: '',
            messages: [],
            rooms: [],
            isSending: false,
        };
    },
    methods: {
        async fetchRooms() {
            try {
                const response = await axios.get("/chats/admin/rooms");
                this.rooms = response.data.data;
            } catch (error) {
                console.error("Error fetching rooms:", error);
                alert("Không thể lấy danh sách phòng.");
            }
        },

        async joinRoom(roomId) {
            if (this.roomId !== roomId) {
                if (this.roomId) {
                    Echo.leave(`chat-room.${this.roomId}`);
                }
                this.roomId = roomId;
                const room = this.rooms.find(r => r.id === roomId);
                this.roomName = room.name;
                await this.fetchMessages();
                this.setupEcho();
                localStorage.setItem('lastRoomId', this.roomId);
            }
        },

        leaveRoom() {
            Echo.leave(`chat-room.${this.roomId}`);
            this.roomId = null;
            this.roomName = '';
            this.messages = [];
            localStorage.removeItem('lastRoomId');
            this.fetchRooms();
        },

        async fetchMessages() {
            try {
                const response = await axios.get(`/chats/${this.roomId}/messages`);
                if (response.data.success) {
                    this.messages = response.data.data;
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            } catch (error) {
                console.error("Error fetching messages:", error);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            this.isSending = true;

            // Tạo một tin nhắn tạm thời (để hiển thị ngay lập tức)
            const tempMessage = {
                id: Date.now(),  // Tạo một ID giả cho tin nhắn tạm
                user: { first_name: 'Bạn (admin)', last_name: '' },  // Thông tin người gửi
                message: this.newMessage,
                created_at: new Date().toISOString(), // Thời gian hiện tại
            };

            // Thêm tin nhắn tạm vào UI ngay lập tức
            this.messages.push(tempMessage);
            this.$nextTick(() => {
                this.scrollToBottom();
            });

            try {
                const response = await axios.post(`/chats/${this.roomId}/message`, { message: this.newMessage });
                const message = response.data.data.message;
                if (message && message.id && !this.messages.some(m => m.id === message.id)) {
                    // Nếu có tin nhắn hợp lệ từ server, cập nhật lại mảng messages
                    const index = this.messages.findIndex(m => m.id === tempMessage.id);
                    if (index !== -1) {
                        this.$set(this.messages, index, message);  // Cập nhật tin nhắn tạm bằng tin nhắn thật từ server
                    }
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
                this.newMessage = '';
            } catch (error) {
                console.error("Error sending message:", error);
                alert("Không thể gửi tin nhắn.");
            } finally {
                this.isSending = false;
            }
        },
        setupEcho() {
            Echo.join(`chat-room.${this.roomId}`)
                .listen('MessageSent', (event) => {
                    const message = event.message;
                    if (message && message.id && !this.messages.some(m => m.id === message.id)) {
                        this.messages.push(message);
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                });
        },


        scrollToBottom() {
            const messagesContainer = this.$refs.messagesContainer;
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return new Intl.DateTimeFormat('vi-VN', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            }).format(date);
        }
    },
    mounted() {
        const lastRoomId = localStorage.getItem('lastRoomId');
        if (lastRoomId) {
            this.joinRoom(lastRoomId);
        } else {
            this.fetchRooms();
        }
    }
};
</script>

<style scoped>
.chat-container {
    max-width: 540px;
    margin: 20px auto;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.main-heading {
    color: var(--primary-color);
    font-weight: var(--font-weight-bold);
    margin-bottom: 20px;
    text-transform: uppercase;
    text-align: center;
    padding-bottom: 16px;
}

.room-selection h3 {
    color: var(--primary-color);
    font-size: 1.25rem;
    font-weight: 600;
}

.room-selection .list-group-item {
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: 500;
}

.room-selection .list-group-item:hover {
    background-color: #e2e6ea;
}

.chat-room {
    display: flex;
    flex-direction: column;
    height: 420px;
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.chat-header {
    border-bottom: 1px solid #dee2e6;
    background-color: var(--primary-color);
}

.chat-header h3 {
    font-size: 1.1rem;
}

.messages {
    flex: 1;
    overflow-y: scroll;
    background-color: #f8f9fa;
    padding: 10px;
}

.messages .message {
    background-color: #e9ecef;
    padding: 8px;
    margin-bottom: 8px;
    border-radius: 8px;
    font-size: 0.9rem;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.1);
}

.messages .message .message-content {
    margin-bottom: 5px;
}

.messages .message small.message-time {
    font-size: 0.7rem;
    color: #6c757d;
    display: block;
    margin-top: 5px;
}

.input-container input {
    border-radius: 20px;
    padding: 8px;
    font-size: 0.9rem;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.input-container .btn-send {
    border-radius: 50%;
    padding: 8px;
    font-size: 1.2rem;
    background-color: #28a745;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
}

.input-container .btn-send:hover {
    background-color: #218838;
}
</style>
