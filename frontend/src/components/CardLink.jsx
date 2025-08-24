import { Link } from "react-router-dom";

export default function CardLink({ to, title, desc, emoji }) {
  return (
    <Link to={to} className="card-link">
      <div className="card-emoji">{emoji}</div>
      <div className="card-title">{title}</div>
      <div className="card-desc">{desc}</div>
    </Link>
  );
}
