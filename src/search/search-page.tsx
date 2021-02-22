import React, { FunctionComponent, useContext } from "react";
import { useQuery, UseQueryResult } from "react-query";
import { Image } from "../model/Image";
import { BounceLoader } from "react-spinners";
import { context } from "../context";
import axios from "redaxios";
import { AxiosError } from "../tailwind";
import { ImageGrid } from "../folders/folder-files";

export default function SearchPage(props: { term: string }) {
	const ctx = useContext(context);
	let {
		isLoading,
		error,
		data,
	}: UseQueryResult<{ files: Record<keyof Image, any>[] }, Response> = useQuery(
		["Search", props.term],
		async () => {
			const url = new URL("Search", ctx.baseUrl);
			url.searchParams.set("term", props.term);
			const res = await axios.get(url.toString());
			return res.data;
		}
	);

	const allImages = () => {
		if (!data) {
			return [];
		}
		return data.files.map(
			(el: any) => new Image({ ...el, baseUrl: ctx.baseUrl })
		);
	};

	const fetchNextPage = () => {};

	const getDeeper = (index: number, image?: Image) => {};

	return (
		<div className="p-2">
			<p>Searching {props.term}</p>
			{isLoading && <BounceLoader />}
			<AxiosError error={error} />
			{data && (
				<ImageGrid
					images={allImages()}
					total={data.files.length}
					fetchNextPage={fetchNextPage}
					isFetchingNextPage={false}
					onClick={(a, b, index, image) => getDeeper(index, image)}
				/>
			)}
			{data && <pre>{JSON.stringify(data, null, 2)}</pre>}
		</div>
	);
}
