import { Source } from "../App";
import { useEffect, useState } from "react";
import { BarLoader } from "react-spinners";

export default function BrowsePage(props: { sources: Source[] }) {
  console.log(props.sources);
  return (
    <div className="bg-white shadow overflow-hidden sm:rounded-lg mx-3">
      <div className="px-4 py-5">
        <h3 className="text-lg leading-6 font-medium text-gray-900">Sources</h3>
      </div>
      <div className="border-t border-gray-200 divide-gray-100 divide-y">
        {props.sources.map((el) => (
          <SourceItem data={el} />
        ))}
      </div>
    </div>
  );
}

function SourceItem(props: { data: Source }) {
  return (
    <div className="my-3 px-3 py-1 flex flex-row">
      <div>
        <h5 className="">{props.data.name}</h5>
        <div>{props.data.path}</div>
      </div>
      <div>
        <CheckSource source={props.data} />
      </div>
    </div>
  );
}

function CheckSource(props: { source: Source }) {
  const [state, setState] = useState(null);

  useEffect(() => {}, props.source);

  return !state ? <BarLoader /> : <span>OK</span>;
}
