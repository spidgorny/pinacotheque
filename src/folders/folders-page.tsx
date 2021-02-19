import { Source } from "../app";
import { useLocation } from "wouter";
import { Sidebar } from "../stream/sidebar";
import React from "react";
import { FolderFiles } from "./folder-files";

export default function FoldersPage(props: {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
	slug: string;
}) {
	const [, setLocation] = useLocation();

	const setSource = (x?: number) => {
		if (!x) {
			setLocation("/folders");
			return;
		}
		setLocation("/folders/" + x.toString());
	};

	let path = props.slug.split("/") as string[];
	let sourceID = path.shift();
	let source = sourceID ? parseInt(sourceID) : undefined;
	console.log(sourceID, source, path);
	return (
		<div className="flex flex-row p-2">
			<div className="w-2/12">
				<Sidebar
					sources={props.sources}
					sourceID={source}
					setSource={setSource}
				/>
			</div>
			{source ? (
				<FolderFiles source={source} path={path} />
			) : (
				<div>Select Source (on the left)</div>
			)}
		</div>
	);
}
