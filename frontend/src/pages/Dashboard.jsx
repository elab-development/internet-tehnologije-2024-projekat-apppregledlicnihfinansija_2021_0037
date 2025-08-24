import { useEffect, useState } from "react";
import client from "../api/client";
import Topbar from "../components/Topbar";
import CardLink from "../components/Cardlink";

export default function Dashboard() {
  const [recent, setRecent] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    client
      .get("/transactions", { params: { per_page: 5, sort: "-date" } })
      .then(({ data }) => setRecent(data.data || []))
      .catch(() => setRecent([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      <Topbar />
      <main className="container">
        <section className="hero">
          <h1>Dobro došli nazad </h1>
          <p className="muted">
            Upravljajte svojim transakcijama, budžetima, kategorijama i ciljevima štednje na jednom mestu.
          </p>
        </section>

        <section className="grid">
          <CardLink
            to="/transactions"
            title="Transakcije"
            desc="Pregledaj, filtriraj i pretražuj sve transakcije"
            emoji="📒"
          />
          <CardLink
            to="/budgets"
            title="Budžeti"
            desc="Mesečni/nedeljni budžeti"
            emoji="🧮"
          />
          <CardLink
            to="/categories"
            title="Kategorije"
            desc="Upravljaj kategorijama troškova"
            emoji="🏷️"
          />
          <CardLink
            to="/savings-goals"
            title="Ciljevi štednje"
            desc="Prati svoje ciljeve"
            emoji="🎯"
          />
        </section>

        <section className="panel">
          <h2>Najnovije transakcije</h2>
          {loading ? (
            <p className="muted">Učitavanje…</p>
          ) : recent.length === 0 ? (
            <p className="muted">Još nema podataka.</p>
          ) : (
            <ul className="list">
              {recent.map((t) => (
                <li key={t.id} className="row">
                  <span className={`type ${t.type}`}>{t.type}</span>
                  <span className="amount">{t.amount}</span>
                  <span className="date">{t.date}</span>
                  <span className="cat">{t.category?.name || `#${t.category_id}`}</span>
                </li>
              ))}
            </ul>
          )}
        </section>
      </main>
    </>
  );
}
