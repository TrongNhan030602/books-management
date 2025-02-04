<template>
  <div class="container form-container mt-3">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 form-content">
        <h2 class="text-center mb-4 form-heading">Đăng Ký</h2>
        <form @submit.prevent="submitForm">
          <!-- Step 1 -->
          <div v-if="currentStep === 1">
            <h5>Bước 1: Thông tin cá nhân</h5>
            <div class="mb-3">
              <label for="last_name" class="form-label">Họ:</label>
              <input type="text" id="last_name" class="form-control" v-model="form.last_name" @blur="validateStep1"
                :class="{ 'is-invalid': errors.last_name }" />
              <div v-if="errors.last_name" class="invalid-feedback">{{ errors.last_name }}</div>
            </div>
            <div class="mb-3">
              <label for="first_name" class="form-label">Tên:</label>
              <input type="text" id="first_name" class="form-control" v-model="form.first_name" @blur="validateStep1"
                :class="{ 'is-invalid': errors.first_name }" />
              <div v-if="errors.first_name" class="invalid-feedback">{{ errors.first_name }}</div>
            </div>

            <div class="mb-3">
              <label for="dob" class="form-label">Ngày Sinh:</label>
              <input type="date" id="dob" class="form-control" v-model="form.dob" @blur="validateStep1"
                :class="{ 'is-invalid': errors.dob }" />
              <div v-if="errors.dob" class="invalid-feedback">{{ errors.dob }}</div>
            </div>
            <button type="button" class="btn btn-primary" @click="nextStep">Tiếp Theo</button>
          </div>

          <!-- Step 2 -->
          <div v-if="currentStep === 2">
            <h5>Bước 2: Thông tin liên hệ</h5>
            <div class="mb-3">
              <label for="address" class="form-label">Địa Chỉ:</label>
              <input type="text" id="address" class="form-control" v-model="form.address" @blur="validateStep2"
                :class="{ 'is-invalid': errors.address }" />
              <div v-if="errors.address" class="invalid-feedback">{{ errors.address }}</div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Số Điện Thoại:</label>
              <input type="text" id="phone" class="form-control" v-model="form.phone" @blur="validateStep2"
                :class="{ 'is-invalid': errors.phone }" />
              <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
            </div>
            <div class="d-flex">
              <button type="button" class="btn btn-secondary" @click="prevStep">Quay Lại</button>
              <button type="button" class="btn btn-primary" @click="nextStep">Tiếp Theo</button>
            </div>
          </div>

          <!-- Step 3 -->
          <div v-if="currentStep === 3">
            <h5>Bước 3: Thông tin tài khoản</h5>
            <div class="mb-3">
              <label for="email" class="form-label">Email:</label>
              <input type="email" id="email" class="form-control" v-model="form.email" @blur="validateStep3"
                :class="{ 'is-invalid': errors.email }" />
              <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Mật khẩu:</label>
              <input type="password" id="password" class="form-control" v-model="form.password" @blur="validateStep3"
                :class="{ 'is-invalid': errors.password }" />
              <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
            </div>
            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Xác nhận mật khẩu:</label>
              <input type="password" id="password_confirmation" class="form-control"
                v-model="form.password_confirmation" @blur="validateStep3"
                :class="{ 'is-invalid': errors.password_confirmation }" />
              <div v-if="errors.password_confirmation" class="invalid-feedback">{{ errors.password_confirmation }}</div>
            </div>
            <div class="d-flex">
              <button type="button" class="btn btn-secondary" @click="prevStep">Quay Lại</button>
              <button type="submit" class="btn btn-primary">Đăng Ký</button>
            </div>
          </div>
        </form>
        <p class="mt-3 text-center">
          <router-link to="/login" class="link">Trở về đăng nhập</router-link> |
          <router-link to="/forgot-password" class="link">Quên mật khẩu?</router-link>
        </p>
        <!-- Toast Message -->
        <ToastMessage v-if="toast.visible" :type="toast.type" :visible="toast.visible" @close="hideToast">
          {{ toast.message }}
        </ToastMessage>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios';
import ToastMessage from '../ToastMessage/ToastMessage.vue';
export default {
  components: {
    ToastMessage
  },
  data() {
    return {
      currentStep: 1,
      form: {
        first_name: '',
        last_name: '',
        dob: '',
        address: '',
        phone: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      errors: {},
      toast: {
        visible: false,
        type: 'info',
        message: ''
      }
    };
  },
  computed: {
    isFormValid() {
      if (this.currentStep === 1) {
        return this.form.first_name && this.form.last_name && this.form.dob && !this.errors.first_name && !this.errors.last_name && !this.errors.dob;
      } else if (this.currentStep === 2) {
        return this.form.address && this.form.phone && !this.errors.address && !this.errors.phone;
      } else if (this.currentStep === 3) {
        return (
          this.form.email &&
          this.form.password &&
          this.form.password_confirmation &&
          this.form.password === this.form.password_confirmation &&
          !this.errors.email &&
          !this.errors.password &&
          !this.errors.password_confirmation
        );
      }
      return false;
    },
  },
  methods: {
    nextStep() {
      this[`validateStep${this.currentStep}`]();
      if (this.isFormValid) {
        this.currentStep++;
      }
    },
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },
    async submitForm() {
      this[`validateStep${this.currentStep}`]();

      if (!this.isFormValid) return;

      try {
        this.errors = {};
        this.toast = { visible: false, type: 'info', message: '' };

        const response = await axios.post('/account/register', this.form);

        this.form = {
          first_name: '',
          last_name: '',
          dob: '',
          address: '',
          phone: '',
          email: '',
          password: '',
          password_confirmation: '',
        };

        this.toast = {
          visible: true,
          type: 'success',
          message: 'Đăng ký thành công!'
        };

        setTimeout(() => {
          this.$router.push('/login');
        }, 3000);
      } catch (error) {
        this.errors = error.response.data.errors || {};
        this.toast = {
          visible: true,
          type: 'error',
          message: 'Đăng ký thất bại: ' + (error.response.data.message || 'Có lỗi xảy ra')
        };
      }
    }
    ,
    hideToast() {
      this.toast.visible = false;
    },
    validateStep1() {
      this.errors.first_name = this.form.first_name ? '' : 'Họ là bắt buộc.';
      this.errors.last_name = this.form.last_name ? '' : 'Tên là bắt buộc.';
      this.errors.dob = this.form.dob ? '' : 'Ngày sinh là bắt buộc.';
      if (this.form.dob && !this.isValidDate(this.form.dob)) {
        this.errors.dob = 'Ngày sinh không hợp lệ.';
      }
      if (this.form.dob && this.isFutureDate(this.form.dob)) {
        this.errors.dob = 'Ngày sinh không thể là một ngày trong tương lai.';
      }
    },
    validateStep2() {
      this.errors.address = this.form.address ? '' : 'Địa chỉ là bắt buộc.';
      this.errors.phone = this.form.phone ? '' : 'Số điện thoại là bắt buộc.';
      if (this.form.phone && !/^\d{10}$/.test(this.form.phone)) {
        this.errors.phone = 'Số điện thoại không hợp lệ.';
      }
    },
    validateStep3() {
      this.errors.email = this.form.email ? '' : 'Email là bắt buộc.';
      if (this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        this.errors.email = 'Email không hợp lệ.';
      }
      this.errors.password = this.form.password ? '' : 'Mật khẩu là bắt buộc.';
      this.errors.password_confirmation = this.form.password_confirmation ? '' : 'Xác nhận mật khẩu là bắt buộc.';
      if (this.form.password !== this.form.password_confirmation) {
        this.errors.password_confirmation = 'Mật khẩu và xác nhận mật khẩu không khớp.';
      }
    },
    isValidDate(date) {
      return !isNaN(Date.parse(date));
    },
    isFutureDate(date) {
      const today = new Date();
      const inputDate = new Date(date);
      return inputDate > today;
    },
  },
};
</script>

<style scoped>
@import url('../../../css/form.css');

.btn {
  margin-right: 10px;
}

.btn:last-child {
  margin-right: 0;
}

.btn-primary {
  background: var(--secondary-color);
  border: none;
  padding: 14px;
  font-size: 16px;
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  border-radius: 6px;
  width: 50%;
  transition: background 0.3s ease;
}

.btn-secondary {
  background: #6c757d;
  border: none;
  padding: 14px;
  font-size: 16px;
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  border-radius: 6px;
  width: 50%;
  transition: background 0.3s ease;
}
</style>
