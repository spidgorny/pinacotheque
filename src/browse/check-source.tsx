import { Source } from "../App";
import { useContext, useEffect, useState } from "react";
import { context } from "../context";
import axios from "redaxios";
import { BarLoader } from "react-spinners";

interface CheckSourceState {
  status: "ok" | "error";
  error?: string;
  files: number;
  folders: number;
}

export function CheckSource(props: { source: Source }) {
  const ctx = useContext(context);
  const [state, setState] = useState(
    (null as unknown) as CheckSourceState | null
  );
  const [error, setError] = useState((null as unknown) as string);

  async function fetchData() {
    setState(null);
    const urlCheck = new URL("CheckSource", ctx.baseUrl);
    urlCheck.searchParams.set("id", props.source.id.toString());
    try {
      const res = await axios.get(urlCheck.toString());
      setState(res.data);
    } catch (e) {
      setError(e.message);
    }
  }

  useEffect(() => {
    // noinspection JSIgnoredPromiseFromCall
    fetchData();
  }, [props.source]);

  if (!state) {
    return <BarLoader loading={true} />;
  }
  if (error && state?.status === "ok") {
    return (
      <button className="bg-red-300 p-1 rounded" onClick={fetchData}>
        {error} {state.error}
      </button>
    );
  }
  return (
    <button className="bg-blue-300 p-1 rounded" onClick={fetchData}>
      Accessible
    </button>
  );
}