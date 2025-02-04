<template>
  <div class="dashboard">
    <HeaderAdmin />

    <div class="main-layout">
      <aside class="sidebar">
        <nav>
          <ul>
            <li><router-link to="/" exact-active-class="active-link"><i class="fas fa-chart-line"></i> Tổng
                quan</router-link></li>
            <li><router-link to="/publishers" active-class="active-link"><i class="fas fa-building"></i> Nhà xuất
                bản</router-link></li>
            <li><router-link to="/categories" active-class="active-link"><i class="fa-solid fa-list"></i> Thể loại
              </router-link></li>
            <li><router-link to="/books" active-class="active-link"><i class="fas fa-book"></i> Quản lý
                sách</router-link></li>
            <li><router-link to="/users" active-class="active-link"><i class="fas fa-users"></i> Quản lý người
                dùng</router-link></li>
            <li><router-link to="/borrow-management" active-class="active-link"><i class="fas fa-bookmark"></i> Duyệt
                mượn Sách</router-link></li>
            <li><router-link to="/return-management" active-class="active-link"><i class="fas fa-undo"></i> Duyệt trả
                Sách</router-link></li>
            <li><router-link to="/transaction-management" active-class="active-link"><i
                  class="fa-solid fa-people-roof"></i>Quản lý
                giao dịch</router-link></li>
            <li><router-link to="/chats" active-class="active-link"><i class="fas fa-comments"></i>Tin
                nhắn</router-link></li>


          </ul>
        </nav>
      </aside>


      <div class="content-area">
        <section class="summary">
          <Summary />
        </section>

        <section class="main-content">
          <router-view />
        </section>
      </div>
    </div>

    <FooterComponent />
  </div>
</template>

<script>
import HeaderAdmin from '../Pagination/HeaderAdmin.vue';
import FooterComponent from '../Pagination/Footer.vue';
import StatisticsDashboard from '../Admin/StatisticsDashboard.vue';
import Summary from '../Admin/Summary.vue';

export default {
  name: 'MainLayout',
  components: {
    HeaderAdmin,
    FooterComponent,
    StatisticsDashboard,
    Summary,
  },
};
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-layout {
  display: flex;
  flex: 1;
  flex-direction: row;
  gap: 20px;
  padding: 20px;
  transition: background-color 0.3s ease;
}



.sidebar {
  width: 250px;
  background: var(--background-color);
  color: var(--white-color);
  padding: 20px;
  box-shadow: 2px 0 6px var(--box-shadow-color);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  border-radius: 6px;
}

.sidebar:hover {
  box-shadow: 2px 0 8px var(--box-shadow-color);
}

.sidebar nav ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.sidebar nav ul li {
  margin-bottom: 15px;
  transition: transform 0.3s ease;
}

.sidebar nav ul li:hover {
  transform: translateX(8px);
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
  transition: color 0.3s ease, background-color 0.3s ease;
  padding: 10px;
  border-radius: 4px;
}

.sidebar nav ul li a.active-link i {
  color: var(--white-color);
}

.sidebar nav ul li a i {
  margin-right: 10px;
  transition: transform 0.3s ease;
}

.sidebar nav ul li a:hover i {
  transform: rotate(-10deg);
}

.sidebar nav ul li a:hover {
  background-color: var(--hover-color);
}

.content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--light-bg-color);
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 6px var(--box-shadow-color);
  transition: box-shadow 0.3s ease, background-color 0.3s ease;
}

.content-area:hover {
  box-shadow: 0 4px 8px var(--box-shadow-color);
  background-color: var(--white-color);
}

.summary {
  background: linear-gradient(60deg, #69c5a5, #53c9e6, #72dbb6);
  padding: 20px;
  color: var(--white-color);
  border-radius: 8px;
  box-shadow: 0 2px 6px var(--box-shadow-color);
  margin-bottom: 20px;
  transition: background-color 0.3s ease;
}

.summary:hover {
  background-color: var(--hover-color);
}

.main-content {
  flex: 1;
  padding: 20px;
  background: var(--white-color);
  border-radius: 8px;
  box-shadow: 0 2px 6px var(--box-shadow-color);
  transition: box-shadow 0.3s ease;
}

@media (max-width: 768px) {
  .main-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    box-shadow: none;
  }

  .content-area {
    padding: 10px;
    margin-top: 10px;
  }
}
</style>
