import React from "react";
import { Source } from "../App";
import { IoReload } from "react-icons/all";
import { CheckSource } from "./check-source";

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
					<SourceItem source={el} key={el.id} />
				))}
			</div>
		</div>
	);
}

function SourceItem(props: { source: Source }) {
	return (
		<div className="my-3 px-3 py-1 flex flex-row">
			<div className="flex-grow">
				<h5 className="">
					<a href={`/browse/${props.source.name}`}>{props.source.name}</a>
					<span className="text-sm bg-blue-300 align-top px-1 mx-1">
						{props.source.id}
					</span>
				</h5>
				<div>
					{props.source.path} [{props.source.folders}/{props.source.files}]
				</div>
				<div>{props.source.md5}</div>
			</div>
			<div className="mx-2">
				<CheckSource source={props.source} />
			</div>
		</div>
	);
}
