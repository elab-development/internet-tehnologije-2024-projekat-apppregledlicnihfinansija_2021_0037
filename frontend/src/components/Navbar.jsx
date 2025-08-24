import { Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

export default function Navbar() {
  const { logout } = useAuth();
  return (
    <div style={{ display: "flex", gap: 12, padding: 12, borderBottom: "1px solid #ddd" }}>
      <Link to="/">Dashboard</Link>
      <Link to="/transactions">Transakcije</Link>
      <button onClick={logout} style={{ marginLeft: "auto" }}>Logout</button>
    </div>
  );
}
