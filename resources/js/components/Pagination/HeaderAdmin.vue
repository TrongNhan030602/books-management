<template>
  <header class="header d-flex justify-content-between align-items-center">
    <div class="logo flex-column">
      <router-link to="/" class="nav-link">
        <h2 class="ms-2"><i class="fas fa-tachometer-alt"></i> DashBoard</h2>
      </router-link>
      <span class="ms-3 pt-2">Welcome, {{ userName }}!</span>
    </div>
    <div class="user-info d-flex align-items-center" ref="dropdownContainer">
      <div class="dropdown">
        <div class="d-flex align-items-center cursor-pointer" @click="toggleDropdown">
          <img :src="userAvatar ? userAvatar : defaultAvatar" alt="Avatar" class="avatar rounded-circle" />
          <span class="ms-2">{{ userName }}</span>
          <i class="fa-solid fa-caret-down ms-2"></i>
        </div>
        <div class="align-items-center pt-1">
          <span class="membership-level"> {{ membershipLevel }}</span>
        </div>
        <div class="dropdown-menu" :class="{ show: dropdownOpen }">
          <a href="#" @click="openAvatarModal" class="dropdown-item">
            <i class="fa-solid fa-image me-2"></i> Cập nhật Avatar
          </a>
          <a href="#" @click="openProfileModal" class="dropdown-item">
            <i class="fas fa-user-edit me-2"></i> Cập nhật thông tin
          </a>
          <a href="#" @click="openPasswordModal" class="dropdown-item">
            <i class="fa-solid fa-lock me-2"></i> Đổi mật khẩu
          </a>
          <a href="#" @click="openDetailedUserModal" class="dropdown-item">
            <i class="fas fa-info-circle me-2"></i> Xem thông tin chi tiết
          </a>

          <a href="#" @click="openLogoutConfirmModal" class="dropdown-item">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </a>
        </div>
      </div>
    </div>

    <!-- Modal cập nhật avatar -->
    <UpdateAvatarModal :isVisible="isAvatarModalVisible" @update:isVisible="isAvatarModalVisible = $event" />

    <!-- Modal profile -->
    <ProfileModal :isVisible="isProfileModalVisible" @update:isVisible="isProfileModalVisible = $event" />

    <!-- Modal đổi mật khẩu -->
    <UpdatePasswordModal :isVisible="isPasswordModalVisible" @update:isVisible="isPasswordModalVisible = $event" />

    <!-- Modal xác nhận đăng xuất -->
    <ConfirmationModal :isVisible="isLogoutConfirmModalVisible" title="Xác nhận đăng xuất"
      message="Bạn có chắc chắn muốn đăng xuất không?" confirmButtonText="Đăng xuất" cancelButtonText="Hủy"
      @update:isVisible="isLogoutConfirmModalVisible = $event" @confirm="logout" />

    <!-- Modal xem thông tin chi tiết -->
    <DetailedUserModal :isVisible="isDetailedUserModalVisible" :user="userData"
      @update:isVisible="isDetailedUserModalVisible = $event" />
  </header>
</template>

<script>
import axios from '../../axios';
import Cookies from 'js-cookie';
import UpdateAvatarModal from '../Modal/UpdateAvatarModal.vue';
import ProfileModal from '../Modal/ProfileModal.vue';
import UpdatePasswordModal from '../Modal/UpdatePasswordModal.vue';
import ConfirmationModal from '../Modal/ConfirmationModal.vue';
import DetailedUserModal from '../Modal/DetailedUserModal.vue';


export default {
  name: 'HeaderAdmin',
  components: {
    UpdateAvatarModal,
    ProfileModal,
    UpdatePasswordModal,
    ConfirmationModal,
    DetailedUserModal,

  },
  data() {
    return {
      userName: '',
      userAvatar: '',
      membershipLevel: '',
      userData: {},
      isAvatarModalVisible: false,
      isProfileModalVisible: false,
      isPasswordModalVisible: false,
      isLogoutConfirmModalVisible: false,
      dropdownOpen: false,
      isDetailedUserModalVisible: false,
      defaultAvatar: '../../images/default_avatar.jpg',
    };
  },
  async created() {
    await this.loadUserData();
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    async loadUserData() {
      try {
        const response = await axios.get('/account/me', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('access_token')}`,
          },
        });
        this.userName = `${response.data.first_name}`;
        this.membershipLevel = response.data.membership_level;
        this.userAvatar = response.data.avatar ? `http://127.0.0.1:8000/storage/${response.data.avatar}` : this.defaultAvatar;
        this.userData = response.data;

      } catch (error) {
        console.error('Failed to load user data:', error);
      }
    },
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    openAvatarModal() {
      this.isAvatarModalVisible = true;
    },
    openProfileModal() {
      this.isProfileModalVisible = true;
    },
    openPasswordModal() {
      this.isPasswordModalVisible = true;
    },
    openDetailedUserModal() {
      this.isDetailedUserModalVisible = true;
    },
    openLogoutConfirmModal() {
      this.isLogoutConfirmModalVisible = true;
    },
    async logout() {
      try {
        localStorage.removeItem("roomId");
        await axios.post('/account/logout', {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('access_token')}`,
          },
        });
        Cookies.remove('access_token');
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    },
    handleClickOutside(event) {
      if (this.$refs.dropdownContainer && !this.$refs.dropdownContainer.contains(event.target)) {
        this.dropdownOpen = false;
      }
    },
  },
  watch: {
    isAvatarModalVisible(newVal) {
      if (!newVal) {
        this.loadUserData();
      }
    },
    isProfileModalVisible(newVal) {
      if (!newVal) {
        this.loadUserData();
      }
    },
  },
};
</script>



<style scoped>
.header {
  height: var(--header-height);
  background: var(--background-color);
  color: var(--black-color);
  padding: 0 20px;
  box-shadow: 0 4px 8px var(--box-shadow-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  transition: background-color 0.3s ease;

}



.logo {
  display: flex;
  align-items: center;
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.05);
}

.logo h2 {
  font-family: var(--font-family-secondary);
  font-weight: var(--font-weight-bold);
  font-size: 1.5em;
  color: var(--white-color);
  margin: 0;
}

.user-info {
  display: flex;
  align-items: center;
  position: relative;
  transition: transform 0.3s ease;
}


.user-info .dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--white-color);
  border: 1px solid var(--box-shadow-color);
  border-radius: 4px;
  box-shadow: 0 4px 8px var(--box-shadow-color);
  min-width: 180px;
  z-index: 1000;
  opacity: 0;
  display: none;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.dropdown-menu.show {
  display: block;
  opacity: 1;
  transform: translateY(5px);
}

.dropdown-item {
  padding: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  border-radius: 4px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.dropdown-item:hover {
  background-color: #f1eaea;
  color: var(--secondary-color);
  transform: translateX(1px);

}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--white-color);
  box-shadow: 0 0 10px rgba(255, 223, 0, 0.5);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.avatar:hover {
  transform: scale(1.1);
  box-shadow: 0 0 8px rgba(255, 223, 0, 0.7);
  cursor: pointer;
  text-shadow: 0 2px 1px rgba(0, 0, 0, 0.4), 0 0 1px rgba(255, 223, 0, 0.8);

}

.membership-level {
  font-size: 1rem;
  margin-top: 4px;
  margin-left: 10px;
  display: flex;
  align-items: center;
  font-weight: var(--font-weight-bold);
  padding: 2px 5px;
  text-shadow: 0 2px 1px rgba(0, 0, 0, 0.4), 0 0 1px rgba(255, 223, 0, 0.8);
  transition: transform 0.3s ease;
}

.membership-level:hover {
  opacity: 0.9;

}

.membership-level::before {
  content: 'Level: ';
  font-weight: var(--font-weight-medium);
  font-size: 0.875rem;
  color: var(--white-color);
  margin-right: 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px;
    height: auto;
  }

  .logo h2 {
    font-size: 1.2em;
  }

  .user-info {
    margin-top: 10px;
  }

  .avatar {
    width: 40px;
    height: 40px;
  }

  .membership-level {
    font-size: 0.875rem;
    margin-left: 5px;
  }
}
</style>
