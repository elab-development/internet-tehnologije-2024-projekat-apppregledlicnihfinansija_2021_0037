import { Link, useLocation } from "react-router-dom";

const LABELS = {
  "": "Početna",
  dashboard: "Početna",
  transactions: "Transakcije",
  budgets: "Budžeti",
  categories: "Kategorije",
  "savings-goals": "Ciljevi",
  profile: "Profil",
  alerts: "Notifikacije",
  admin: "Admin",
  reports: "Izveštaji",
};

export default function Breadcrumbs() {
  const { pathname } = useLocation();
  const parts = pathname.replace(/^\//, "").split("/").filter(Boolean);

  const crumbs = [{ to: "/", label: LABELS[""] }];
  let acc = "";
  for (const p of parts) {
    acc += "/" + p;
    crumbs.push({ to: acc, label: LABELS[p] || decodeURIComponent(p) });
  }

  return (
    <nav aria-label="breadcrumb" style={{ marginBottom: 8, fontSize: 13 }}>
      {crumbs.map((c, i) => (
        <span key={c.to}>
          {i > 0 && <span style={{ opacity:.6, margin:"0 6px" }}>›</span>}
          {i < crumbs.length - 1 ? (
            <Link to={c.to} style={{ color:"var(--muted)" }}>{c.label}</Link>
          ) : (
            <span style={{ fontWeight:700 }}>{c.label}</span>
          )}
        </span>
      ))}
    </nav>
  );
}
