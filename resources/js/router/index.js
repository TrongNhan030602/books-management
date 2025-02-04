import { createRouter, createWebHistory } from "vue-router";
import axios from "../axios";
import Cookies from "js-cookie";

import Login from "../components/Auth/Login.vue";
import Register from "../components/Auth/Register.vue";
import ForgotPassword from "../components/Auth/ForgotPassword.vue";
import ResetPassword from "../components/Auth/ResetPassword.vue";
// Admin
import AdminLayout from "../components/views/AdminLayout.vue";
import Publishers from "../components/Admin/Publishers/Publisher.vue";
import StatisticsDashboard from "../components/Admin/StatisticsDashboard.vue";
import Books from "../components/Admin/Books/Books.vue";
import Users from "../components/Admin/Users/Users.vue";
import Category from "../components/Admin/Categories/Category.vue";

//Reader
import ReaderLayout from "../components/views/ReaderLayout.vue";
import BorrowBookComponent from "../components/Reader/Borrow/BorrowBookComponent.vue";
import BorrowHistoryComponent from "../components/Reader/Borrow/BorrowHistoryComponent.vue";
import ReturnBooks from "../components/Reader/Return/ReturnBookComponent.vue";
import Wishlist from "../components/Reader/Wishlist/Wishlist.vue";
import Reviews from "../components/Reader/Review/Reviews.vue";
import Notifications from "../components/Reader/Notification/Notifications.vue";
import Penalties from "../components/Reader/Penalty/Penalties.vue";
// Chat
import ChatComponent from "../components/Chat/ChatRoom.vue";
const isAuthenticated = async () => {
    try {
        const response = await axios.get("/account/me", {
            headers: {
                Authorization: `Bearer ${Cookies.get("access_token")}`,
            },
        });
        if (response.status === 200) {
            return { authenticated: true, role: response.data.role }; // Lấy role từ response
        }
    } catch (error) {
        return { authenticated: false, role: null };
    }
    return { authenticated: false, role: null };
};

const routes = [
    {
        path: "/",
        component: AdminLayout,
        meta: { requiresAuth: true, role: "Admin" }, // Chỉ Admin có thể truy cập layout này
        children: [
            {
                path: "",
                component: StatisticsDashboard,
                name: "StatisticsDashboard",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/publishers",
                component: Publishers,
                name: "Publishers",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/categories",
                component: Category,
                name: "Categories",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/books",
                component: Books,
                name: "Books",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/users",
                component: Users,
                name: "Users",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/borrow-management",
                component: () =>
                    import(
                        "../components/Admin/BorrowTransactions/AdminBorrowManagement.vue"
                    ),
                name: "BorrowManagement",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/return-management",
                component: () =>
                    import(
                        "../components/Admin/BorrowTransactions/AdminReturnManagement.vue"
                    ),
                name: "ReturnManagement",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "/transaction-management",
                component: () =>
                    import(
                        "../components/Admin/TransactionManagement/TransactionList.vue"
                    ),
                name: "TransactionManagement",
                meta: { requiresAuth: true, role: "Admin" },
            },
            {
                path: "chats",
                component: ChatComponent,
                name: "ChatComponent",
                meta: { requiresAuth: true, role: "Admin" },
            },
        ],
    },
    { path: "/login", name: "Login", component: Login },
    { path: "/register", name: "Register", component: Register },
    {
        path: "/forgot-password",
        name: "ForgotPassword",
        component: ForgotPassword,
    },
    {
        path: "/reset-password",
        name: "ResetPassword",
        component: ResetPassword,
        props: (route) => ({ token: route.query.token }),
    },
    {
        path: "/reader",
        component: ReaderLayout,
        meta: { requiresAuth: true, role: "Reader" }, // Chỉ Reader có thể truy cập layout này
        children: [
            {
                path: "",
                component: BorrowBookComponent,
                name: "BorrowBookComponent",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "return-books",
                component: ReturnBooks,
                name: "ReturnBooks",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "history-borrow",
                component: BorrowHistoryComponent,
                name: "BorrowHistoryComponent",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "notifications",
                component: Notifications,
                name: "Notifications",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "penalties",
                component: Penalties,
                name: "Penalties",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "reviews",
                component: Reviews,
                name: "Reviews",
                meta: { requiresAuth: true, role: "Reader" },
            },
            {
                path: "wishlist",
                component: Wishlist,
                name: "Wishlist",
                meta: { requiresAuth: true, role: "Reader" },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const { authenticated, role } = await isAuthenticated();

    if (to.matched.some((record) => record.meta.requiresAuth)) {
        if (authenticated) {
            if (to.meta.role && to.meta.role !== role) {
                // Chuyển hướng nếu vai trò không khớp với route yêu cầu
                next("/login");
            } else {
                next();
            }
        } else {
            next("/login");
        }
    } else {
        next();
    }
});

export default router;
