// src/pages/Login.jsx
import { useState } from "react";
import { useNavigate, useLocation, Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import Button from "../components/Button";
import TextInput from "../components/TextInput";


export default function Login() {
  const { login, loading } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [form, setForm] = useState({
    email: "test@example.com",
    password: "password",
  });
  const [error, setError] = useState("");

  const from = location.state?.from?.pathname || "/";

  const onChange = (e) =>
    setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");
    const res = await login(form.email, form.password);
    if (res.ok) navigate(from, { replace: true });
    else setError(res.error || "Neuspešna prijava.");
  }

  return (
    <main className="auth" style={{ maxWidth: 420, margin: "64px auto" }}>
      <h1>Prijava</h1>
      {error && <div className="alert alert--error">{error}</div>}

      <form onSubmit={handleSubmit} className="panel">
        <TextInput
          label="Email"
          name="email"
          type="email"
          value={form.email}
          onChange={onChange}
          placeholder="you@example.com"
          required
        />
        <TextInput
          label="Lozinka"
          name="password"
          type="password"
          value={form.password}
          onChange={onChange}
          required
        />
        <Button type="submit" variant="primary" loading={loading}>
          Prijavi se
        </Button>
      </form>

      <p style={{ marginTop: 12 }}>
        Nemaš nalog? <Link to="/register">Registruj se</Link>
      </p>
    </main>
  );
}



