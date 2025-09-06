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
  const parts = pathname.replace(/^\/+/, "").split("/").filter(Boolean);

  const crumbs = [{ to: "/", label: LABELS[""] }];
  let acc = "";
  for (const p of parts) {
    acc += `/${p}`;
    crumbs.push({ to: acc, label: LABELS[p] ?? decodeURIComponent(p) });
  }

  return (
    <nav aria-label="breadcrumb" className="breadcrumbs" style={{ marginBottom: 8, fontSize: 13 }}>
      {crumbs.map((c, i) => {
        const isLast = i === crumbs.length - 1;
        return (
          <span key={c.to}>
            {isLast ? (
              <span style={{ fontWeight: 700 }}>{c.label}</span>
            ) : (
              <>
                <Link to={c.to} style={{ color: "var(--muted, #6b7280)" }}>{c.label}</Link>
                <span style={{ margin: "0 6px", color: "#bbb" }}>/</span>
              </>
            )}
          </span>
        );
      })}
    </nav>
  );
}
