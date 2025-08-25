import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import Button from "../components/Button";
import {
  ResponsiveContainer,
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, Legend,
  PieChart, Pie, Cell
} from "recharts";
import Breadcrumbs from "../components/Breadcrumbs";
export default function Reports() {
  // monthly
  const [mData, setMData] = useState([]);
  const [mLoading, setMLoading] = useState(true);
  const [mErr, setMErr] = useState("");

  // categories (default: tekući mesec)
  const now = new Date();
  const [year, setYear]   = useState(now.getFullYear());
  const [month, setMonth] = useState(now.getMonth() + 1);
  const [cData, setCData] = useState([]);
  const [cLoading, setCLoading] = useState(true);
  const [cErr, setCErr] = useState("");

  useEffect(() => {
    (async () => {
      setMLoading(true); setMErr("");
      try {
        const { data } = await client.get("/reports/monthly");
        setMData(Array.isArray(data?.data) ? data.data : []);
      } catch (e) {
        setMErr(e?.response?.data?.message || "Greška pri učitavanju mesečnog izveštaja.");
      } finally {
        setMLoading(false);
      }
    })();
  }, []);

  async function loadCategories(y = year, m = month) {
    setCLoading(true); setCErr("");
    try {
      const { data } = await client.get("/reports/categories", { params: { year: y, month: m } });
      setCData(Array.isArray(data?.data) ? data.data : []);
    } catch (e) {
      setCErr(e?.response?.data?.message || "Greška pri učitavanju kategorija.");
    } finally {
      setCLoading(false);
    }
  }

  useEffect(() => { loadCategories(); }, []); // initial

  function onApplyCats(e) {
    e?.preventDefault?.();
    loadCategories(year, month);
  }

  const months = useMemo(() =>
    ["", "Januar","Februar","Mart","April","Maj","Jun","Jul","Avgust","Septembar","Oktobar","Novembar","Decembar"], []);

  return (
    <>
      <Topbar />
      <main className="container">
        <header className="hero">
        <Breadcrumbs />
          <h1>Izveštaji</h1>
          <p className="muted">Grafički prikaz finansija — pregled po mesecima i po kategorijama.</p>
        </header>

        {/* Mesečni trend (12 meseci) */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <h3 style={{ margin: 0, marginBottom: 8 }}>Prihodi vs Troškovi (poslednjih 12 meseci)</h3>
          {mErr && <div className="alert">{mErr}</div>}
          {mLoading ? (
            <p className="muted">Učitavanje…</p>
          ) : mData.length === 0 ? (
            <p className="muted">Nema podataka.</p>
          ) : (
            <div style={{ width: "100%", height: 320 }}>
              <ResponsiveContainer>
                <AreaChart data={mData} margin={{ top: 10, right: 20, left: 0, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="ym" />
                  <YAxis tickFormatter={(v) => fmtShort(v)} />
                  <Tooltip formatter={(val) => [fmtMoney(val), "Iznos"]} />
                  <Legend />
                  <Area type="monotone" dataKey="income"  name="Prihod"  fillOpacity={0.3} stroke="#22c55e" fill="#22c55e" />
                  <Area type="monotone" dataKey="expense" name="Trošak"  fillOpacity={0.3} stroke="#ef4444" fill="#ef4444" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          )}
        </section>

        {/* Raspodela troškova po kategorijama (pie) */}
        <section className="panel">
          <div style={{ display: "flex", justifyContent: "space-between", gap: 12, flexWrap: "wrap", alignItems: "end" }}>
            <h3 style={{ margin: 0 }}>Troškovi po kategorijama</h3>
            <form onSubmit={onApplyCats} style={{ display: "flex", gap: 8, alignItems: "end", flexWrap: "wrap" }}>
              <label style={{ minWidth: 120 }}>
                <div className="label">Godina</div>
                <input className="input" type="number" value={year} onChange={(e)=>setYear(Number(e.target.value||now.getFullYear()))}/>
              </label>
              <label style={{ minWidth: 160 }}>
                <div className="label">Mesec</div>
                <select className="input" value={month} onChange={(e)=>setMonth(Number(e.target.value))}>
                  {months.slice(1).map((n, i) => <option key={i+1} value={i+1}>{n}</option>)}
                </select>
              </label>
              <Button type="submit" variant="secondary">Primeni</Button>
            </form>
          </div>

          {cErr && <div className="alert" style={{ marginTop: 8 }}>{cErr}</div>}
          {cLoading ? (
            <p className="muted">Učitavanje…</p>
          ) : cData.length === 0 ? (
            <p className="muted">Nema troškova za izabrani period.</p>
          ) : (
            <div style={{ display: "grid", gridTemplateColumns: "1.2fr 1fr", gap: 16 }}>
              <div style={{ width: "100%", height: 320 }}>
                <ResponsiveContainer>
                  <PieChart>
                    <Tooltip formatter={(val, name) => [fmtMoney(val), name]} />
                    <Pie
                      data={cData}
                      dataKey="total"
                      nameKey="name"
                      innerRadius={60}
                      outerRadius={120}
                      paddingAngle={2}
                      label={({ name, percent }) => `${name} ${(percent*100).toFixed(0)}%`}
                    >
                      {cData.map((_, idx) => (
                        <Cell key={idx} fill={pieColor(idx)} />
                      ))}
                    </Pie>
                  </PieChart>
                </ResponsiveContainer>
              </div>
              <div className="panel" style={{ background: "transparent", border: "0" }}>
                <table className="table" style={{ width: "100%" }}>
                  <thead>
                    <tr><th>Kategorija</th><th style={{ textAlign: "right" }}>Iznos</th></tr>
                  </thead>
                  <tbody>
                    {cData.map((r, i) => (
                      <tr key={i}>
                        <td>{r.name}</td>
                        <td className="text-right">{fmtMoney(r.total)}</td>
                      </tr>
                    ))}
                    <tr>
                      <td style={{ fontWeight: 700 }}>Ukupno</td>
                      <td className="text-right" style={{ fontWeight: 700 }}>
                        {fmtMoney(cData.reduce((s,x)=>s+Number(x.total||0),0))}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </section>
      </main>
    </>
  );
}

/* ===== helpers ===== */
function fmtMoney(n) {
  const num = Number(n) || 0;
  return num.toLocaleString("sr-RS", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " RSD";
}
function fmtShort(n) {
  const num = Math.abs(Number(n)) || 0;
  if (num >= 1_000_000) return (n/1_000_000).toFixed(1) + "M";
  if (num >= 1_000)     return (n/1_000).toFixed(1) + "k";
  return String(n);
}
function pieColor(i) {
  const palette = ["#22c55e","#ef4444","#3b82f6","#f59e0b","#a855f7","#14b8a6","#e11d48","#10b981"];
  return palette[i % palette.length];
}
