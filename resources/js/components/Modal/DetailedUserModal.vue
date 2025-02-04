<template>
    <div v-if="isVisible" class="modal-backdrop" @click.self="closeModal">
        <div class="modal" :class="{ show: isVisible, hide: isFadingOut }">
            <div class="modal-header">
                <h5 class="modal-title">Thông tin chi tiết</h5>
                <button type="button" class="btn-close" @click="closeModal" aria-label="Close">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <!-- ToastMessage thông báo -->
                <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
                    {{ alert.message }}
                </ToastMessage>

                <div v-if="user" class="user-info-card">
                    <div class="user-info-item">
                        <i class="fas fa-user info-icon"></i>
                        <strong class="me-2">Tên:</strong>
                        <span>{{ user.last_name }} {{ user.first_name }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="fas fa-birthday-cake info-icon"></i>
                        <strong class="me-2">Ngày sinh:</strong>
                        <span>{{ formatDate(user.dob) }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="fas fa-home info-icon"></i>
                        <strong class="me-2">Địa chỉ:</strong>
                        <span>{{ user.address }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="fas fa-phone info-icon"></i>
                        <strong class="me-2">Số điện thoại:</strong>
                        <span>{{ user.phone }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="fas fa-envelope info-icon"></i>
                        <strong class="me-2">Email:</strong>
                        <span>{{ user.email }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="fas fa-crown info-icon"></i>
                        <strong class="me-2">Cấp độ thành viên:</strong>
                        <span :class="membershipClass">{{
                            user.membership_level
                        }}</span>
                    </div>
                </div>
                <div v-else>
                    <p>Đang tải thông tin...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ToastMessage from "../ToastMessage/ToastMessage.vue";

export default {
    name: "DetailedUserModal",
    components: {
        ToastMessage,
    },
    props: {
        isVisible: Boolean,
        user: Object,
    },
    data() {
        return {
            alert: {
                visible: false,
                message: "",
                type: "info",
            },
            isFadingOut: false,
        };
    },
    methods: {
        closeModal() {
            this.isFadingOut = true;
            setTimeout(() => {
                this.$emit("update:isVisible", false);
                this.isFadingOut = false;
            }, 300);
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return `${date.getDate().toString().padStart(2, "0")}/${(
                date.getMonth() + 1
            )
                .toString()
                .padStart(2, "0")}/${date.getFullYear()}`;
        },
        showAlert(message, type = "info") {
            this.alert.message = message;
            this.alert.type = type;
            this.alert.visible = true;
            setTimeout(() => {
                this.alert.visible = false;
            }, 3000);
        },
    },
    computed: {
        membershipClass() {
            return {
                "membership-gold": this.user.membership_level === "Gold",
                "membership-silver": this.user.membership_level === "Silver",
                "membership-bronze": this.user.membership_level === "Bronze",
            };
        },
    },
};
</script>

<style scoped>
@import url("../../../css/form.css");

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: var(--white-color);
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 600px;
    padding: 20px;
    position: relative;
    color: var(--text-color);
    font-family: var(--font-family-primary);
    opacity: 0;
    display: block;
    transform: scale(0.95);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal.show {
    opacity: 1;
    transform: scale(1);
}

.modal.hide {
    opacity: 0;
    transform: scale(0.95);
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeOutScale {
    from {
        opacity: 1;
        transform: scale(1);
    }

    to {
        opacity: 0;
        transform: scale(0.95);
    }
}

.modal.show {
    animation: fadeInScale 0.3s ease forwards;
}

.modal.hide {
    animation: fadeOutScale 0.3s ease forwards;
}

/* Modal Header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--input-border-color);
    padding-bottom: 15px;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    color: var(--secondary-color);
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 1.75rem;
    cursor: pointer;
    color: var(--secondary-color);
}

/* Modal Body */
.modal-body {
    padding: 20px 0;
}

.user-info-card {
    border: 1px solid var(--input-border-color);
    border-radius: 8px;
    padding: 20px;
    background: var(--white-color);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.user-info-item {
    margin-bottom: 15px;
    font-size: 16px;
    color: var(--text-color);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;

}

.info-icon {
    font-size: 22px;
    margin-right: 12px;
    color: var(--primary-color);
}

.user-info-item:hover {
    opacity: 0.9;
    border-radius: 4px;
    padding: 4px;
}

/* Membership Levels */
.membership-gold {
    color: gold;
    font-weight: var(--font-weight-bold);
}

.membership-silver {
    color: silver;
    font-weight: var(--font-weight-bold);
}

.membership-bronze {
    color: #cd7f32;
    font-weight: var(--font-weight-bold);
}
</style>
