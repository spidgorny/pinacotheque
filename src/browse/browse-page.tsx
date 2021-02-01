import { Source } from "../App";
import { useContext, useEffect, useState } from "react";
import { BarLoader } from "react-spinners";
import axios from "redaxios";
import { context } from "../context";
import CheckMD5 from "./check-md5";
import { IoReload } from "react-icons/all";

export default function BrowsePage(props: {
  sources: Source[];
  reloadSources: () => void;
}) {
  console.log(props.sources);
  return (
    <div className="bg-white shadow overflow-hidden sm:rounded-lg mx-3">
      <div className="px-4 py-5 flex flex-row">
        <h3 className="text-lg leading-6 font-medium text-gray-900 flex-grow">
          Sources
        </h3>
        <button
          className="bg-blue-300 p-1 rounded"
          onClick={props.reloadSources}
        >
          <IoReload />
        </button>
      </div>
      <div className="border-t border-gray-200 divide-gray-100 divide-y">
        {props.sources.map((el: Source) => (
          <SourceItem data={el} key={el.id} />
        ))}
      </div>
    </div>
  );
}

function SourceItem(props: { data: Source }) {
  return (
    <div className="my-3 px-3 py-1 flex flex-row">
      <div className="flex-grow">
        <h5 className="">
          {props.data.name}
          <span className="text-sm bg-blue-300 align-top px-1 mx-1">
            {props.data.id}
          </span>
        </h5>
        <div>
          {props.data.path} [{props.data.folders}/{props.data.files}]
        </div>
        <div>{props.data.md5}</div>
      </div>
      <CheckSource source={props.data} />
    </div>
  );
}

interface CheckSourceState {
  status: "ok" | "error";
  error?: string;
  files: number;
  folders: number;
}

function CheckSource(props: { source: Source }) {
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
    <>
      <div className="mx-2">
        <button className="bg-blue-300 p-1 rounded" onClick={fetchData}>
          OK
        </button>
      </div>
      <div className="mx-2 w-24">
        <div>Files: {state.files}</div>
        <div>Folders: {state.folders}</div>
      </div>
      <div className="mx-2">
        <CheckMD5 source={props.source} />
      </div>
    </>
  );
}
