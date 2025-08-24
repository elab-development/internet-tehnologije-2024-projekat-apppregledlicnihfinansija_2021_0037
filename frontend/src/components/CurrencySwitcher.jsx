import { useEffect, useState } from "react";
import client from "../api/client";

const BASIC = [
  { code: "RSD", name: "Serbian Dinar" },
  { code: "EUR", name: "Euro" },
  { code: "USD", name: "US Dollar" },
  { code: "GBP", name: "British Pound" },
  { code: "CHF", name: "Swiss Franc" },
];

export default function CurrencySwitcher({ value, onChange }) {
  const [list, setList] = useState(BASIC);

  useEffect(() => {
    let mounted = true;
    client.get("/rates/currencies")
      .then(({ data }) => {
        if (!mounted) return;
        const arr = Array.isArray(data) ? data.slice() : [];
        if (arr.length) {
          // RSD gore, ostalo abecedno
          const rsd = arr.find(x => x.code === "RSD");
          const rest = arr.filter(x => x.code !== "RSD").sort((a,b) => a.code.localeCompare(b.code));
          setList([rsd || BASIC[0], ...rest]);
        } else {
          setList(BASIC);
        }
      })
      .catch((e) => {
        console.error("currencies failed", e);
        setList(BASIC);
      });
    return () => { mounted = false; };
  }, []);

  return (
    <label>
      <div className="label">Valuta</div>
      <select value={value} onChange={(e) => onChange(e.target.value)}>
        {list.map((c) => (
          <option key={c.code} value={c.code}>
            {c.code} — {c.name}
          </option>
        ))}
      </select>
    </label>
  );
}
