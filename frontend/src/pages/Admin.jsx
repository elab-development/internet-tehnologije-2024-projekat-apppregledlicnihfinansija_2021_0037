import { useEffect, useState } from "react";
import { Navigate } from "react-router-dom";
import Topbar from "../components/Topbar";
import client from "../api/client";
import { useAuth } from "../context/AuthContext";
import Breadcrumbs from "../components/Breadcrumbs";


export default function Admin() {
  const { isAuthenticated, role } = useAuth();
  const [stats, setStats] = useState(null);
  const [err, setErr] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (role !== "admin") return;
    (async () => {
      setLoading(true); setErr("");
      try {
        const { data } = await client.get("/admin/stats");
        setStats(data);
      } catch (e) {
        setErr(e?.response?.data?.message || "Greška pri učitavanju statistike.");
      } finally {
        setLoading(false);
      }
    })();
  }, [role]);

  if (!isAuthenticated) return <Navigate to="/login" replace />;
  if (role !== "admin") return <Navigate to="/" replace />;

  return (
    <>
      <Topbar />
      <main className="container" style={{ maxWidth: 1000, padding: 16 }}>
      <Breadcrumbs />
        <h1>Admin — Statistika</h1>
        {err && <div className="alert alert--error">{err}</div>}
        {loading ? (
          <p className="muted">Učitavanje…</p>
        ) : (
          <section className="panel" style={{ display: "grid", gap: 12 }}>
            <div style={grid}>
              <Stat label="Korisnici" value={stats?.users} />
              <Stat label="Transakcije" value={stats?.transactions} />
              <Stat label="Budžeti" value={stats?.budgets} />
              <Stat label="Kategorije" value={stats?.categories} />
              <Stat label="Ciljevi štednje" value={stats?.goals} />
              <Stat label="Notifikacije" value={stats?.alerts} />
            </div>
            <div className="muted" style={{ fontSize: 12 }}>
              Ažurirano: {stats?.generated_at ? new Date(stats.generated_at).toLocaleString() : "—"}
            </div>
          </section>
        )}
      </main>
    </>
  );
}

function Stat({ label, value }) {
  return (
    <div style={{ border:"1px solid #eee", borderRadius:10, padding:14, background:"#fafafa" }}>
      <div style={{ fontSize:28, fontWeight:800 }}>{fmt(value)}</div>
      <div style={{ color:"#555" }}>{label}</div>
    </div>
  );
}
function fmt(v){ return v==null ? "—" : new Intl.NumberFormat().format(v); }
const grid = { display:"grid", gridTemplateColumns:"repeat(auto-fit, minmax(160px, 1fr))", gap:12 };
