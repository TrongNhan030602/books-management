<template>
  <div class="container mt-5 form-container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 form-content">
        <h2 class="text-center mb-4 form-heading">Đăng Nhập</h2>
        <form @submit.prevent="submitForm" class="login-form">
          <div class="mb-3">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i> Email:
            </label>
            <input type="email" id="email" class="form-control" v-model="email"
              :class="{ 'is-invalid': errors.email }" />
            <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
          </div>
          <div class="mb-3 position-relative">
            <label for="password" class="form-label">
              <i class="fas fa-lock"></i> Mật khẩu:
            </label>
            <input :type="passwordFieldType" id="password" autocomplete class="form-control" v-model="password"
              :class="{ 'is-invalid': errors.password }" />
            <i @click="togglePasswordVisibility" :class="['password-toggle', passwordIconClass]"></i>
            <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Đăng Nhập</button>
        </form>
        <p class="mt-3 text-center">
          <router-link to="/register" class="link">Đăng ký</router-link> |
          <router-link to="/forgot-password" class="link">Quên mật khẩu?</router-link>
        </p>
        <ToastMessage :type="toastType" :visible="showToast" @close="showToast = false">
          {{ toastMessage }}
        </ToastMessage>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios';
import Cookies from 'js-cookie';

import ToastMessage from '../ToastMessage/ToastMessage.vue';

export default {
  components: {
    ToastMessage
  },
  data() {
    return {
      email: '',
      password: '',
      errors: {},
      toastMessage: '',
      toastType: 'info', // 'success' or 'error'
      showToast: false,
      showPassword: false,
    };
  },
  computed: {
    isFormValid() {
      return this.email && this.password && !this.errors.email && !this.errors.password;
    },
    passwordFieldType() {
      return this.showPassword ? 'text' : 'password';
    },
    passwordIconClass() {
      return this.showPassword ? 'fas fa-eye' : 'fas fa-eye-slash';
    },
  },
  methods: {
    async submitForm() {
      this.validateEmail();
      this.validatePassword();
      if (!this.isFormValid) {
        return;
      }
      await this.login();
    },
    async login() {
      try {
        this.errors = {};
        this.toastMessage = '';
        this.toastType = 'info';
        this.showToast = false;

        const response = await axios.post('/account/login', {
          email: this.email,
          password: this.password,
        });

        // Lưu token vào cookies
        Cookies.set('access_token', response.data.access_token, { expires: 7 }); // Lưu token trong 7 ngày

        const userRole = response.data.role;
        this.toastMessage = 'Đăng nhập thành công! Chào mừng bạn quay lại!';
        this.toastType = 'success';
        this.showToast = true;

        setTimeout(() => {
          if (userRole === 'Admin') {
            this.$router.push('/');
          } else if (userRole === 'Reader') {
            this.$router.push('/reader');
          }
        }, 1000);

      } catch (error) {
        console.error('Login error:', error);
        this.errors = error.response?.data.errors || {};

        if (error.response && error.response.status === 400) {
          this.toastMessage = 'Thông tin đăng nhập không chính xác. Vui lòng thử lại!';
        } else if (error.response && error.response.status === 422) {
          this.toastMessage = 'Dữ liệu nhập vào không hợp lệ. Vui lòng kiểm tra lại!';
        } else {
          this.toastMessage = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
        }

        this.toastType = 'error';
        this.showToast = true;

        setTimeout(() => {
          this.showToast = false;
        }, 3000);
      }
    },
    validateEmail() {
      this.errors.email = '';
      if (!this.email) {
        this.errors.email = 'Email là bắt buộc.';
      } else if (!/\S+@\S+\.\S+/.test(this.email)) {
        this.errors.email = 'Email không hợp lệ.';
      }
    },
    validatePassword() {
      this.errors.password = '';
      if (!this.password) {
        this.errors.password = 'Mật khẩu là bắt buộc.';
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
  },
};
</script>

<style scoped>
@import url('../../../css/form.css');

.password-toggle {
  position: absolute;
  right: 10px;
  top: 60%;
  cursor: pointer;
  color: var(--secondary-color);
}

.password-toggle:hover {
  color: var(--hover-color);
}
</style>
