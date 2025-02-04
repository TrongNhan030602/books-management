<template>
  <div class="container form-container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 form-content">
        <h2 class="text-center mb-4 form-heading">Đặt Lại Mật Khẩu</h2>

        <!-- ToastMessage -->
        <ToastMessage :type="alert.type" :visible="alert.visible" @close="alert.visible = false">
          {{ alert.message }}
        </ToastMessage>

        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu mới:</label>
            <input type="password" id="password" class="form-control" v-model="password"
              :class="{ 'is-invalid': errors.password }" />
            <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
          </div>
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu:</label>
            <input type="password" id="password_confirmation" class="form-control" v-model="password_confirmation"
              :class="{ 'is-invalid': errors.password_confirmation }" />
            <div v-if="errors.password_confirmation" class="invalid-feedback">{{ errors.password_confirmation }}</div>
          </div>
          <button type="submit" class="btn btn-primary">Đặt Lại Mật Khẩu</button>
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
  props: ['token'],
  data() {
    return {
      password: '',
      password_confirmation: '',
      errors: {},
      alert: {
        visible: false,
        message: '',
        type: 'info',
      },
    };
  },
  methods: {
    async submitForm() {
      this.errors = {};

      if (!this.password) {
        this.errors.password = 'Mật khẩu mới là bắt buộc.';
      }
      if (this.password !== this.password_confirmation) {
        this.errors.password_confirmation = 'Mật khẩu xác nhận không khớp.';
      }

      // Nếu có lỗi, dừng lại
      if (Object.keys(this.errors).length > 0) {
        return;
      }

      // Thực hiện yêu cầu API để đặt lại mật khẩu
      try {
        await axios.put(`/account/reset-password/${this.token}`, {
          password: this.password,
          password_confirmation: this.password_confirmation,
        });

        this.showToast('Mật khẩu đã được đặt lại thành công.', 'success');

        setTimeout(() => {
          this.$router.push('/login');
        }, 3000);
      } catch (error) {
        this.errors.general = error.response?.data?.message || 'Có lỗi xảy ra';
        this.showToast(this.errors.general, 'error');
      }
    },
    showToast(message, type = 'info') {
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

.form-heading {
  font-weight: bold;
}

.link {
  color: var(--secondary-color);
  text-decoration: none;
}

.link:hover {
  text-decoration: underline;
}
</style>
