// src/pages/Profile.jsx
import { useEffect, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import Button from "../components/Button";
import { useAuth } from "../context/AuthContext";
import BudgetUsage from "../components/BudgetUsage"

export default function Profile() {
  const [user, setUser] = useState(null);
  const [stats, setStats] = useState({
    transactions: null,
    budgets: null,
    categories: null,
    goals: null,
  });
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");
  const [upgrading, setUpgrading] = useState(false);
  const [msg, setMsg] = useState("");
  const { user: authUser, points, isPremium } = useAuth();
  const viewUser = user ?? authUser;




  useEffect(() => {
    let mounted = true;

    (async () => {
      setLoading(true);
      setErr("");
      try {
        
        let u;
        
          const { data } = await client.get("/user");
          u = data?.data ?? data;
     
        if (!mounted) return;
        setUser(u);

        // 2) Učitaj statistike (broj zapisa iz meta.total)
        const [tx, bu, ca, go] = await Promise.all([
          client.get("/transactions", { params: { per_page: 1 } }),
          client.get("/budgets", { params: { per_page: 1 } }),
          client.get("/categories", { params: { per_page: 1 } }),
          client.get("/savings-goals", { params: { per_page: 1 } }),
        ]);

        const total = (res) => res?.data?.meta?.total ?? (res?.data?.data?.length ?? 0);
        if (!mounted) return;
        setStats({
          transactions: total(tx),
          budgets: total(bu),
          categories: total(ca),
          goals: total(go),
        });
      } catch (e) {
        if (!mounted) return;
        setErr(e?.response?.data?.message || e.message || "Greška pri učitavanju profila.");
      } finally {
        if (mounted) setLoading(false);
      }
    })();

    return () => { mounted = false; };
  }, []);

  const initials = (user?.name || "?")
    .split(" ")
    .map((w) => w[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();

  async function downloadExport(format) {
    try {
      const res = await client.get("/transactions/export", {
        params: { format },
        responseType: "blob",
      });
      const blob = new Blob([res.data], {
        type: format === "pdf" ? "application/pdf" : "text/csv",
      });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      const ts = new Date().toISOString().slice(0, 10);
      a.download = `transactions_${ts}.${format}`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    } catch (e) {
      alert(e?.response?.data?.message || "Greška pri preuzimanju.");
    }
  }

  async function upgrade() {
    setUpgrading(true);
    setMsg("");
    try {
      const { data } = await client.post("/account/upgrade"); // POST /api/v1/account/upgrade
      setMsg(data?.message || "Uspešno!");
      // osveži user-a
      try {
        const { data: meRes } = await client.get("/user");
        setUser(meRes?.data ?? meRes);
      } catch { }
    } catch (e) {
      setMsg(e?.response?.data?.message || "Greška pri nadogradnji.");
    } finally {
      setUpgrading(false);
    }
  }

  return (
    <>
      <Topbar />
      <main style={{ maxWidth: 900, margin: "24px auto", padding: "0 16px" }}>
        <h1 style={{ marginBottom: 12 }}>Moj profil</h1>

        {err && (
          <div style={styles.alertError}>
            {err}
          </div>
        )}

        <section style={styles.card}>
          <div>
            <div style={{ fontSize: 18, fontWeight: 700 }}>{viewUser?.name || "—"}</div>
            <div style={{ color: "#555" }}>{viewUser?.email || "—"}</div>

            {viewUser?.created_at && (
              <div style={{ color: "#777", fontSize: 13, marginTop: 4 }}>
                Član od: {new Date(viewUser.created_at).toLocaleDateString()}
              </div>
            )}

            <div style={{ marginTop: 6 }}>
              <span style={{ fontSize: 13, color: "#666" }}>Uloga: </span>
              <b>{viewUser?.role ?? "user"}</b>
            </div>

            <div style={{ marginTop: 6 }}>
              Poeni: <b>{Number(points ?? 0)}</b>
              {isPremium && (
                <span
                  style={{
                    marginLeft: 8,
                    padding: "2px 8px",
                    borderRadius: 999,
                    background: "#f5f5f5",
                    border: "1px solid #e5e5e5",
                    fontSize: 12,
                  }}
                >
                  Premium
                </span>
              )}
            </div>
          </div>

          <div style={{ marginTop: 16, display: "flex", gap: 8, flexWrap: "wrap" }}>
            <button onClick={() => downloadExport("csv")} style={styles.btn}>
              ⬇️ Preuzmi CSV
            </button>

            {isPremium && (
              <button onClick={() => downloadExport("pdf")} style={styles.btn}>
                ⬇️ Preuzmi PDF
              </button>
            )}
          </div>

        </section>

        {msg && (
          <div className="alert" style={{ marginTop: 8 }}>
            {msg}
          </div>
        )}

        {!isPremium ? (
          <div style={{ marginTop: 8 }}>
            <Button onClick={upgrade} loading={upgrading}>
              ⭐ Postani premium
            </Button>
          </div>
        ) : (
          <p style={{ marginTop: 8 }} className="muted">
            Premium nalog je aktivan — PDF eksport i ostale premium opcije su otključane. 🎉
          </p>
        )}


        <section style={{ ...styles.card, marginTop: 16 }}>
          <h2 style={{ marginBottom: 12 }}>Statistika</h2>
          {loading ? (
            <p style={{ color: "#777" }}>Učitavanje…</p>
          ) : (
            <div style={styles.statsGrid}>
              <Stat label="Transakcije" value={fmt(stats.transactions)} />
              <Stat label="Budžeti" value={fmt(stats.budgets)} />
              <Stat label="Kategorije" value={fmt(stats.categories)} />
              <Stat label="Ciljevi štednje" value={fmt(stats.goals)} />
            </div>
          )}
        </section>

        <BudgetUsage />
      </main>
    </>
  );
}

function Stat({ label, value }) {
  return (
    <div style={styles.statItem}>
      <div style={{ fontSize: 28, fontWeight: 800 }}>{value}</div>
      <div style={{ color: "#555" }}>{label}</div>
    </div>
  );
}

function fmt(v) {
  if (v === null || v === undefined) return "—";
  return new Intl.NumberFormat().format(v);
}

const styles = {
  card: {
    border: "1px solid #e9e9e9",
    borderRadius: 12,
    padding: 16,
    background: "#fff",
  },
  avatar: {
    width: 56,
    height: 56,
    borderRadius: "50%",
    background: "#111",
    color: "#fff",
    display: "grid",
    placeItems: "center",
    fontWeight: 800,
    letterSpacing: 1,
  },
  statsGrid: {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(160px, 1fr))",
    gap: 12,
  },
  statItem: {
    border: "1px solid #eee",
    borderRadius: 10,
    padding: 14,
    background: "#fafafa",
  },
  btn: {
    padding: "8px 12px",
    borderRadius: 8,
    border: "1px solid #ddd",
    background: "#fff",
    cursor: "pointer",
  },
  alertError: {
    border: "1px solid #f2b1b1",
    background: "#fdeeee",
    color: "#7a0b0b",
    borderRadius: 8,
    padding: "10px 12px",
    marginBottom: 12,
  },
};
