import React, { useContext, useState } from "react";
import { useQuery, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { SearchResult } from "./search-page";
import { BounceLoader } from "react-spinners";
import { AxiosError } from "../tailwind";
import { context } from "../context";
import { ImageLightbox } from "../stream/image-lightbox";
import { ImageGrid } from "../folders/image-grid";
import { ShortcutHandler } from "../stream/shortcut-handler";
import { useLocation } from "wouter";

export default function SearchResults(props: { term: string; url: string }) {
	const ctx = useContext(context);
	const [lightbox, setLightbox] = useState(undefined as number | undefined);
	const [, setLocation] = useLocation();

	let tagSearch: UseQueryResult<SearchResult, Response> = useQuery(
		[new URL(props.url).pathname, props.term],
		async () => {
			const res = await axios.get(props.url);
			return res.data;
		}
	);

	const getDeeper = (index: number, image?: Image) => {
		console.log("getDeeper", index);
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

	const upFolder = () => {
		if (tagSearch.data && lightbox) {
			const img = allImages(tagSearch.data)[lightbox];
			setLocation("/folders/" + img.source + "/" + img.dirname);
		}
	};

	return (
		<div>
			<hr />
			{tagSearch.isLoading && (
				<div>
					<BounceLoader />
				</div>
			)}
			{tagSearch.isFetching && (
				<div>
					<BounceLoader />
				</div>
			)}
			<AxiosError error={tagSearch.error} />
			{tagSearch.data && (
				<div>
					<ImageGrid
						images={allImages(tagSearch.data)}
						total={tagSearch.data.files.length}
						fetchNextPage={fetchNextPage}
						isFetchingNextPage={false}
						onClick={(a, b, index, image) => getDeeper(index, image)}
					/>

					<ImageLightbox
						currentIndex={lightbox ?? 0}
						images={allImages(tagSearch.data)}
						onClose={() => setLightbox(undefined)}
						viewerIsOpen={typeof lightbox !== "undefined"}
					/>

					<ShortcutHandler
						keyToPress="ArrowUp"
						modifier="ctrlKey"
						handler={upFolder}
					/>
				</div>
			)}
		</div>
	);
}
