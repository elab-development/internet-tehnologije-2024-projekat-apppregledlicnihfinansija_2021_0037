import { createContext, useContext, useEffect, useMemo, useState } from "react";
import client from "../api/client";

const Ctx = createContext(undefined);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem("token"));
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);

  function firstError(err) {
    const api = err?.response?.data ?? err?.data;
    if (api?.message) return api.message;
    if (api?.errors && typeof api.errors === "object") {
      const flat = Object.values(api.errors).flat();
      if (flat[0]) return flat[0];
    }
    return err?.message || "Greška";
  }

  async function refreshMe() {
    try {
      const res = await client.get("/user");
      const u = res?.data?.data ?? res?.data ?? null;
      setUser(u);
    } catch {
      setUser(null);
    }
  }

  useEffect(() => {
    if (token) {
      client.defaults.headers.common.Authorization = `Bearer ${token}`;
      localStorage.setItem("token", token);
      refreshMe();
    } else {
      delete client.defaults.headers.common.Authorization;
      localStorage.removeItem("token");
      setUser(null);
    }
  }, [token]);

  async function login(email, password) {
    setLoading(true);
    try {
      const { data } = await client.post("/auth/login", { email, password });
      const t = data?.token;
      if (!t) throw new Error("Token nije stigao sa servera.");
      setToken(t);
      if (data?.user) setUser(data.user);
      else await refreshMe();
      return { ok: true };
    } catch (err) {
      return { ok: false, error: firstError(err) };
    } finally {
      setLoading(false);
    }
  }

  async function register(payload) {
    setLoading(true);
    try {
      await client.post("/auth/register", payload);
      return { ok: true };
    } catch (err) {
      return { ok: false, error: firstError(err) };
    } finally {
      setLoading(false);
    }
  }

  async function logout() {
    try { await client.post("/auth/logout"); } catch {}
    setToken(null);
    setUser(null);
    window.location.href = "/login";
  }

  const role = user?.role ?? "user";
  const points = Number(user?.points ?? 0);
  const isPremium = role === "premium";
  const isAuthenticated = !!token;

  const value = useMemo(
    () => ({
      token,
      user,
      role,
      points,
      isPremium,
      isAuthenticated,
      loading,
      login,
      register,
      logout,
      refreshMe,
      setUser,
      setToken,
    }),
    [token, user, role, points, isPremium, isAuthenticated, loading]
  );

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}

export function useAuth() {
  const ctx = useContext(Ctx);
  // U DEV/HMR nemoj rušiti aplikaciju kad HMR nakratko “izgubi” Provider
  if (!ctx) {
    if (import.meta.env.DEV) {
      console.warn("useAuth pozvan van <AuthProvider> (DEV/HMR).");
      return {
        token: null,
        user: null,
        role: "user",
        points: 0,
        isPremium: false,
        isAuthenticated: false,
        loading: false,
        login: async () => ({ ok: false, error: "Kontekst nije spreman" }),
        register: async () => ({ ok: false, error: "Kontekst nije spreman" }),
        logout: () => {},
        refreshMe: async () => {},
        setUser: () => {},
        setToken: () => {},
      };
    }
    throw new Error("useAuth mora biti unutar <AuthProvider>.");
  }
  return ctx;
}
