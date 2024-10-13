import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: "23ac53d7303716c97001", // Your Pusher App Key
    cluster: "ap1", // Your Pusher App Cluster
    wsHost: "ws-ap1.pusher.com", // Host
    wsPort: 443, // Port
    wssPort: 443, // Secure WebSocket Port
    forceTLS: true, // Use TLS
    enabledTransports: ["ws", "wss"], // Allowed transports
});
