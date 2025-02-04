import Echo from "laravel-echo";
import Pusher from "pusher-js";
import Cookies from "js-cookie";
window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: "pusher",
    key: "6e25d69de6144c9ef6d3",
    cluster: "ap1",
    forceTLS: true,
    auth: {
        headers: {
            Authorization: `Bearer ${Cookies.get("access_token")}`,
        },
    },
});

export default echo;
