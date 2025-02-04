<template>
    <div v-if="visible" class="alert-custom" :class="['alert-custom-' + type, { 'alert-custom-show': visible }]">
        <div class="alert-body">
            <slot></slot>
            <button type="button" class="btn-close" @click="hideToast">×</button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        type: {
            type: String,
            default: 'info' // 'success', 'error', 'info'
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        hideToast() {
            this.$emit('close');
        }
    }
};
</script>

<style scoped>
/* Alert */
.alert-custom {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #fff;
    border-left: 4px solid;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 30px 20px;
    min-width: 300px;
    max-width: 400px;
    max-height: 100px;
    display: flex;
    align-items: center;
    z-index: 1050;
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.3s ease, transform 0.3s ease;
    animation: slideInLeft 0.3s ease forwards;
}

.alert-body {
    flex: 1;
    display: flex;
    align-items: center;
}

.alert-custom-show {
    opacity: 1;
    transform: translateY(0);
}


.alert-custom-info {
    border-left-color: var(--alert-info-border-color);
    background-color: var(--alert-info-bg-color);
    color: var(--alert-info-text-color);
}

.alert-custom-success {
    border-left-color: var(--alert-success-border-color);
    background-color: var(--alert-success-bg-color);
    color: var(--alert-success-text-color);
}

.alert-custom-error {
    border-left-color: var(--alert-error-border-color);
    background-color: var(--alert-error-bg-color);
    color: var(--alert-error-text-color);
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: rgba(0, 0, 0, 0.6);
    margin-left: 15px;
}

.btn-close:hover {
    color: rgba(0, 0, 0, 0.8);
}

@keyframes slideInLeft {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}
</style>
