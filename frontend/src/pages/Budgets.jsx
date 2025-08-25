// src/pages/Budgets.jsx
import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import TextInput from "../components/TextInput";
import Button from "../components/Button";
import Breadcrumbs from "../components/Breadcrumbs";

const PER_PAGE = 10;
function splitYearMonth(yyyyMm) {
  if (!yyyyMm) return { year: "", month: "" };
  const [y, m] = yyyyMm.split("-").map(Number);
  return { year: y, month: m };
}


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
  const [q, setQ] = useState("");             
  const [categoryId, setCategoryId] = useState("");
  const [month, setMonth] = useState("");       
  const [page, setPage] = useState(1);

 
const [amountMin, setAmountMin] = useState("");
const [amountMax, setAmountMax] = useState("");
const [sort, setSort] = useState("-created_at");

  // kategorije
  const [cats, setCats] = useState([]);
  const [catsLoading, setCatsLoading] = useState(true);

  // kreiranje
  const [creating, setCreating] = useState(false);
const [form, setForm] = useState({
  amount: "",
  month: "",           
  category_id: "",
  description: "",      
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
    const params = { page: p, per_page: PER_PAGE, sort };
    if (categoryId) params.category_id = Number(categoryId);
    if (month) {
      const { year, month: m } = splitYearMonth(month);
      params.year = year;
      params.month = m;
    }
    if (amountMin) params.amount_min = Number(amountMin);
    if (amountMax) params.amount_max = Number(amountMax);

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
    const { year, month: m } = splitYearMonth(form.month);

    const payload = {
      category_id: Number(form.category_id),   
      amount: Number(form.amount),            
      month: m,                                
      year,                                    
      description: form.description || "",     
    };

    await client.post("/budgets", payload);
    setForm({ amount: "", month: "", category_id: "", description: "" });
    fetchBudgets(1);
  } catch (e) {
    const errs = e?.response?.data?.errors;
    const msg = e?.response?.data?.message || "Kreiranje nije uspelo.";
    alert(
      msg +
        (errs
          ? "\n" +
            Object.entries(errs)
              .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(", ") : v}`)
              .join("\n")
          : "")
    );
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
        <Breadcrumbs />
          <h1>Budžeti</h1>
          <p className="muted">Definiši mesečne limite po kategorijama i prati potrošnju.</p>
        </header>

        {/* FILTERI */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <form
  onSubmit={onSearch}
  style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr 1fr 1fr 1fr auto" }}
>
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

  <TextInput
    label="Iznos min"
    type="number"
    min="0"
    step="0.01"
    value={amountMin}
    onChange={(e) => setAmountMin(e.target.value)}
  />

  <TextInput
    label="Iznos max"
    type="number"
    min="0"
    step="0.01"
    value={amountMax}
    onChange={(e) => setAmountMax(e.target.value)}
  />

  <label>
    <div className="label">Sort</div>
    <select value={sort} onChange={(e) => setSort(e.target.value)}>
      <option value="-created_at">Najnovije</option>
      <option value="created_at">Najstarije</option>
      <option value="-amount">Iznos ↓</option>
      <option value="amount">Iznos ↑</option>
      <option value="year">Godina ↑</option>
      <option value="-year">Godina ↓</option>
      <option value="month">Mesec ↑</option>
      <option value="-month">Mesec ↓</option>
    </select>
  </label>

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
  <div className="label">Kategorija</div>
  <select
    value={form.category_id}
    onChange={(e) => setForm((f) => ({ ...f, category_id: e.target.value }))} 
    required
  >
    <option value="">— izaberi —</option>
    {cats.map((c) => (
      <option key={c.id} value={c.id}>{c.name}</option>
    ))}
  </select>
</label>

<TextInput
  label="Opis (opciono)"
  placeholder="npr. mesečni limit za hranu"
  value={form.description}
  onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
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
                    <th>Opis</th>
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
                      <td>
  <div style={{ fontWeight: 600 }}>
    {b.category?.name ?? (b.category_id ? `#${b.category_id}` : "—")}
  </div>
  {b.description ? (
    <div className="muted" style={{ fontSize: 12 }}>{b.description}</div>
  ) : null}
</td>

                      <td>
                       
  {b.year && b.month
    ? `${b.year}-${String(b.month).padStart(2, "0")}`
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
