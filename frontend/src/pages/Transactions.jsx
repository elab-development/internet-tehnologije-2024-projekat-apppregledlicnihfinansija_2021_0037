// src/pages/Transactions.jsx
import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import TextInput from "../components/TextInput";
import Button from "../components/Button";
import { useAuth } from "../context/AuthContext";
import CurrencySwitcher from "../components/CurrencySwitcher";


const PER_PAGE = 10;
const SORT_OPTIONS = [
  { value: "-date", label: "Datum ↓ (novije prvo)" },
  { value: "date", label: "Datum ↑ (starije prvo)" },
  { value: "-amount", label: "Iznos ↓" },
  { value: "amount", label: "Iznos ↑" },
];



function fmt(n) {
  if (n == null) return "—";
  const num = Number(n);
  if (Number.isNaN(num)) return String(n);
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export default function Transactions() {
  // lista + meta
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const { isPremium, refreshMe } = useAuth();

  // filteri
  const [q, setQ] = useState(""); // pretraga po opisu (backend: optional)
  const [type, setType] = useState("all"); // all | income | expense
  const [categoryId, setCategoryId] = useState("");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");
  const [sort, setSort] = useState("-date");
  const [page, setPage] = useState(1);

  // kategorije (za select)
  const [cats, setCats] = useState([]);
  const [catsLoading, setCatsLoading] = useState(true);

  // kreiranje
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({
    type: "expense",
    amount: "",
    date: "",
    category_id: "",
    description: "",
  });

  //valute 
  const [currency, setCurrency] = useState(() => localStorage.getItem("currency") || "RSD");
  const [fxRate, setFxRate] = useState(1);
  function onCurrencyChange(cur) {
    setCurrency(cur);
    localStorage.setItem("currency", cur);
  }


  const [role, setRole] = useState("user");
  const canPdf = useMemo(() => role === "premium" || role === "admin", [role]);
  useEffect(() => {
    let canceled = false;

    async function loadRate() {
      // ako je RSD, kurs je 1
      if (currency === "RSD") {
        if (!canceled) setFxRate(1);
        return;
      }

      // 1) pokušaj: 1 RSD -> {currency}
      try {
        const { data } = await client.get("/rates/convert", {
          params: { amount: 1, from: "RSD", to: currency },
        });
        const rate = Number(data?.rate ?? data?.result);
        if (!canceled && rate && isFinite(rate) && rate > 0) {
          setFxRate(rate);
          return;
        }
      } catch (e) {
        console.debug("convert RSD->", currency, "failed:", e);
      }

      // 2) invert: 1 {currency} -> RSD, pa recipročna vrednost
      try {
        const { data } = await client.get("/rates/convert", {
          params: { amount: 1, from: currency, to: "RSD" },
        });
        const inv = Number(data?.rate ?? data?.result);
        const rate = inv ? 1 / inv : NaN;
        if (!canceled && rate && isFinite(rate) && rate > 0) {
          setFxRate(rate);
          return;
        }
      } catch (e) {
        console.debug("convert", currency, "-> RSD failed:", e);
      }

      // 3) fallback
      if (!canceled) setFxRate(1);
    }

    loadRate();
    return () => { canceled = true; };
  }, [currency]);



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

  // dohvat transakcija
  async function fetchTransactions(p = 1) {
    setLoading(true);
    setError("");
    try {
      const params = {
        page: p,
        per_page: PER_PAGE,
        sort,
      };
      if (q.trim()) params.q = q.trim();          // ako si implementirala pretragu po opisu
      if (type !== "all") params.type = type;
      if (categoryId) params.category_id = categoryId;
      if (from) params.from = from;
      if (to) params.to = to;

      const { data } = await client.get("/transactions", { params });
      const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setItems(list);
      setMeta(data?.meta ?? null);
      setPage(data?.meta?.current_page ?? p);
    } catch (e) {
      setError(e?.response?.data?.message || "Greška pri učitavanju transakcija.");
      setItems([]);
      setMeta(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchCategories();
    fetchTransactions(1);

    client.get("/user")
      .then(({ data }) => setRole(data?.data?.role ?? data?.role ?? "user"))
      .catch(() => setRole("user"));

  }, []);

  // submit filtera
  function onSearch(e) {
    e?.preventDefault?.();
    fetchTransactions(1);
  }

  // dodavanje nove transakcije
  async function handleCreate(e) {
    e.preventDefault();
    setCreating(true);
    try {
      const payload = {
        type: form.type,
        amount: Number(form.amount),
        date: form.date,
        category_id: form.category_id ? Number(form.category_id) : null,
        description: form.description || "",
      };
      await client.post("/transactions", payload);
      await refreshMe();
      window.dispatchEvent(new Event("transactions:changed"));
      setForm({ type: "expense", amount: "", date: "", category_id: "", description: "" });
      fetchTransactions(1);
    } catch (e) {
      alert(e?.response?.data?.message || "Kreiranje nije uspelo.");
    } finally {
      setCreating(false);
    }
  }

  // brisanje
  async function handleDelete(id) {
    if (!confirm("Obrisati ovu transakciju?")) return;
    try {
      await client.delete(`/transactions/${id}`);
      window.dispatchEvent(new Event("transactions:changed"))
      const nextPage = meta?.current_page ?? page;
      fetchTransactions(nextPage);
    } catch (e) {
      alert(e?.response?.data?.message || "Brisanje nije uspelo.");
    }
  }

  // eksport CSV/PDF (sa Bearer headerom)
  async function exportFile(fmt) {
    try {
      const token = localStorage.getItem("token");
      const url = `${client.defaults.baseURL}/transactions/export?format=${fmt}`;
      const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } });
      if (!res.ok) throw new Error(`Export failed: ${res.status}`);
      const blob = await res.blob();
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = `transactions_${Date.now()}.${fmt}`;
      a.click();
      URL.revokeObjectURL(a.href);
    } catch (e) {
      alert("Eksport nije uspeo. Proveri da li si ulogovan/a.");
    }
  }

  const canPrev = meta ? meta.current_page > 1 : page > 1;
  const canNext = meta ? meta.current_page < meta.last_page : items.length === PER_PAGE;

  const catOptions = useMemo(
    () => [{ id: "", name: "Sve kategorije" }, ...cats],
    [cats]
  );

  return (
    <>
      <Topbar />
      <main className="container">
        <header className="hero">
          <h1>Transakcije</h1>
          <p className="muted">Pregled, filtriranje, dodavanje i eksport podataka.</p>
        </header>

        {/* FILTERI */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <form
            onSubmit={onSearch}
            style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 160px 1fr 1fr 1fr 1fr auto" }}
          >
            <TextInput
              label="Pretraga"
              placeholder="opis…"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
            <label>
              <div className="label">Tip</div>
              <select value={type} onChange={(e) => setType(e.target.value)}>
                <option value="all">Svi</option>
                <option value="expense">Trošak</option>
                <option value="income">Prihod</option>
              </select>
            </label>
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
            <TextInput label="Od datuma" type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
            <TextInput label="Do datuma" type="date" value={to} onChange={(e) => setTo(e.target.value)} />
            <label>
              <div className="label">Sort</div>
              <select value={sort} onChange={(e) => setSort(e.target.value)}>
                {SORT_OPTIONS.map((o) => (
                  <option key={o.value} value={o.value}>{o.label}</option>
                ))}
              </select>
            </label>
            <CurrencySwitcher value={currency} onChange={onCurrencyChange} />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" variant="secondary">Primeni</Button>
            </div>
          </form>
        </section>

        {/* KREIRANJE */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <h3 style={{ marginBottom: 8 }}>Nova transakcija</h3>
          <form
            onSubmit={handleCreate}
            style={{ display: "grid", gap: 12, gridTemplateColumns: "120px 1fr 1fr 1fr 2fr auto" }}
          >
            <label>
              <div className="label">Tip</div>
              <select value={form.type} onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}>
                <option value="expense">Trošak</option>
                <option value="income">Prihod</option>
              </select>
            </label>
            <TextInput
              label="Iznos"
              type="number"
              min="0"
              step="0.01"
              required
              value={form.amount}
              onChange={(e) => setForm((f) => ({ ...f, amount: e.target.value }))}
            />
            <TextInput
              label="Datum"
              type="date"
              required
              value={form.date}
              onChange={(e) => setForm((f) => ({ ...f, date: e.target.value }))}
            />
            <label>
              <div className="label">Kategorija</div>
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
              label="Opis"
              placeholder="npr. Namirnice"
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
          <div style={{ display: "flex", gap: 8, justifyContent: "flex-end", marginBottom: 8 }}>
            <Button variant="secondary" onClick={() => exportFile("csv")}>⬇️ Export CSV</Button>
            {canPdf ? (
              <Button variant="secondary" onClick={() => exportFile("pdf")}>⬇️ Export PDF</Button>
            ) : (
              <span className="muted" style={{ alignSelf: "center" }}>
                PDF eksport je dostupan samo Premium korisnicima.
              </span>
            )}
          </div>



          {error && <div className="alert alert--error">{error}</div>}
          {loading ? (
            <p className="muted">Učitavanje…</p>
          ) : items.length === 0 ? (
            <p className="muted">Nema podataka.</p>
          ) : (
            <div className="table-wrap">
              <table className="table">
                <thead>
                  <tr>
                    <th style={{ width: 56 }}>#</th>
                    <th style={{ width: 110 }}>Datum</th>
                    <th style={{ width: 110 }}>Tip</th>
                    <th style={{ width: 180 }}>Kategorija</th>
                    <th>Opis</th>
                    <th className="text-right" style={{ width: 140 }}>Iznos</th>
                    <th style={{ width: 120 }}>Akcije</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((t) => (
                    <tr key={t.id}>
                      <td>{t.id}</td>
                      <td>{t.date ?? t.created_at?.slice(0, 10) ?? "—"}</td>
                      <td>
                        <span className={`type ${t.type}`}>
                          {t.type === "income" ? "Prihod" : t.type === "expense" ? "Trošak" : t.type}
                        </span>
                      </td>
                      <td>{t.category?.name ?? (t.category_id ? `#${t.category_id}` : "—")}</td>
                      <td className="muted">{t.description || "—"}</td>
                      <td className="text-right">
                        {fmt(t.amount)}
                        {currency !== "RSD" && (
                          <div className="muted">
                            ≈ {(Number(t.amount) * fxRate).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} {currency}
                          </div>
                        )}
                      </td>

                      <td>
                        <Button variant="danger" onClick={() => handleDelete(t.id)}>Obriši</Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              <div className="pager" style={{ display: "flex", justifyContent: "space-between", marginTop: 12 }}>
                <Button
                  variant="secondary"
                  disabled={!canPrev}
                  onClick={() => canPrev && fetchTransactions((meta?.current_page ?? page) - 1)}
                >
                  ← Prethodna
                </Button>
                <div className="muted">
                  {meta ? `Strana ${meta.current_page} / ${meta.last_page}` : `Strana ${page}`}
                </div>
                <Button
                  variant="secondary"
                  disabled={!canNext}
                  onClick={() => canNext && fetchTransactions((meta?.current_page ?? page) + 1)}
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
