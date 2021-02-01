import React from "react";
import { Source } from "../App";
import { MdDoNotDisturbOn } from "react-icons/all";
import { CheckSource } from "./check-source";
import { AppContext, context } from "../context";
import CheckMD5 from "./check-md5";
import ScanDir from "./scan-dir";

export function NotFound() {
  return <MdDoNotDisturbOn />;
}

export default function OneSourcePage(props: {
  sources: Source[];
  name: string;
}) {
  const source = props.sources.find((el) => el.name === props.name);
  return source ? <SourcePage source={source} /> : <NotFound />;
}

function SourcePage(props: { source: Source }) {
  const fakeContext = {
    baseUrl: new URL("http://localhost:8080/"),
  } as AppContext;
  return (
    <context.Provider value={fakeContext}>
      <div className="p-2">
        <pre>{JSON.stringify(props.source, null, 2)}</pre>
      </div>
      <hr />
      <div className="p-2">
        <CheckSource source={props.source} />
      </div>
      <hr />
      <div className="p-2">
        <CheckMD5 source={props.source} />
      </div>
      <hr />
      <div className="p-2">
        <ScanDir source={props.source} />
      </div>
    </context.Provider>
  );
}
