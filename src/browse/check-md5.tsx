import { Source } from "../App";
import { useContext, useEffect, useState } from "react";
import { context } from "../context";
import { BarLoader } from "react-spinners";
// @ts-ignore
import ndjsonStream from "can-ndjson-stream";

export default function CheckMD5(props: { source: Source }) {
  const ctx = useContext(context);
  const [folders, setFolders] = useState([]);
  const [md5, setMD5] = useState("");

  async function fetchData() {
    setFolders([]);
    const urlCheck = new URL("SourceScan", ctx.baseUrl);
    urlCheck.searchParams.set("id", props.source.id.toString());
    const res = await fetch(urlCheck.toString());
    const exampleReader = ndjsonStream(res.body).getReader();

    let result;
    while (!result || !result.done) {
      result = await exampleReader.read();
      console.log(result);
      folders.concat(...result.file);
      if ("md5" in result) {
        setMD5(result.md5);
      }
      setFolders(folders);
    }
  }

  useEffect(() => {
    // noinspection JSIgnoredPromiseFromCall
    fetchData();
  }, [props.source]);

  if (!folders) {
    return <BarLoader loading={true} />;
  }

  return (
    <div>
      <div>Dir: {folders.length}</div>
      <div>{md5}</div>
      <button onClick={fetchData}>Rescan</button>
    </div>
  );
}
