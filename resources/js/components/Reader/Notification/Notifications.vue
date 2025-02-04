<template>
    <div class="notifications container mt-5">
        <h2 class="text-center section-title mb-5">Thông báo của bạn</h2>

        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center my-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải dữ liệu...</p>
        </div>

        <!-- Kiểm tra xem có thông báo nào không -->
        <div v-else-if="notifications.length === 0" class="alert alert-info text-center">
            Bạn chưa có thông báo nào.
        </div>

        <!-- Nút hành động cho thông báo -->
        <div class="actions d-flex mt-3 mb-3" v-if="!loading && notifications.length > 0">
            <button @click="markSelectedAsRead" class="btn btn-primary w-75 me-3"
                :disabled="selectedNotifications.length === 0">
                Đánh dấu đã đọc
            </button>
            <button @click="deleteSelectedNotifications" class="btn btn-danger w-25"
                :disabled="selectedNotifications.length === 0">
                Xóa đã chọn
            </button>
        </div>

        <ul class="list-group" v-if="notifications.length > 0">
            <li class="list-group-item d-flex align-items-center justify-content-between">
                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="me-3" />
                <strong>Chọn tất cả</strong>
            </li>
            <li v-for="notification in notifications" :key="notification.id"
                class="list-group-item d-flex align-items-center justify-content-between" :class="{
                    'list-group-item-info': notification.type === 'info',
                    'list-group-item-success': notification.type === 'success',
                    'list-group-item-warning': notification.type === 'warning',
                    'list-group-item-error': notification.type === 'error',
                    'unread': notification.status === 'unread',
                    'read': notification.status === 'read'
                }" @click="toggleSelection(notification.id)">
                <div class="d-flex align-items-center">
                    <input type="checkbox" v-model="selectedNotifications" :value="notification.id" class="me-3" />
                    <i :class="{
                        'fas fa-info-circle': notification.type === 'info',
                        'fas fa-check-circle': notification.type === 'success',
                        'fas fa-exclamation-triangle': notification.type === 'warning',
                        'fas fa-times-circle': notification.type === 'error',
                    }" :style="{
                        color: getIconColor(notification.type)
                    }" class="me-2 icon"></i>
                    <strong>{{ notification.message }}</strong>
                    <span class="status" v-if="notification.status === 'unread'">(Chưa đọc)</span>
                </div>
                <small>{{ formatDate(notification.created_at) }}</small>
            </li>
        </ul>

        <toast-component :visible="toastVisible" type="success" @close="toastVisible = false">
            {{ toastMessage }}
        </toast-component>
    </div>
</template>


<script>
import axios from '../../../axios';
import EventBus from '../../../eventBus';
import ToastComponent from '../../ToastMessage/ToastMessage.vue';

export default {
    components: {
        ToastComponent
    },
    data() {
        return {
            notifications: [],
            selectedNotifications: [],
            selectAll: false,
            toastVisible: false,
            toastMessage: '',
            loading: true,
        };
    },
    methods: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/notifications/users');
                this.notifications = response.data;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                timeZone: 'Asia/Ho_Chi_Minh',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        },
        async markSelectedAsRead() {
            try {
                await Promise.all(
                    this.selectedNotifications.map((id) =>
                        axios.put(`/notifications/${id}/read`)
                    )
                );
                this.fetchNotifications();
                this.selectedNotifications = [];
                this.selectAll = false;
                this.toastMessage = 'Đã đánh dấu thông báo được chọn là đã đọc.';
                this.showToast();
                EventBus.$emit('notificationsUpdated');
            } catch (error) {
                console.error(error);
            }
        },
        async deleteSelectedNotifications() {
            try {
                await Promise.all(
                    this.selectedNotifications.map((id) =>
                        axios.delete(`/notifications/${id}`)
                    )
                );
                this.fetchNotifications();
                this.selectedNotifications = [];
                this.selectAll = false;
                this.toastMessage = 'Đã xóa tất cả thông báo đã chọn.';
                this.showToast();
                EventBus.$emit('notificationsUpdated');
            } catch (error) {
                console.error(error);
            }
        },
        showToast() {
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;
            }, 2000);
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedNotifications = this.notifications.map(notification => notification.id);
            } else {
                this.selectedNotifications = [];
            }
        },
        toggleSelection(id) {
            const index = this.selectedNotifications.indexOf(id);
            if (index === -1) {
                this.selectedNotifications.push(id);
            } else {
                this.selectedNotifications.splice(index, 1);
            }
            this.selectAll = this.selectedNotifications.length === this.notifications.length;
        },
        getIconColor(type) {
            switch (type) {
                case 'info':
                    return '#2f86ab';
                case 'success':
                    return '#47d894';
                case 'warning':
                    return '#ffba3d';
                case 'error':
                    return '#ff623d';
                default:
                    return '#000';
            }
        }
    },
    mounted() {
        this.fetchNotifications();
    },
};
</script>

<style scoped>
/* Styles for Notification Container */
.notifications {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 2em;
    color: var(--primary-color);
    font-weight: bold;
    margin-bottom: 20px;
}


.list-group-item-info {
    background-color: #e9f6ff;
    border-color: #2f86ab;
}

.list-group-item-success {
    background-color: #e8f6e4;
    border-color: #47d894;
}

.list-group-item-warning {
    background-color: #fff9e6;
    border-color: #ffba3d;
}

.list-group-item-error {
    background-color: #ffe6e1;
    border-color: #ff623d;
}

/* Hover effect */
.list-group-item:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}

.alert {
    font-size: 16px;
    font-weight: bold;
}

/* Icon styles */
.icon {
    font-size: 18px;
}

/* Status styles */
.status {
    color: var(--error-color);
    margin-left: 10px;
    font-weight: bold;
}

/* Unread and Read notification styles */
.unread {
    border-left: 5px solid var(--primary-color);
    background-color: #e9f6ff;
    font-weight: 600;
}

.read {
    opacity: 0.8;
    background-color: #f8f9fa;
}

input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
}

small {
    font-size: 12px;
    color: #888;
}

/* Action buttons */
.actions button {
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
}

.actions .btn-primary:disabled {
    background-color: var(--primary-color);
    color: #fff;
    opacity: 0.7;
    cursor: not-allowed;
    border: none;
}

.actions button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
}

.actions .btn-primary:hover {
    background-color: var(--hover-color);
}

.actions .btn-danger {
    background-color: #dc3545;
}

.actions .btn-danger:hover {
    background-color: #d40f23;
    border: none;
}
</style>
