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

  const fetchData = async () => {
    setFolders([]);
    setLoading(true);
    setError("");
    const urlCheck = new URL("SourceScan", ctx.baseUrl);
    urlCheck.searchParams.set("id", props.source.id.toString());
    const res = await fetch(urlCheck.toString());
    console.warn(urlCheck.toString(), res.status, res.statusText);
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
  };

  // autostart fetchData() is disabled as this is heavy load on the server
  useEffect(() => {
    // noinspection JSIgnoredPromiseFromCall
    // fetchData();
  }, [props.source]);

  if (!folders) {
    return <BarLoader loading={true} />;
  }

  const loadingProgress = loading ? (
    <div>
      <div>
        Dir: {folders.length}/{props.source.folders}
      </div>
      <progress
        value={folders.length}
        max={props.source.folders}
        className="w-full"
      />
    </div>
  ) : null;

  const afterScan = folders?.length ? (
    <div>
      <div>Dir: {folders?.length ?? props.source.folders}</div>
      <div>
        MD5:{" "}
        <span
          className={md5 === props.source.md5 ? "bg-green-300" : "bg-red-300"}
        >
          {md5}
        </span>
      </div>
    </div>
  ) : null;

  return (
    <div className="">
      <button
        className="bg-yellow-300 p-1 rounded"
        onClick={fetchData}
        disabled={loading}
      >
        Rescan
      </button>
      {loadingProgress}
      {afterScan}
      {error && <div className="bg-red-300">{error}</div>}
    </div>
  );
}
