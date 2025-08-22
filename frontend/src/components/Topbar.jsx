// src/components/Topbar.jsx
import { Link, useNavigate } from "react-router-dom";
import { useState, useEffect, useRef } from "react";
import { useAuth } from "../context/AuthContext";

export default function Topbar() {
  const [open, setOpen] = useState(false);
  const menuRef = useRef(null);
  const navigate = useNavigate();
  const { logout } = useAuth();


  useEffect(() => {
    const onDocClick = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) setOpen(false);
    };
    const onEsc = (e) => e.key === "Escape" && setOpen(false);
    document.addEventListener("mousedown", onDocClick);
    document.addEventListener("keydown", onEsc);
    return () => {
      document.removeEventListener("mousedown", onDocClick);
      document.removeEventListener("keydown", onEsc);
    };
  }, []);

  async function handleLogout() {
    await logout();
    navigate("/login", { replace: true });
  }

  const bar = { display: "block", height: 2, width: 18, background: "#111", margin: "3px 0", borderRadius: 2 };

  return (
    <header style={{
      display: "flex", alignItems: "center", justifyContent: "space-between",
      padding: "12px 16px", borderBottom: "1px solid #eee", position: "sticky", top: 0, background: "#fff", zIndex: 10
    }}>
      <Link to="/dashboard" style={{ fontWeight: 700, textDecoration: "none", color: "#111" }}>💸 Lične finansije</Link>
 
      <nav style={{ display: "flex", gap: 12 }}>
        <Link to="/transactions">Transakcije</Link>
        <Link to="/budgets">Budžeti</Link>
        <Link to="/categories">Kategorije</Link>
        <Link to="/savings-goals">Ciljevi</Link>
      </nav>

      <div ref={menuRef} style={{ position: "relative" }}>
        <button
          aria-label="Meni"
          onClick={() => setOpen((v) => !v)}
          style={{ padding: 8, borderRadius: 8, border: "1px solid #ddd", background: "#fff" }}
        >
          <span style={bar} /><span style={bar} /><span style={bar} />
        </button>

        {open && (
          <div style={{
            position: "absolute", right: 0, top: "calc(100% + 8px)",
            background: "#fff", border: "1px solid #ddd", borderRadius: 8, minWidth: 180,
            boxShadow: "0 6px 24px rgba(0,0,0,.08)", padding: 8
          }}>
            <Link to="/profile" onClick={() => setOpen(false)} style={itemStyle}>Moj profil</Link>
            <Link to="/reset-password" onClick={() => setOpen(false)} style={itemStyle}>Reset lozinke</Link>
            <button onClick={handleLogout} style={{ ...itemStyle, width: "100%", textAlign: "left", background: "none", border: 0, cursor: "pointer" }}>
              Odjava
            </button>
          </div>
        )}
      </div>
    </header>
  );
}

const itemStyle = {
  display: "block",
  padding: "8px 10px",
  borderRadius: 6,
  textDecoration: "none",
  color: "#111",
  fontSize: 14
};

