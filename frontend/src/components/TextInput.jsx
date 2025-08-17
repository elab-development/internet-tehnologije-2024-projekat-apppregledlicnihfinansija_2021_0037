export default function TextInput({
    label,
    name,
    type = "text",
    value,
    onChange,
    placeholder = "",
    error = "",
    required = false,
    className = "",
    ...props
  }) {
    const id = props.id || name;
  
    return (
      <div className={`field ${className}`}>
        {label && (
          <label className="field__label" htmlFor={id}>
            {label} {required && <span className="req">*</span>}
          </label>
        )}
        <input
          id={id}
          name={name}
          type={type}
          className={`field__input ${error ? "field__input--error" : ""}`}
          value={value}
          onChange={onChange}
          placeholder={placeholder}
          required={required}
          {...props}
        />
        {error && <div className="field__error">{error}</div>}
      </div>
    );
  }
  