import { createApp } from "vue";
import router from "./router/index.js";
import App from "./components/App.vue";

import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "@fortawesome/fontawesome-free/css/all.min.css";

// Import VueScrollTo
import VueScrollTo from "vue-scrollto";

const app = createApp(App);

// Sử dụng Vue Router và VueScrollTo
app.use(router);
app.use(VueScrollTo, {
    duration: 500, // Thời gian cuộn 500ms
    easing: "ease", // Hiệu ứng cuộn mượt
    offset: -50, // Điều chỉnh offset nếu có header cố định
});

app.mount("#app");
