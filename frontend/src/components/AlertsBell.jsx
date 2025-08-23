import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import client from "../api/client";

export default function AlertsBell() {
  const [count, setCount] = useState(0);

  async function fetchUnread() {
    try {
      const { data } = await client.get("/alerts/unread-count");
      setCount(Number(data?.count || 0));
    } catch {}
  }

  useEffect(() => {
    fetchUnread();
    const onChanged = () => fetchUnread();
    window.addEventListener("transactions:changed", onChanged);
    return () => window.removeEventListener("transactions:changed", onChanged);
  }, []);

  return (
    <Link to="/alerts" className="alerts-bell" title="Notifikacije" style={{ position: "relative" }}>
      <span style={{ fontSize: 20 }}>🔔</span>
      {count > 0 && (
        <span
          style={{
            position: "absolute", top: -6, right: -8,
            background: "#e11d48", color: "#fff",
            borderRadius: 999, fontSize: 12, padding: "0 6px",
            lineHeight: "18px", minWidth: 18, textAlign: "center"
          }}
        >
          {count}
        </span>
      )}
    </Link>
  );
}
