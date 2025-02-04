<template>
    <header id="header">
        <div class="container">
            <div class="row align-items-center" id="headwrap">
                <!-- Logo -->
                <div class="col-md-3 col-sm-6 slogan">
                    <p class="site-title">
                        <router-link to="/reader">Quản Lý Mượn Sách</router-link>
                    </p>
                </div>


                <div class="col-md-5 col-sm-6 header__search-form">
                    <form id="search-form-pc" @submit.prevent="handleSearch">
                        <div class="input-group">
                            <input v-model="searchQuery" type="text" class="form-control" placeholder="Tìm sách..."
                                aria-label="Tìm sách..." required @focus="toggleSearchDropdown" />
                            <button class="btn" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Dropdown kết quả tìm kiếm -->
                    <div v-if="searchDropdownOpen && searchResults.length > 0" class="search-dropdown"
                        ref="searchDropdown">
                        <ul class="list-group">
                            <li v-for="result in searchResults" :key="result.id"
                                class="list-group-item d-flex align-items-center">
                                <img :src="`${host}/storage/${result.cover_image}`" alt="Cover Image"
                                    class="img-thumbnail" />
                                <div @click="openDetailModal(result)">
                                    <h5 class="mb-0">{{ result.title }}</h5>
                                    <p class="mb-0">Tác giả: {{ result.author }}</p>
                                    <p class="mb-0">Năm xuất bản: {{ result.published_year }}</p>
                                    <p class="mb-0">Còn lại: {{ result.quantity }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div v-if="searchDropdownOpen && searchResults.length === 0" class="search-dropdown"
                        ref="searchDropdown">
                        <p class="text-center">Không có kết quả nào.</p>
                    </div>



                </div>



                <!-- Profile -->
                <div class="col-md-4 profile">
                    <div class="dropdown d-flex align-items-center ">
                        <div class="d-flex align-items-center cursor-pointer" @click="toggleDropdown"
                            ref="dropdownContainer">
                            <img :src="userAvatar ? userAvatar : defaultAvatar" alt="Avatar"
                                class="avatar rounded-circle" />
                            <span class="ms-3">{{ userName }}</span>
                            <i class="fa-solid fa-caret-down ms-2"></i>
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
            </div>

            <!-- Navbar -->
            <div class="navbar-container mt-3">
                <nav class="navbar navbar-expand-lg navbar-dark">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarMenu">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-md-2 mt-sm-2">
                                <li class="nav-item">
                                    <router-link id="home-reader" class="nav-link" exact-active-class="active"
                                        to="/reader">
                                        Trang chủ
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link class="nav-link" active-class="active" v-scroll-to="'#return-books'"
                                        to="/reader/return-books">
                                        Sách đang mượn
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link class="nav-link" active-class="active" v-scroll-to="'#history-borrow'"
                                        to="/reader/history-borrow">
                                        Lịch Sử Mượn
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!-- Modal Chi Tiết -->
        <DetailBookModal :isVisible="isDetailModalVisible" :book="selectedBook"
            @update:isVisible="isDetailModalVisible = false" @open-borrow-modal="openBorrowModal" />

        <!-- Modal Mượn -->
        <BorrowBookModal :isVisible="isBorrowModalVisible" :bookId="selectedBookId"
            @update:isVisible="isBorrowModalVisible = false" />
        <!-- Modal cập nhật avatar -->
        <UpdateAvatarModal :isVisible="isAvatarModalVisible" @update:isVisible="isAvatarModalVisible = $event"
            @avatar-updated="loadUserData" />

        <!-- Modal profile -->
        <ProfileModal :isVisible="isProfileModalVisible" @update:isVisible="isProfileModalVisible = $event"
            @profile-updated="loadUserData" />

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
import UpdateAvatarModal from '../Modal/UpdateAvatarModal.vue';
import ProfileModal from '../Modal/ProfileModal.vue';
import UpdatePasswordModal from '../Modal/UpdatePasswordModal.vue';
import ConfirmationModal from '../Modal/ConfirmationModal.vue';
import DetailedUserModal from '../Modal/DetailedUserModal.vue';
import DetailBookModal from '../Modal/DetailBookModal.vue';
import BorrowBookModal from '../Reader/Borrow/BorrowBookModal.vue';
import axios from '../../axios';
export default {
    name: 'ReaderHeader',
    components: {
        UpdateAvatarModal,
        ProfileModal,
        UpdatePasswordModal,
        ConfirmationModal,
        DetailedUserModal,
        DetailBookModal,
        BorrowBookModal
    },
    data() {
        return {
            host: 'http://127.0.0.1:8000',
            searchQuery: "",
            searchResults: [],
            userName: '',
            userAvatar: '',
            membershipLevel: '',
            userData: {},
            isAvatarModalVisible: false,
            isProfileModalVisible: false,
            isPasswordModalVisible: false,
            isLogoutConfirmModalVisible: false,
            isDetailedUserModalVisible: false,
            dropdownOpen: false,
            searchDropdownOpen: false,
            defaultAvatar: '../../images/default_avatar.jpg',
            selectedBook: null,
            isDetailModalVisible: false,
            isBorrowModalVisible: false,
            selectedBookId: null,
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
                this.userName = response.data.first_name;
                this.userAvatar = response.data.avatar ? `http://127.0.0.1:8000/storage/${response.data.avatar}` : this.defaultAvatar;
                this.userData = response.data;
            } catch (error) {
                console.error('Failed to load user data:', error);
            }
        },
        openDetailModal(book) {
            this.selectedBook = book;
            this.isDetailModalVisible = true;
        },
        openBorrowModal(bookId) {
            this.selectedBookId = bookId;
            this.isBorrowModalVisible = true;
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
            localStorage.removeItem("roomId");
            await axios.post('/account/logout', {}, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('access_token')}`,
                },
            });
            try {
                localStorage.removeItem('access_token');
                this.$router.push('/login');
            } catch (error) {
                console.error('Logout failed:', error);
            }
        },
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen) {
                this.searchDropdownOpen = false;
            }
        },

        toggleSearchDropdown() {
            this.searchDropdownOpen = !this.searchDropdownOpen;
            if (this.searchDropdownOpen) {
                this.dropdownOpen = false;
            }
        },

        handleClickOutside(event) {
            if (this.$refs.dropdownContainer && !this.$refs.dropdownContainer.contains(event.target) && this.dropdownOpen) {
                this.dropdownOpen = false;
            }

            if (this.$refs.searchDropdown && !this.$refs.searchDropdown.contains(event.target) && this.searchDropdownOpen) {
                this.searchDropdownOpen = false;
            }
        },
        async handleSearch() {
            try {
                const response = await axios.get(`books/advanced-search`, {
                    params: {
                        keyword: this.searchQuery,
                    },
                });

                if (response.data.success) {
                    this.searchResults = response.data.data;
                    this.searchDropdownOpen = true;
                } else {
                    this.searchResults = [];
                    this.searchDropdownOpen = false;
                }
            } catch (error) {
                console.error('Tìm kiếm thất bại:', error);
            }
        },
    },
};
</script>


<style scoped>
#header {
    background: var(--background-color);
    padding: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 10;
}

.site-title a {
    color: var(--white-color);
    font-size: 26px;
    font-weight: var(--font-weight-bold);
    text-transform: uppercase;
    text-decoration: none;
    transition: color 0.3s ease;
    text-shadow: rgba(160, 205, 103, 0.91) 0px 0px 33px;
}

.site-title a:hover {
    opacity: 0.9;
}

.header__search-form {
    position: relative;

}

.header__search-form .form-control {
    border: 2px solid var(--input-border-color);
    border-radius: 30px !important;
    padding: 12px 40px 12px 40px;
    overflow: hidden;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, border-radius 0.3s ease;
}

.header__search-form .form-control:focus {
    border-color: var(--hover-color);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    outline: none;
    border-radius: 2px !important;
    z-index: 2;
}

.header__search-form .btn {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-color) !important;
    border: none;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}


.header__search-form .btn i {
    font-size: 18px;
    color: var(--text-color) !important;
    z-index: 100;
}

.input-group .form-control {
    border: none;
    box-shadow: none;
}

.input-group .btn i {
    color: var(--text-color);
    text-shadow: rgba(184, 181, 176, 0.9) 0px 0px 4px;

}

.search-dropdown {
    position: absolute;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px 0;
}

.list-group-item {
    padding: 15px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
}

.list-group-item:hover {
    background-color: #f1f1f1;
}

.list-group-item img {
    width: 60px;
    height: 80px;
    margin-right: 10px;
}

/* Navbar Styles */
.navbar-container {
    background-color: var(--primary-color);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}


.navbar-dark .navbar-nav .nav-link {
    color: var(--white-color);
    font-size: 16px;
    font-weight: var(--font-weight-medium);
    padding: 10px 20px;
    border-radius: 4px;
    transition: background-color 0.3s ease, color 0.3s ease;
}


.navbar-dark .navbar-nav .nav-link:hover,
.navbar-dark .navbar-nav .nav-link.active {
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    background-color: var(--hover-color);
    color: var(--white-color);
    outline: 0;
    transition: all .5s ease;
}


.navbar-dark .navbar-nav .dropdown-menu {
    background-color: var(--white-color);
    border-radius: 8px;
    padding: 10px 30px;
    border: none;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    transition: opacity 0.3s ease, transform 0.3s ease;
    padding-top: 6px;

}

.navbar-dark .navbar-nav .dropdown-menu .dropdown-item {
    padding: 10px 20px;
    font-size: 16px;
    color: var(--black-color);
    border-radius: 4px;
    transition: background-color 0.3s ease, transform 0.3s ease;
    font-weight: 400;
}

.navbar-dark .navbar-nav .dropdown-menu .dropdown-item:hover {
    background-color: #F1EAEA;
    color: var(--primary-color);
    transform: translateX(2px);
}





/* Profile Dropdown */
.profile {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}


.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--white-color);
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}


.profile .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--white-color);
    min-width: 200px;
    border: 1px solid var(--box-shadow-color);
    border-radius: 4px;
    box-shadow: 0 4px 8px var(--box-shadow-color);
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

@media (max-width: 767px) {
    .profile {
        margin-top: 8px;
        margin-left: 0;
    }

    .navbar-container {
        margin-top: 0;
    }

    .navbar-collapse {
        padding-top: 6px;
    }
}
</style>
