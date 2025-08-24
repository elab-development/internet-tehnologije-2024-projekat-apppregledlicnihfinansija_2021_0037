import { useEffect, useMemo, useState } from "react";
import client from "../api/client";

function lastDayOfMonth(y, m /* 1-12 */) {
  return new Date(y, m, 0).getDate(); // npr. m=8 -> 31
}

export default function BudgetUsage({ year, month }) {
  const now = new Date();
  const y = year ?? now.getFullYear();
  const m = month ?? (now.getMonth() + 1);

  const [items, setItems] = useState([]);
  const [totals, setTotals] = useState({ limit: 0, spent: 0, percent: 0 });
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  const from = useMemo(() => `${y}-${String(m).padStart(2, "0")}-01`, [y, m]);
  const to = useMemo(() => {
    const d = lastDayOfMonth(y, m);
    return `${y}-${String(m).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
  }, [y, m]);

  async function load() {
    setLoading(true);
    setErr("");
    try {
      // 1) Budžeti 
      const { data: budRes } = await client.get("/budgets", {
        params: { per_page: 1000, month: m, year: y },
      });
      const budgetsRaw = Array.isArray(budRes?.data) ? budRes.data : (Array.isArray(budRes) ? budRes : []);
      const budgets = budgetsRaw.filter(b =>
        (b.month ? Number(b.month) === m : true) &&
        (b.year ? Number(b.year) === y : true)
      );

      // 2) Troškovi u tom mesecu (per_page "dovoljno veliko"; po potrebi paginirati)
      const { data: trRes } = await client.get("/transactions", {
        params: { type: "expense", from, to, per_page: 1000, sort: "date" },
      });
      const expenses = Array.isArray(trRes?.data) ? trRes.data : (Array.isArray(trRes) ? trRes : []);

      // 3) Grupisanje potrošnje po kategoriji
      const spentByCat = new Map();
      for (const t of expenses) {
        const cid = t.category_id ?? t.category?.id ?? null;
        if (!cid) continue;
        const prev = spentByCat.get(cid) ?? 0;
        spentByCat.set(cid, prev + Number(t.amount || 0));
      }

      // 4) Mapiranje: budžet + potrošnja + procenat
      const itemsCalc = budgets.map(b => {
        const limit = Number(b.amount || 0);
        const cid = b.category_id;
        const spent = Number(spentByCat.get(cid) || 0);
        const percent = limit > 0 ? Math.round((100 * spent) / limit) : 0;
        return {
          budget_id: b.id,
          category_id: cid,
          category: b.category?.name ?? (b.category_name ?? `Kategorija #${cid}`),
          limit,
          spent,
          percent,
          remaining: Math.max(0, limit - spent),
        };
      }).sort((a, b) => (b.percent - a.percent));

      const totalsLimit = itemsCalc.reduce((s, it) => s + it.limit, 0);
      const totalsSpent = itemsCalc.reduce((s, it) => s + it.spent, 0);
      const totalsPercent = totalsLimit > 0 ? Math.round((100 * totalsSpent) / totalsLimit) : 0;

      setItems(itemsCalc);
      setTotals({ limit: totalsLimit, spent: totalsSpent, percent: totalsPercent });
    } catch (e) {
      setErr(e?.message || "Greška pri učitavanju budžeta/potrošnje.");
      setItems([]);
      setTotals({ limit: 0, spent: 0, percent: 0 });
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); }, [from, to]);

  
  useEffect(() => {
    const onChanged = () => load();
    window.addEventListener("transactions:changed", onChanged);
    return () => window.removeEventListener("transactions:changed", onChanged);
  }, []);

  return (
    <section style={styles.card}>
      <h2 style={{ marginBottom: 12 }}>Budžet — {String(m).padStart(2, "0")}.{y}.</h2>

      {err && <div style={styles.alertError}>{err}</div>}
      {loading ? (
        <p className="muted">Učitavanje…</p>
      ) : items.length === 0 ? (
        <p className="muted">Nema postavljenih budžeta za ovaj mesec.</p>
      ) : (
        <div style={{ display: "grid", gap: 10 }}>
          {items.map((b) => (
            <div key={b.budget_id ?? `${b.category_id}-${m}-${y}`} style={{ display: "grid", gap: 6 }}>
              <div style={{ display: "flex", justifyContent: "space-between" }}>
                <div><b>{b.category}</b></div>
                <div style={{ color: "#555" }}>
                  {b.spent.toLocaleString()} / {b.limit.toLocaleString()} RSD ({b.percent}%)
                </div>
              </div>
              <div style={{ height: 8, background: "#eee", borderRadius: 999 }}>
                <div
                  style={{
                    width: `${Math.min(100, b.percent)}%`,
                    height: "100%",
                    borderRadius: 999,
                    background: b.percent >= 100 ? "#ef4444" : b.percent >= 80 ? "#f59e0b" : "#10b981",
                    transition: "width .3s ease",
                  }}
                />
              </div>
            </div>
          ))}
          <div style={{ marginTop: 6, color: "#444" }}>
            Ukupno: <b>{totals.spent.toLocaleString()}</b> / {totals.limit.toLocaleString()} RSD{" "}
            <span style={{ color: totals.percent>=100 ? "#ef4444" : totals.percent>=80 ? "#f59e0b" : "#10b981" }}>
              ({totals.percent}%)
            </span>
          </div>
        </div>
      )}
    </section>
  );
}

const styles = {
  card: {
    border: "1px solid #e9e9e9",
    borderRadius: 12,
    padding: 16,
    background: "#fff",
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
