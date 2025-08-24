// src/pages/SavingsGoals.jsx
import { useEffect, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import TextInput from "../components/TextInput";
import Button from "../components/Button";

const PER_PAGE = 10;

// Tolerantno mapiranje polja iz backenda
function pickView(g) {
  const name = g.name ?? g.title ?? g.label ?? `Cilj #${g.id}`;
  const description = g.description ?? g.desc ?? "";
  const target = g.target_amount ?? g.goal_amount ?? g.target ?? g.amount ?? null;
  const saved = g.current_amount ?? g.saved_amount ?? g.saved ?? g.progress_amount ?? 0;
  const dueDate = g.due_date ?? g.deadline ?? g.target_date ?? null;
  const pct = target ? Math.min(100, Math.round((Number(saved) / Number(target)) * 100)) : null;
  return { id: g.id, name, description, target, saved, dueDate, pct, raw: g };
}

export default function SavingsGoals() {
  // lista + meta
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [page, setPage] = useState(1);

  // filter (pretraga po nazivu/opisu — backend može ignorisati ako nije podržano)
  const [q, setQ] = useState("");

  // kreiranje
  const [gName, setGName] = useState("");
  const [gTarget, setGTarget] = useState("");
  const [creating, setCreating] = useState(false);

  // uplate po ID-u cilja
  const [deposit, setDeposit] = useState({});

  async function fetchGoals(p = 1, query = "") {
    setLoading(true);
    setError("");
    try {
      const params = { page: p, per_page: PER_PAGE };
      if (query.trim()) params.q = query.trim();

      const { data } = await client.get("/savings-goals", { params });
      const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setItems(list);
      setMeta(data?.meta ?? null);
      setPage(data?.meta?.current_page ?? p);
    } catch (e) {
      setError(e?.response?.data?.message || "Greška pri učitavanju ciljeva.");
      setItems([]);
      setMeta(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchGoals(1, "");
  }, []);

  function onSearch(e) {
    e.preventDefault();
    fetchGoals(1, q);
  }

  async function handleCreate(e) {
    e.preventDefault();
    setCreating(true);
    try {
      const payload = {
        name: gName,
        target_amount: Number(gTarget),
      };
      await client.post("/savings-goals", payload);
      setGName("");
      setGTarget("");
      fetchGoals(1, q);
    } catch (e) {
      alert(e?.response?.data?.message || "Kreiranje cilja nije uspelo.");
    } finally {
      setCreating(false);
    }
  }

  async function handleDeposit(id) {
    const amount = Number(deposit[id]);
    if (!amount || amount <= 0) return;

    const g = items.find((x) => x.id === id);
    if (!g) return;
    const v = pickView(g);

    // prvo probaj PATCH sa samo current_amount
    try {
      await client.patch(`/savings-goals/${id}`, {
        current_amount: (Number(v.saved) || 0) + amount,
      });
    } catch {
      // fallback: neki backend traži "pun" payload za PUT
      try {
        await client.put(`/savings-goals/${id}`, {
          name: v.name,
          target_amount: Number(v.target) || 0,
          current_amount: (Number(v.saved) || 0) + amount,
          ...(v.dueDate ? { due_date: v.dueDate } : {}),
          description: v.description || "",
        });
      } catch (e2) {
        alert(e2?.response?.data?.message || "Uplata nije uspela.");
        return;
      }
    }

    setDeposit((d) => ({ ...d, [id]: "" }));
    fetchGoals(page, q);
  }

  async function handleDelete(id) {
    if (!confirm("Obrisati ovaj cilj?")) return;
    try {
      await client.delete(`/savings-goals/${id}`);
      const keepPage = meta?.current_page ?? page;
      fetchGoals(keepPage, q);
    } catch (e) {
      alert(e?.response?.data?.message || "Brisanje nije uspelo.");
    }
  }

  const canPrev = meta ? meta.current_page > 1 : page > 1;
  const canNext = meta ? meta.current_page < meta.last_page : items.length === PER_PAGE;

  return (
    <>
      <Topbar />
      <main className="container">
        <header className="hero">
          <h1>Ciljevi štednje</h1>
          <p className="muted">Kreiraj ciljeve, uplaćuj sredstva i prati napredak.</p>
        </header>

        {/* Pretraga */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <form onSubmit={onSearch} style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr auto" }}>
            <TextInput
              label="Pretraga"
              placeholder="npr. 'auto', 'odmor'…"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" variant="secondary">Pretraži</Button>
            </div>
          </form>
        </section>

        {/* Dodavanje novog cilja */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <h3 style={{ marginBottom: 8 }}>Novi cilj</h3>
          <form onSubmit={handleCreate} style={{ display: "grid", gap: 12, gridTemplateColumns: "2fr 1fr auto" }}>
            <TextInput
              label="Naziv cilja"
              value={gName}
              onChange={(e) => setGName(e.target.value)}
              placeholder="npr. Letovanje"
              required
            />
            <TextInput
              label="Ciljani iznos"
              type="number"
              min="0"
              step="0.01"
              value={gTarget}
              onChange={(e) => setGTarget(e.target.value)}
              placeholder="1000.00"
              required
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" loading={creating}>Dodaj</Button>
            </div>
          </form>
        </section>

        {/* Lista ciljeva */}
        <section className="panel">
          {error && <div className="alert alert--error">{error}</div>}
          {loading ? (
            <p className="muted">Učitavanje…</p>
          ) : items.length === 0 ? (
            <p className="muted">Nema ciljeva.</p>
          ) : (
            <div className="table-wrap">
              <table className="table">
                <thead>
                  <tr>
                    <th style={{ width: 56 }}>#</th>
                    <th>Naziv</th>
                    <th style={{ width: 240 }}>Napredak</th>
                    <th className="text-right" style={{ width: 120 }}>Ušteđeno</th>
                    <th className="text-right" style={{ width: 120 }}>Cilj</th>
                    <th style={{ width: 160 }}>Rok</th>
                    <th style={{ width: 220 }}>Uplata</th>
                    <th style={{ width: 120 }}>Akcije</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((g) => {
                    const v = pickView(g);
                    return (
                      <tr key={v.id}>
                        <td>{v.id}</td>
                        <td>
                          <div style={{ fontWeight: 600 }}>{v.name}</div>
                          <div className="muted" style={{ fontSize: 12 }}>
                            {v.description || "—"}
                          </div>
                        </td>
                        <td>
                          <div style={{ height: 8, background: "#eee", borderRadius: 8, overflow: "hidden" }}>
                            <div
                              style={{
                                width: `${v.pct ?? 0}%`,
                                height: "100%",
                                background: (v.pct ?? 0) >= 100 ? "#16a34a" : "#3b82f6",
                                transition: "width .2s",
                              }}
                            />
                          </div>
                          <div className="muted" style={{ fontSize: 12, marginTop: 4 }}>
                            {v.pct != null ? `${v.pct}%` : "—"}
                          </div>
                        </td>
                        <td className="text-right">{v.saved ?? "—"}</td>
                        <td className="text-right">{v.target ?? "—"}</td>
                        <td>{v.dueDate || "—"}</td>
                        <td>
                          <div style={{ display: "grid", gridTemplateColumns: "1fr auto", gap: 8 }}>
                            <input
                              type="number"
                              min="0"
                              step="0.01"
                              placeholder="npr. 100"
                              value={deposit[v.id] ?? ""}
                              onChange={(e) => setDeposit((d) => ({ ...d, [v.id]: e.target.value }))}
                            />
                            <Button variant="secondary" onClick={() => handleDeposit(v.id)}>
                              Uplati +
                            </Button>
                          </div>
                        </td>
                        <td>
                          <Button variant="danger" onClick={() => handleDelete(v.id)}>
                            Obriši
                          </Button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>

              <div className="pager" style={{ display: "flex", justifyContent: "space-between", marginTop: 12 }}>
                <Button
                  variant="secondary"
                  disabled={!canPrev}
                  onClick={() => canPrev && fetchGoals((meta?.current_page ?? page) - 1, q)}
                >
                  ← Prethodna
                </Button>
                <div className="muted">
                  {meta ? `Strana ${meta.current_page} / ${meta.last_page}` : `Strana ${page}`}
                </div>
                <Button
                  variant="secondary"
                  disabled={!canNext}
                  onClick={() => canNext && fetchGoals((meta?.current_page ?? page) + 1, q)}
                >
                  Sledeća →
                </Button>
              </div>
            </div>
          )}
        </section>
      </main>
    </>
  );
}
