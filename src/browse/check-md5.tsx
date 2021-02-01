import { Source } from "../App";
import { useCallback, useContext, useEffect, useState } from "react";
import { context } from "../context";
import { BarLoader } from "react-spinners";
// @ts-ignore
import ndjsonStream from "can-ndjson-stream";

export default function CheckMD5(props: { source: Source }) {
  const ctx = useContext(context);
  const [loading, setLoading] = useState(false);
  const [folders, setFolders] = useState([] as string[]);
  const [md5, setMD5] = useState("");
  const [error, setError] = useState((null as unknown) as string);

  const fetchData = useCallback(async () => {
    setFolders([]);
    setLoading(true);
    const urlCheck = new URL("SourceScan", ctx.baseUrl);
    urlCheck.searchParams.set("id", props.source.id.toString());
    const res = await fetch(urlCheck.toString());
    if (res.status !== 200) {
      setError(res.statusText);
      setLoading(false);
      return;
    }
    const exampleReader = ndjsonStream(res.body).getReader();

    let result:
      | {
          done: boolean;
          value?: { status: string; file: string[]; md5?: string };
        }
      | undefined;
    while (!result || !result.done) {
      result = await exampleReader.read();
      // console.log(result);
      if (result && result.value) {
        // skip errors (status === 'err')
        if ("file" in result.value && result.value.status === "lines") {
          // @ts-ignore
          setFolders((folders) => folders.concat(...result.value.file));
        }
        if ("md5" in result.value) {
          setMD5(result.value.md5 ?? "");
          setLoading(false);
        }
      }
    }
  }, []);

  useEffect(() => {
    // noinspection JSIgnoredPromiseFromCall
    // fetchData();
  }, [props.source]);

  if (!folders) {
    return <BarLoader loading={true} />;
  }

  return (
    <div className="w-32">
      <button
        className="bg-yellow-300 p-1 rounded"
        onClick={fetchData}
        disabled={loading}
      >
        Rescan
      </button>
      {folders.length ? (
        <div>
          <div>Dir: {folders.length}</div>
          <div>{md5}</div>
        </div>
      ) : null}
      {loading && <BarLoader loading={true} />}
      <BarLoader loading={loading} />
    </div>
  );
}
