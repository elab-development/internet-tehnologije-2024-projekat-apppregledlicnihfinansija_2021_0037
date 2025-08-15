import { useNavigate, Link } from "react-router-dom";
import client from "../api/client";

export default function Topbar() {
  const nav = useNavigate();

  async function logout() {
    try { await client.post("/auth/logout"); } catch {}
    localStorage.removeItem("token");
    nav("/login");
  }

  return (
    <header className="topbar">
      <Link to="/" className="brand">Lične finansije</Link>
      <nav className="menu">
        <Link to="/transactions">Transakcije</Link>
        <Link to="/budgets">Budžeti</Link>
        <Link to="/categories">Kategorije</Link>
        <Link to="/savings-goals">Ciljevi štednje</Link>
        <button onClick={logout} className="logout">Odjavi se</button>
      </nav>
    </header>
  );
}
