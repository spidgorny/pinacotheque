export function Header(props: {}) {
  return (
    <div
      className="flex flex-row"
      style={{
        background: "#33C3F0",
        color: "white",
        padding: "0.5em",
      }}
    >
      <div className="px-2">
        <a href="/" style={{ color: "white" }}>
          stream
        </a>
      </div>
      <div className="px-2">
        <a href="/browse" style={{ color: "white" }}>
          browse
        </a>
      </div>
      <div className="" />
    </div>
  );
}
