import { Link } from "wouter";
import React from "react";

export function FoldersHeader(props: {
	source: number;
	path: string[];
	isLoading: boolean;
	isFetching: boolean;
	error: null | Response;
	pages: number;
	dataLength: number;
	rows: number;
}) {
	return (
		<div
			className="flex flex-row justify-between bg-blue-300 p-1"
			style={{ position: "sticky", top: 0, zIndex: 100 }}
		>
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
						<React.Fragment key={index}>
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
						</React.Fragment>
					);
				})}
			</div>
			<div className={props.isLoading ? "" : "text-gray-300 text-opacity-50"}>
				isLoading
			</div>
			<div className={props.isFetching ? "" : "text-gray-300 text-opacity-50"}>
				isFetching
			</div>
			<div className={props.error ? "" : "text-gray-300 text-opacity-50"}>
				isError
			</div>
			<div>Pages: {props.pages}</div>
			<div>
				Photos: {props.dataLength}/{props.rows}
			</div>
		</div>
	);
}
