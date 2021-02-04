import React, { useContext } from "react";
import { Source } from "../App";
import { IoReload, MdDoNotDisturbOn } from "react-icons/all";
import { CheckSource } from "./check-source";
import { AppContext, context } from "../context";
import CheckMD5 from "./check-md5";
import ScanDir from "./scan-dir";
import Unscanned from "./unscanned";
import SourceMeta from "./source-meta";

export function NotFound() {
	return <MdDoNotDisturbOn />;
}

export default function OneSourcePage(props: {
	sources: Source[];
	name: string;
	reloadSources: () => void;
}) {
	const source = props.sources.find((el) => el.name === props.name);
	return source ? (
		<SourcePage source={source} reloadSources={props.reloadSources} />
	) : (
		<NotFound />
	);
}

function SourcePage(props: { source: Source; reloadSources: () => void }) {
	const context1 = useContext(context);
	const fakeContext =
		props.source.name === "depidsvy"
			? ({
					baseUrl: new URL("http://localhost:8080/"),
			  } as AppContext)
			: context1;
	return (
		<context.Provider value={fakeContext}>
			<div className="p-2 flex flex-row">
				<div className="flex-grow">
					<pre>{JSON.stringify(props.source, null, 2)}</pre>
				</div>
				<SourceMeta source={props.source} />
				<div>
					<button
						className="bg-blue-300 p-1 rounded"
						onClick={props.reloadSources}
					>
						<IoReload />
					</button>
				</div>
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
				<ScanDir source={props.source} reloadSources={props.reloadSources} />
			</div>
			<hr />
			<div className="p-2">
				<Unscanned source={props.source} />
			</div>
		</context.Provider>
	);
}
