// src/pages/Categories.jsx
import { useEffect, useState } from "react";
import Topbar from "../components/Topbar";
import client from "../api/client";
import TextInput from "../components/TextInput";
import Button from "../components/Button";
import Breadcrumbs from "../components/Breadcrumbs";

const PER_PAGE = 10;

// tolerantno mapiranje mogućih polja iz backenda
function pickCategoryView(c) {
  const name = c.name ?? c.title ?? c.label ?? `Kategorija #${c.id}`;
  const description = c.description ?? c.desc ?? "";
  const txCount = c.transactions_count ?? c.tx_count ?? c.count ?? null;
  return { id: c.id, name, description, txCount, raw: c };
}

export default function Categories() {
  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [q, setQ] = useState("");
  const [page, setPage] = useState(1);

  // create form
  const [createName, setCreateName] = useState("");
  const [createDesc, setCreateDesc] = useState("");
  const [creating, setCreating] = useState(false);

  async function fetchCategories(p = 1, query = q) {
    setLoading(true);
    setError("");
    try {
      const { data } = await client.get("/categories", {
        params: { page: p, per_page: PER_PAGE, q: query || undefined },
      });
      const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setItems(list);
      setMeta(data?.meta ?? null);
      setPage(data?.meta?.current_page ?? p);
    } catch (e) {
      setError(e?.response?.data?.message || "Greška pri čitanju kategorija.");
      setItems([]);
      setMeta(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchCategories(1, q);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function onSearch(e) {
    e.preventDefault();
    fetchCategories(1, q);
  }

  async function handleCreate(e) {
    e.preventDefault();
    if (!createName.trim()) return;
    setCreating(true);
    try {
      await client.post("/categories", {
        name: createName.trim(),
        description: createDesc.trim() || undefined,
      });
      setCreateName("");
      setCreateDesc("");
      await fetchCategories(1, q);
    } catch (e) {
      alert(e?.response?.data?.message || "Kreiranje nije uspelo.");
    } finally {
      setCreating(false);
    }
  }

  async function handleDelete(id) {
    if (!confirm("Obrisati ovu kategoriju?")) return;
    try {
      await client.delete(`/categories/${id}`);
      fetchCategories(page, q);
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
        <Breadcrumbs />
          <h1>Kategorije</h1>
          <p className="muted">Upravljaj listom kategorija: pretraga, dodavanje i brisanje.</p>
        </header>

        {/* Pretraga */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <form onSubmit={onSearch} style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr auto" }}>
            <TextInput
              label="Pretraga"
              placeholder="npr. 'hrana'…"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" variant="secondary">Pretraži</Button>
            </div>
          </form>
        </section>

        {/* Dodavanje */}
        <section className="panel" style={{ marginBottom: 16 }}>
          <h3 style={{ marginBottom: 8 }}>Nova kategorija</h3>
          <form onSubmit={handleCreate} style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 2fr auto" }}>
            <TextInput
              label="Naziv"
              value={createName}
              onChange={(e) => setCreateName(e.target.value)}
              placeholder="npr. Hrana"
              required
            />
            <TextInput
              label="Opis (opciono)"
              value={createDesc}
              onChange={(e) => setCreateDesc(e.target.value)}
              placeholder="kratak opis…"
            />
            <div style={{ alignSelf: "end" }}>
              <Button type="submit" loading={creating}>Dodaj</Button>
            </div>
          </form>
        </section>

        {/* Lista */}
        <section className="panel">
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
                    <th>Naziv</th>
                    <th>Opis</th>
                    <th className="text-right" style={{ width: 140 }}># Transakcija</th>
                    <th style={{ width: 120 }}>Akcije</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((c) => {
                    const v = pickCategoryView(c);
                    return (
                      <tr key={v.id}>
                        <td>{v.id}</td>
                        <td>{v.name}</td>
                        <td>{v.description || "—"}</td>
                        <td className="text-right">{v.txCount ?? "—"}</td>
                        <td>
                          <div style={{ display: "flex", gap: 8 }}>
                            <Button variant="danger" onClick={() => handleDelete(v.id)}>
                              Obriši
                            </Button>
                          </div>
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
                  onClick={() => canPrev && fetchCategories((meta?.current_page ?? page) - 1, q)}
                >
                  ← Prethodna
                </Button>
                <div className="muted">
                  {meta ? `Strana ${meta.current_page} / ${meta.last_page}` : `Strana ${page}`}
                </div>
                <Button
                  variant="secondary"
                  disabled={!canNext}
                  onClick={() => canNext && fetchCategories((meta?.current_page ?? page) + 1, q)}
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
