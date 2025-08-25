import { useMemo, useCallback } from "react";
import { useSearchParams } from "react-router-dom";

export default function useQueryParams(initial = {}) {
  const [sp, setSp] = useSearchParams();

  const params = useMemo(() => {
    const obj = Object.fromEntries(sp.entries());
    return { ...initial, ...obj };
  }, [sp, initial]);

  const setParam = useCallback((key, value, opts={replace:true}) => {
    const next = new URLSearchParams(sp);
    if (value === undefined || value === null || value === "") {
      next.delete(key);
    } else {
      next.set(key, String(value));
    }
    setSp(next, opts);
  }, [sp, setSp]);

  return [params, setParam];
}
