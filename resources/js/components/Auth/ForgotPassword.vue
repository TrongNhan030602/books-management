<template>
  <div class="container form-container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6  form-content">
        <h2 class="text-center mb-4 form-heading">Quên Mật Khẩu</h2>
        <!-- ToastMessage -->
        <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
          {{ alert.message }}
        </ToastMessage>

        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i> Email:
            </label>
            <input type="email" id="email" class="form-control" v-model="email" :class="{ 'is-invalid': error }"
              placeholder="Nhập email của bạn" />
            <div v-if="error" class="invalid-feedback">{{ error }}</div>
          </div>
          <button type="submit" class="btn btn-primary">Gửi Liên Kết Đặt Lại Mật Khẩu</button>
        </form>
        <p class="mt-3">
          <router-link to="/login" class="link">Trở về đăng nhập</router-link> |
          <router-link to="/register" class="link">Đăng ký</router-link>
        </p>
      </div>
    </div>
  </div>
</template>


<script>
import axios from '../../axios';
import ToastMessage from '../ToastMessage/ToastMessage.vue';

export default {
  components: {
    ToastMessage,
  },
  data() {
    return {
      email: '',
      error: '',
      alert: {
        visible: false,
        message: '',
        type: 'info', // 'success' hoặc 'error'
      },
    };
  },
  methods: {
    async submitForm() {
      this.error = '';

      if (!this.email) {
        this.error = 'Vui lòng nhập email của bạn.';
        return;
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(this.email)) {
        this.error = 'Địa chỉ email không hợp lệ.';
        return;
      }

      try {
        await axios.post('/account/reset-password', { email: this.email });
        this.showAlert('Liên kết đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra email của bạn.', 'success');

        setTimeout(() => {
          this.$router.push('/login');
        }, 3000);
      } catch (error) {
        this.showAlert(error.response.data.message || 'Có lỗi xảy ra', 'error');
      }
    },
    showAlert(message, type = 'info') {
      this.alert.message = message;
      this.alert.type = type;
      this.alert.visible = true;
      setTimeout(() => {
        this.alert.visible = false;
      }, 3000);
    },
  },
};
</script>


<style scoped>
@import url('../../../css/form.css');
</style>
