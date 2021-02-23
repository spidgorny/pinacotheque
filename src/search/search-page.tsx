import React, { useContext } from "react";
import { Image } from "../model/Image";
import { context } from "../context";
import SearchResults from "./search-results";

export interface SearchResult {
	files: Record<keyof Image, any>[];
}

export default function SearchPage(props: { term: string }) {
	const ctx = useContext(context);
	let searchTags = new URL("SearchTags", ctx.baseUrl);
	searchTags.searchParams.set("term", props.term);
	let searchPath = new URL("SearchPath", ctx.baseUrl);
	searchPath.searchParams.set("term", props.term);

	return (
		<div className="p-2">
			<p>Searching {props.term}</p>
			<SearchResults term={props.term} url={searchTags.toString()} />
			<SearchResults term={props.term} url={searchPath.toString()} />
		</div>
	);
}
