import React, { useContext, useState } from "react";
import { useQuery, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { SearchResult } from "./search-page";
import { BounceLoader } from "react-spinners";
import { AxiosError } from "../tailwind";
import { ImageGrid } from "../folders/folder-files";
import { context } from "../context";
import * as url from "url";

export default function SearchResults(props: { term: string; url: string }) {
	const ctx = useContext(context);
	const [lightbox, setLightbox] = useState(undefined as number | undefined);
	let tagSearch: UseQueryResult<SearchResult, Response> = useQuery(
		[new URL(props.url).pathname, props.term],
		async () => {
			const res = await axios.get(props.url);
			return res.data;
		}
	);

	const getDeeper = (index: number, image?: Image) => {
		setLightbox(index);
	};

	const allImages = (from: SearchResult) => {
		if (!from) {
			return [];
		}
		return from.files.map(
			(el: any) => new Image({ ...el, baseUrl: ctx.baseUrl })
		);
	};

	const fetchNextPage = () => {};

	return (
		<div>
			<hr />
			{tagSearch.isLoading && <BounceLoader />}
			<AxiosError error={tagSearch.error} />
			{tagSearch.data && (
				<ImageGrid
					images={allImages(tagSearch.data)}
					total={tagSearch.data.files.length}
					fetchNextPage={fetchNextPage}
					isFetchingNextPage={false}
					onClick={(a, b, index, image) => getDeeper(index, image)}
				/>
			)}
		</div>
	);
}
