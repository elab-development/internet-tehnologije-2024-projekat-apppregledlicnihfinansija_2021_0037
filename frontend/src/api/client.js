import axios from "axios";

const client = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api/v1",
  headers: { Accept: "application/json" },
});

// ubacujemo Bearer token ako postoji
client.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// globalno hvatamo 401 i čistimo token
client.interceptors.response.use(
  (res) => res,
  (err) => {
    const status = err?.response?.status ?? null;
    const data = err?.response?.data;
    const message =
      (data && typeof data === "object" && (data.error || data.message)) ||
      err?.message ||
      "Greška u mreži ili serveru";

    if (status === 401) {
      localStorage.removeItem("token");
      // Preusmerenje rešava UI (AuthContext/route guard), ne interceptor
    }

    return Promise.reject({ status, message, data });
  }
);

export default client;

