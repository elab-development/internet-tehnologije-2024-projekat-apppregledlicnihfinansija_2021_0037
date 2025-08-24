// src/pages/Register.jsx
import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import Button from "../components/Button";
import TextInput from "../components/TextInput";


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

  const onChange = (e) =>
  setForm((f) => ({ ...f, [e.target.name]: e.target.value }));


  async function handleSubmit(e) {
    e.preventDefault();
    setError("");
    if (form.password !== form.password_confirmation) {
      setError("Lozinke se ne poklapaju.");
      return;
    }

    const res = await registerUser(form);
    if (res?.ok) navigate("/login");
    else setError(res.error || "Neuspešna registracija.");
  }

  return (
    <main className="auth" style={{ maxWidth: 420, margin: "64px auto" }}>
      <h1>Registracija</h1>
      {error && <div className="alert alert--error">{error}</div>}

      <form onSubmit={handleSubmit} className="panel" style={{ display: "grid", gap: 12 }}>
        <TextInput
          label="Ime"
          name="name"
          value={form.name}
          onChange={onChange}
          required
        />
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
        <TextInput
          label="Potvrda lozinke"
          name="password_confirmation"
          type="password"
          value={form.password_confirmation}
          onChange={onChange}
          required
        />

        <Button type="submit" variant="primary" loading={loading}>
          Registruj se
        </Button>
      </form>

      <p style={{ marginTop: 12 }}>
        Imaš nalog? <Link to="/login">Prijavi se</Link>
      </p>
    </main>
  );
}
