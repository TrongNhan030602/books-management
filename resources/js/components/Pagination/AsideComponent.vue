<template>
    <aside class="sidebar">
        <h4 class="sidebar-title">Liên Kết Nhanh</h4>
        <nav>
            <ul>
                <li>
                    <router-link to="/reader/notifications" active-class="active-link">
                        <i class="fas fa-bell"></i>
                        Thông báo
                        <span class="badge" v-if="unreadCount > 0" :class="{ 'new-notifications': unreadCount > 0 }">{{
                            unreadCount }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader" exact-active-class="active-link">
                        <i class="fa-solid fa-book-open"></i>
                        Mượn sách
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader/return-books" active-class="active-link">
                        <i class="fa-solid fa-rotate-left"></i>
                        Trả sách
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader/history-borrow" active-class="active-link">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Lịch sử mượn
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader/penalties" active-class="active-link">
                        <i class="fas fa-exclamation-triangle"></i>
                        Xử phạt
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader/reviews" active-class="active-link">
                        <i class="fas fa-comment-dots"></i>
                        Đánh giá
                    </router-link>
                </li>
                <li>
                    <router-link to="/reader/wishlist" active-class="active-link">
                        <i class="fas fa-heart"></i>
                        Yêu thích
                    </router-link>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script>
import axios from '../../axios';
import EventBus from '../../eventBus';

export default {
    name: "AsideComponent",
    data() {
        return {
            unreadCount: 0, // Số lượng thông báo chưa đọc
        };
    },
    mounted() {
        this.fetchUnreadCount();
        EventBus.$on('bookBorrowed', this.fetchUnreadCount);
        EventBus.$on('notificationsUpdated', this.fetchUnreadCount);
    },
    methods: {
        async fetchUnreadCount() {
            try {
                const response = await axios.get('/notifications/users/unread-count');
                this.unreadCount = response.data.count;
            } catch (error) {
                console.error(error);
            }
        },
        handleBookBorrowed() {
            this.updateUnreadCount();
            // Tùy chọn: Cũng có thể gọi lại `fetchUnreadCount()` nếu cần.
            // this.fetchUnreadCount();
        },
        updateUnreadCount() {
            this.unreadCount += 1; // Tăng số lượng thông báo chưa đọc
        },

    },
    beforeDestroy() {
        EventBus.$off('bookBorrowed', this.fetchUnreadCount);
        EventBus.$off('notificationsUpdated', this.fetchUnreadCount);
    }
};
</script>

<style scoped>
.sidebar {
    background-color: var(--primary-color);
    color: var(--white-color);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px var(--box-shadow-color);
}

.sidebar-title {
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: 20px;
}

.sidebar nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar nav ul li {
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

.sidebar nav ul li:hover {
    transform: translateX(10px);
}

.sidebar nav ul li a.active-link {
    background-color: var(--secondary-color);
    color: var(--white-color);
    font-weight: var(--font-weight-medium);
}

.sidebar nav ul li a {
    color: var(--white-color);
    text-decoration: none;
    font-size: 1em;
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 6px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.sidebar nav ul li a.active-link i {
    color: var(--white-color);
}

.sidebar nav ul li a i {
    margin-right: 12px;
    transition: transform 0.3s ease;
}

.sidebar nav ul li a:hover i {
    transform: rotate(-10deg);
}

.sidebar nav ul li a:hover {
    background-color: var(--hover-color);
}

/* Style cho số lượng thông báo */
.badge {
    background-color: rgb(239, 37, 37);
    color: var(--white-color);
    padding: 5px 8px;
    border-radius: 12px;
    margin-left: auto;
    transition: all 0.3s ease;
    font-size: 0.9em;
    position: relative;
    z-index: 5000;
}

/* Thêm hiệu ứng nhấp nhô cho badge khi có thông báo mới */
.badge::after {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    width: 100%;
    height: 100%;
    border-radius: 12px;
    background-color: rgba(255, 255, 255, 0.3);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.badge:focus-within::after,
.badge:hover::after {
    opacity: 1;
}

.new-notifications {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }

    100% {
        transform: scale(1);
    }
}
</style>
