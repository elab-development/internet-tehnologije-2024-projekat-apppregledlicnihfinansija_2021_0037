// src/context/AuthContext.jsx
import { createContext, useContext, useEffect, useMemo, useState } from "react";
import client from "../api/client";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem("token"));
  const [loading, setLoading] = useState(false);

  // Kad se token promeni, ažuriraj axios header + localStorage
  useEffect(() => {
    if (token) {
      client.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      localStorage.setItem("token", token);
    } else {
      delete client.defaults.headers.common["Authorization"];
      localStorage.removeItem("token");
    }
  }, [token]);

  function firstError(err) {
    const api = err?.response?.data;
    if (api?.message) return api.message;
    if (api?.errors) {
      const flat = Object.values(api.errors).flat();
      if (flat[0]) return flat[0];
    }
    return err.message || "Greška";
  }
  
  async function login(email, password) {
    setLoading(true);
    try {
      const { data } = await client.post("/auth/login", { email, password });
      const t = data?.token || data?.data?.token;
      if (!t) throw new Error("Token nije stigao sa servera.");
      setToken(t);
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
    try { await client.post("/auth/logout"); } catch (_) {}
    setToken(null);
    window.location.href = "/login"; // opcionalno
  }
  

  const value = useMemo(
    () => ({
      token,
      isAuthenticated: !!token,
      loading,
      login,
      register,
      logout,
    }),
    [token, loading]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth mora biti unutar <AuthProvider>.");
  return ctx;
}
