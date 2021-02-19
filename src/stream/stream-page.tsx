import { Sidebar } from "./sidebar";
import ScaleLoader from "react-spinners/ScaleLoader";
import ImageStream from "./image-stream";
import React from "react";
import { Source } from "../app";

export default function StreamPage(props: {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
}) {
	console.log("StreamPage", props.sources);
	return (
		<div className="flex flex-row p-2">
			<div className="w-2/12">
				<Sidebar
					sources={props.sources}
					sourceID={props.sourceID}
					setSource={props.setSource}
				/>
			</div>
			<div className="flex-grow">
				{props.sources === null ? (
					<ScaleLoader loading={true} color="#4DAF7C" />
				) : (
					<ImageStream sourceID={props.sourceID} />
				)}
			</div>
		</div>
	);
}
