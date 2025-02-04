import axios from "axios";
import Cookies from "js-cookie";
// Tạo một instance của axios với cấu hình cơ bản
const instance = axios.create({
    baseURL: "/api", // Chuyển tất cả các yêu cầu đến proxy /api
    headers: {
        "Content-Type": "application/json",
    },
});

// Thêm một interceptor để đính kèm token vào mỗi yêu cầu
instance.interceptors.request.use(
    (config) => {
        const token = Cookies.get("access_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default instance;
