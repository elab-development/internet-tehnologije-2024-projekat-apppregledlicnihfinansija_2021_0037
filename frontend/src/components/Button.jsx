export default function Button({
    children,
    type = "button",
    variant = "primary", 
    size = "md",         
    loading = false,
    disabled = false,
    className = "",
    ...props
  }) {
    const base = "btn";
    const variants = {
      primary: "btn--primary",
      secondary: "btn--secondary",
      danger: "btn--danger",
      ghost: "btn--ghost",
    };
    const sizes = { sm: "btn--sm", md: "btn--md", lg: "btn--lg" };
  
    return (
      <button
        type={type}
        disabled={disabled || loading}
        className={`${base} ${variants[variant]} ${sizes[size]} ${className}`}
        {...props}
      >
        {loading ? "Loading…" : children}
      </button>
    );
  }
  