// src/pages/ResetPassword.jsx
import { useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";

export default function ResetPassword() {
  const [email, setEmail] = useState("");
  const [msg, setMsg] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setMsg(""); setError(""); setLoading(true);
    try {
      await client.post("/auth/forgot-password", { email });
      setMsg("Ako nalog postoji, poslali smo instrukcije za reset lozinke.");
    } catch (err) {
      setError(err.response?.data?.message || "Greška pri slanju zahteva.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <>
      <Topbar />
      <main className="container" style={{ maxWidth: 480 }}>
        <h1>Reset lozinke</h1>
        <p className="muted" style={{ marginBottom: 12 }}>
          Unesi email; dobićeš link/token za reset (API ruta: <code>/auth/forgot-password</code>).
        </p>

        {msg && <div className="alert alert--ok">{msg}</div>}
        {error && <div className="alert alert--error">{error}</div>}

        <form onSubmit={handleSubmit} className="panel" style={{ display: "grid", gap: 12 }}>
          <label>
            Email
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="you@example.com"
              style={{ width: "100%" }}
            />
          </label>
          <button disabled={loading} type="submit">
            {loading ? "Slanje…" : "Pošalji link"}
          </button>
        </form>
      </main>
    </>
  );
}
