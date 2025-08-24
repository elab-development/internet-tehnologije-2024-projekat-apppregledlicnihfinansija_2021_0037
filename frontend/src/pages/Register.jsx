// src/pages/auth/Register.jsx
import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext"; 

export default function Register() {
  const { register: registerUser, loading } = useAuth();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: "Test Korisnik",
    email: "",
    password: "",
    password_confirmation: "",
  });
  const [error, setError] = useState("");

  function onChange(e) {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    if (form.password !== form.password_confirmation) {
      setError("Lozinke se ne poklapaju.");
      return;
    }

    const res = await registerUser(form);
    if (res?.ok) {
      navigate("/login");
    } else {
      setError(res?.error || "Neuspešna registracija.");
    }
  }

  return (
    <main style={{ maxWidth: 420, margin: "64px auto", fontFamily: "system-ui" }}>
      <h1 style={{ marginBottom: 16 }}>Registracija</h1>

      {error && (
        <div
          style={{
            background: "#fee2e2",
            border: "1px solid #ef4444",
            color: "#991b1b",
            padding: 12,
            borderRadius: 8,
            marginBottom: 12,
          }}
        >
          {error}
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        style={{
          display: "grid",
          gap: 12,
          padding: 16,
          border: "1px solid #e5e7eb",
          borderRadius: 12,
          background: "#fff",
        }}
      >
        <label style={{ display: "grid", gap: 6 }}>
          <span>Ime</span>
          <input
            name="name"
            value={form.name}
            onChange={onChange}
            required
            style={{ padding: "10px 12px", borderRadius: 10, border: "1px solid #d1d5db" }}
          />
        </label>

        <label style={{ display: "grid", gap: 6 }}>
          <span>Email</span>
          <input
            name="email"
            type="email"
            value={form.email}
            onChange={onChange}
            placeholder="you@example.com"
            required
            style={{ padding: "10px 12px", borderRadius: 10, border: "1px solid #d1d5db" }}
          />
        </label>

        <label style={{ display: "grid", gap: 6 }}>
          <span>Lozinka</span>
          <input
            name="password"
            type="password"
            value={form.password}
            onChange={onChange}
            required
            style={{ padding: "10px 12px", borderRadius: 10, border: "1px solid #d1d5db" }}
          />
        </label>

        <label style={{ display: "grid", gap: 6 }}>
          <span>Potvrda lozinke</span>
          <input
            name="password_confirmation"
            type="password"
            value={form.password_confirmation}
            onChange={onChange}
            required
            style={{ padding: "10px 12px", borderRadius: 10, border: "1px solid #d1d5db" }}
          />
        </label>

        <button
          type="submit"
          disabled={loading}
          style={{
            padding: "10px 14px",
            borderRadius: 10,
            border: "1px solid #111827",
            background: loading ? "#9ca3af" : "#111827",
            color: "#fff",
            cursor: loading ? "not-allowed" : "pointer",
            fontWeight: 600,
            marginTop: 8,
          }}
        >
          {loading ? "Registracija..." : "Registruj se"}
        </button>
      </form>

      <p style={{ marginTop: 12 }}>
        Imaš nalog? <Link to="/login">Prijavi se</Link>
      </p>
    </main>
  );
}
