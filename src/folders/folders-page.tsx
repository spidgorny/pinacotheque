import {Sidebar} from "../stream/Sidebar";
import React from "react";
import {Source} from "../App";

export default function FoldersPage(props: {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
}) {
	return <div className="flex flex-row p-2">
		<div className="w-2/12">
			<Sidebar
				sources={props.sources}
				sourceID={props.sourceID}
				setSource={props.setSource}
			/>
		</div>
		<div className="flex-grow">
		</div>
	</div>;
}
