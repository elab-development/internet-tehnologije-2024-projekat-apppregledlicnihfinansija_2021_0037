// src/pages/Budgets.jsx
import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import TextInput from "../components/TextInput";
import Button from "../components/Button";

const PER_PAGE = 10;

function monthToRange(yyyyMm) {
  // yyyyMm: '2025-08' → start: '2025-08-01', end: '2025-08-31'
  if (!yyyyMm) return { start: "", end: "" };
  const [y, m] = yyyyMm.split("-").map(Number);
  const start = new Date(y, m - 1, 1);
  const end = new Date(y, m, 0); // last day of month
  const pad = (n) => String(n).padStart(2, "0");
  return {
    start: `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`,
    end: `${end.getFullYear()}-${pad(end.getMonth() + 1)}-${pad(end.getDate())}`,
  };
}

export default function Budgets() {
  // lista + meta
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // filteri
  const [q, setQ] = useState("");              // pretraga po nazivu/opisu (ako je podržano)
  const [categoryId, setCategoryId] = useState("");
  const [month, setMonth] = useState("");       // input type="month" npr. 2025-08
  const [page, setPage] = useState(1);

  // kategorije
  const [cats, setCats] = useState([]);
  const [catsLoading, setCatsLoading] = useState(true);

  // kreiranje
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({
    name: "",
    amount: "",        // ⟵ ako backend očekuje `limit`, samo preimenuj u payloadu
    month: "",         // bira se kao 2025-08; u payload prevodimo u start/end
    category_id: "",
    notes: "",
  });

  // dohvat kategorija
  async function fetchCategories() {
    setCatsLoading(true);
    try {
      const { data } = await client.get("/categories", { params: { per_page: 100 } });
      const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setCats(list);
    } catch {
      setCats([]);
    } finally {
      setCatsLoading(false);
    }
  }

  // dohvat budžeta
  async function fetchBudgets(p = 1) {
    setLoading(true);
    setError("");
    try {
      const params = { page: p, per_page: PER_PAGE };
      if (q.trim()) params.q = q.trim();
      if (categoryId) params.category_id = categoryId;
      if (month) {
        const { start, end } = monthToRange(month);
        params.from = start;
        params.to = end;
      }
      const { data } = await client.get("/budgets", { params });
      const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setItems(list);
      setMeta(data?.meta ?? null);
      setPage(data?.meta?.current_page ?? p);
    } catch (e) {
      setError(e?.response?.data?.message || "Greška pri učitavanju budžeta.");
      setItems([]);
      setMeta(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchCategories();
    fetchBudgets(1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // submit filtera
  function onSearch(e) {
    e?.preventDefault?.();
    fetchBudgets(1);
  }

  // kreiranje novog budžeta
  async function handleCreate(e) {
    e.preventDefault();
    setCreating(true);
    try {
      const { start, end } = monthToRange(form.month);

      // ⚠️ PRILAGODI OVO svojim poljima u backendu:
      // ako kontroler očekuje `limit`, promeni `amount: Number(form.amount)` u `limit: Number(form.amount)`
      // ako očekuje `starts_at` i `ends_at`, koristi ta imena umesto `start_date` i `end_date`.
      const payload = {
        name: form.name,
        amount: Number(form.amount),       // ili: limit: Number(form.amount)
        start_date: start,                 // ili: starts_at: start
        end_date: end,                     // ili: ends_at: end
        category_id: form.category_id ? Number(form.category_id) : null,
        notes: form.notes || "",
      };

      await client.post("/budgets", payload);
      setForm({ name: "", amount: "", month: "", category_id: "", notes: "" });
      fetchBudgets(1);
    } catch (e) {
      alert(e?.response?.data?.message || "Kreiranje nije uspelo.");
    } finally {
      setCreating(false);
    }
  }

  // brisanje
  async function handleDelete(id) {
    if (!confirm("Obrisati ovaj budžet?")) return;
    try {
      await client.delete(`/budgets/${id}`);
      const nextPage = meta?.current_page ?? page;
      fetchBudgets(nextPage);
    } catch (e) {
      alert(e?.response?.data?.message || "Brisanje nije uspelo.");
    }
  }

  const catOptions = useMemo(
    () => [{ id: "", name: "Sve kategorije" }, ...cats],
    [cats]
  );

  const canPrev = meta ? meta.current_page > 1 : page > 1;
  const canNext = meta ? meta.current_page < meta.last_page : items.length === PER_PAGE;

  return (
    <>
      <Topbar />
      <main className="container">
        <header className="hero">
          <h1>Budžeti</h1>
          <p className="muted">Definiši mesečne limite po kategorijama i prati potrošnju.</p>
        </header>

        {/* FILTERI */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <form
            onSubmit={onSearch}
            style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr 1fr auto" }}
          >
            <TextInput
              label="Pretraga"
              placeholder="naziv/napomena…"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
            <label>
              <div className="label">Kategorija</div>
              <select
                value={categoryId}
                onChange={(e) => setCategoryId(e.target.value)}
                disabled={catsLoading}
              >
                {catOptions.map((c) => (
                  <option key={c.id ?? "all"} value={c.id ?? ""}>
                    {c.name}
                  </option>
                ))}
              </select>
            </label>
            <TextInput
              label="Mesec"
              type="month"
              value={month}
              onChange={(e) => setMonth(e.target.value)}
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" variant="secondary">Primeni</Button>
            </div>
          </form>
        </section>

        {/* KREIRANJE */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <h3 style={{ marginBottom: 8 }}>Novi budžet</h3>
          <form
            onSubmit={handleCreate}
            style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 160px 160px 1fr 2fr auto" }}
          >
            <TextInput
              label="Naziv"
              value={form.name}
              onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
              required
            />
            <TextInput
              label="Iznos (limit)"
              type="number"
              min="0"
              step="0.01"
              value={form.amount}
              onChange={(e) => setForm((f) => ({ ...f, amount: e.target.value }))}
              required
            />
            <TextInput
              label="Mesec"
              type="month"
              value={form.month}
              onChange={(e) => setForm((f) => ({ ...f, month: e.target.value }))}
              required
            />
            <label>
              <div className="label">Kategorija (opciono)</div>
              <select
                value={form.category_id}
                onChange={(e) => setForm((f) => ({ ...f, category_id: e.target.value }))}
              >
                <option value="">—</option>
                {cats.map((c) => (
                  <option key={c.id} value={c.id}>{c.name}</option>
                ))}
              </select>
            </label>
            <TextInput
              label="Napomena"
              placeholder="npr. mesečni limit za hranu"
              value={form.notes}
              onChange={(e) => setForm((f) => ({ ...f, notes: e.target.value }))}
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" loading={creating}>Sačuvaj</Button>
            </div>
          </form>
        </section>

        {/* LISTA */}
        <section className="panel">
          {error && <div className="alert alert--error">{error}</div>}
          {loading ? (
            <p className="muted">Učitavanje…</p>
          ) : items.length === 0 ? (
            <p className="muted">Nema budžeta.</p>
          ) : (
            <div className="table-wrap">
              <table className="table">
                <thead>
                  <tr>
                    <th style={{ width: 56 }}>#</th>
                    <th>Naziv</th>
                    <th style={{ width: 200 }}>Period</th>
                    <th style={{ width: 180 }}>Kategorija</th>
                    <th className="text-right" style={{ width: 140 }}>Limit</th>
                    <th style={{ width: 120 }}>Akcije</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((b) => (
                    <tr key={b.id}>
                      <td>{b.id}</td>
                      <td>{b.name ?? b.title ?? "(bez naziva)"}</td>
                      <td>
                        {/* pokuša da prikaže po onome što backend vraća */}
                        {b.start_date && b.end_date
                          ? `${b.start_date} → ${b.end_date}`
                          : b.starts_at && b.ends_at
                          ? `${b.starts_at} → ${b.ends_at}`
                          : b.month
                          ? b.month
                          : "—"}
                      </td>
                      <td>{b.category?.name ?? (b.category_id ? `#${b.category_id}` : "—")}</td>
                      <td className="text-right">
                        {Number(b.amount ?? b.limit ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                      </td>
                      <td>
                        <Button variant="danger" onClick={() => handleDelete(b.id)}>Obriši</Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              <div className="pager" style={{ display: "flex", justifyContent: "space-between", marginTop: 12 }}>
                <Button
                  variant="secondary"
                  disabled={!canPrev}
                  onClick={() => canPrev && fetchBudgets((meta?.current_page ?? page) - 1)}
                >
                  ← Prethodna
                </Button>
                <div className="muted">
                  {meta ? `Strana ${meta.current_page} / ${meta.last_page}` : `Strana ${page}`}
                </div>
                <Button
                  variant="secondary"
                  disabled={!canNext}
                  onClick={() => canNext && fetchBudgets((meta?.current_page ?? page) + 1)}
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
