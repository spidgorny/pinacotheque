import { Link } from "wouter";
import React from "react";

export function FoldersHeader(props: {
	source: number;
	path: string[];
	isLoading: boolean;
	error: null | string;
	dataLength: number;
}) {
	return (
		<div className="flex flex-row justify-between">
			<div>
				Source:{" "}
				<Link
					className="underline text-blue-600 hover:text-blue-800 visited:text-purple-600"
					to={"/folders/" + props.source}
				>
					[{props.source}]
				</Link>
			</div>
			<div>
				Path:{" "}
				{props.path.map((el: string, index: number) => {
					return (
						<>
							{"/"}
							<Link
								className="underline text-blue-600 hover:text-blue-800 visited:text-purple-600"
								to={
									"/folders/" +
									props.source +
									"/" +
									props.path.slice(0, index + 1).join("/")
								}
							>
								{decodeURIComponent(el)}
							</Link>
						</>
					);
				})}
			</div>
			<div className={props.isLoading ? "" : "text-gray-300 text-opacity-50"}>
				isLoading
			</div>
			<div className={props.error ? "" : "text-gray-300 text-opacity-50"}>
				isError
			</div>
			<div>Photos: {props.dataLength}</div>
		</div>
	);
}
