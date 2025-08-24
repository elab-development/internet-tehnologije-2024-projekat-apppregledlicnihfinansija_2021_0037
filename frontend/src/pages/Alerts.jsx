import { useEffect, useState, useMemo } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import Button from "../components/Button";

export default function Alerts() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");
  const [marking, setMarking] = useState(false);

  async function load() {
    setLoading(true); setErr("");
    try {
      const { data } = await client.get("/alerts");
      const list = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
      setItems(list);
    } catch (e) {
      setErr(e?.response?.data?.message || "Greška pri učitavanju notifikacija.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); }, []);

  async function markRead(id) {
    try {
      await client.patch(`/alerts/${id}/read`);
      await load();
      window.dispatchEvent(new Event("transactions:changed"));
    } catch (e) {
      alert(e?.response?.data?.message || "Nije uspelo označavanje.");
    }
  }

  async function markAll() {
  if (!confirm("Označiti sve kao pročitane?")) return;
  setMarking(true);
  try {
    await client.patch("/alerts/read-all");
    await load();
    // obavesti zvonce da osveži broj
    window.dispatchEvent(new Event("alerts:changed"));
    window.dispatchEvent(new Event("transactions:changed")); 
  } finally {
    setMarking(false);
  }
}


  return (
    <>
      <Topbar />
      <main className="container" style={{ maxWidth: 900, padding: "16px" }}>
        <header style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom: 12 }}>
          <h1 style={{ margin: 0 }}>Notifikacije</h1>
          {items.some(a => !a.read_at) && (
            <Button variant="secondary" onClick={markAll} loading={marking}>
              Označi sve kao pročitane
            </Button>
          )}
        </header>

        {err && <div className="alert alert--error">{err}</div>}

        {loading ? (
          <p className="muted">Učitavanje…</p>
        ) : items.length === 0 ? (
          <p className="muted">Nema notifikacija.</p>
        ) : (
          <div style={{ display: "grid", gap: 12 }}>
            {items.map((a) => (
              <AlertCard key={a.id} alert={a} onRead={() => markRead(a.id)} />
            ))}
          </div>
        )}
      </main>
    </>
  );
}

/* ====== POMOĆNE KOMPONENTE ====== */

function AlertCard({ alert, onRead }) {
  const created = useMemo(() => {
    const d = alert.created_at ? new Date(alert.created_at) : null;
    return d ? d.toLocaleString() : "—";
  }, [alert.created_at]);

  const badgeColor = alert.type === "budget_exceeded"
    ? "#c1121f"
    : alert.type === "budget_warning"
    ? "#b45309"
    : "#0b7285";

  return (
    <div className="panel" style={{ display:"grid", gap: 8 }}>
      <div style={{ display:"flex", gap:8, alignItems:"center", flexWrap:"wrap" }}>
        <span style={{
          display:"inline-block",
          padding:"2px 8px",
          borderRadius:999,
          background:"#f5f5f5",
          border:"1px solid #e5e5e5",
          color: badgeColor,
          fontWeight:700,
          fontSize:12
        }}>
          {alert.type}
        </span>
        <div style={{ fontWeight: 700, fontSize: 16 }}>{alert.title}</div>
      </div>

      {alert.message && <div className="muted">{alert.message}</div>}

      <AlertMeta meta={alert.meta} />

      <div style={{ display:"flex", gap:12, justifyContent:"space-between", alignItems:"center" }}>
        <span className="muted" style={{ fontSize: 12 }}>
          Kreirano: {created}
          {alert.read_at && <> · Pročitano: {new Date(alert.read_at).toLocaleString()}</>}
        </span>
        {!alert.read_at && (
          <Button variant="secondary" onClick={onRead}>
            Označi kao pročitano
          </Button>
        )}
      </div>
    </div>
  );
}

function AlertMeta({ meta }) {
  if (!meta || typeof meta !== "object") return null;

  // specijalni prikaz za budžet (month/year/category_id/spent/limit)
  const hasBudget =
    (meta.month != null || meta.year != null) &&
    (meta.spent != null) &&
    (meta.limit != null);

  if (hasBudget) {
    const monthName = monthToName(meta.month);
    const spent = Number(meta.spent) || 0;
    const limit = Math.max(0, Number(meta.limit) || 0);
    const ratio = limit > 0 ? Math.min(1, spent / limit) : 0;
    const pct = Math.round(ratio * 100);

    const barColor =
      ratio >= 1 ? "#dc2626" : ratio >= 0.8 ? "#d97706" : "#16a34a";

    return (
      <div style={{
        border:"1px solid #eee", borderRadius:8, padding:12, background:"#fafafa"
      }}>
        <div style={{ display:"flex", justifyContent:"space-between", marginBottom:6 }}>
          <div>
            <b>Budžet:</b>{" "}
            {monthName} {meta.year} · Kategorija {meta.category_id ?? "—"}
          </div>
          <div>
            {fmtMoney(spent)} / {fmtMoney(limit)} ({pct}%)
          </div>
        </div>
        <div style={{
          height:10, background:"#eee", borderRadius:999, overflow:"hidden"
        }}>
          <div style={{
            width: `${Math.min(100, pct)}%`,
            height:"100%",
            background: barColor,
            transition:"width .3s ease"
          }} />
        </div>
      </div>
    );
  }

  // generički (ako nema specijalnog formata)
  return (
    <div style={{
      border:"1px solid #eee", borderRadius:8, padding:12, background:"#fafafa"
    }}>
      <table style={{ width:"100%", borderCollapse:"collapse", fontSize:14 }}>
        <tbody>
          {Object.entries(meta).map(([k,v]) => (
            <tr key={k}>
              <td style={{ padding:"4px 8px", color:"#555", width:160 }}>{k}</td>
              <td style={{ padding:"4px 8px" }}>{String(v)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

/* ====== UTIL ====== */

function monthToName(m) {
  const names = ["", "Januar","Februar","Mart","April","Maj","Jun","Jul","Avgust","Septembar","Oktobar","Novembar","Decembar"];
  const i = Number(m);
  return names[i] || `Mesec ${m ?? "?"}`;
}
function fmtMoney(n) {
  const num = Number(n) || 0;
  return num.toLocaleString("sr-RS", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " RSD";
}
