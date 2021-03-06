import React, { useContext, useState } from "react";
import { useLocation } from "wouter";
import {
	QueryFunctionContext,
	useInfiniteQuery,
	UseInfiniteQueryResult,
} from "react-query";
import axios from "redaxios";
import { context } from "../context";
import { Image } from "../model/Image";
import { BarLoader, GridLoader } from "react-spinners";
import { FoldersHeader } from "./folders-header";
import { QueryKey } from "react-query/types/core/types";
import { AxiosError } from "../tailwind";
import { ImageLightbox } from "../stream/image-lightbox";
import { ShortcutHandler } from "../stream/shortcut-handler";
import { ImageGrid } from "./image-grid";

interface FolderResponse {
	status: string;
	path: string;
	offset: number;
	folder: Record<keyof Image, any>;
	query: string;
	rows: number;
	countQuery: string;
	data: Record<keyof Image, any>[];
	nextOffset: number;
	duration: number;
}

interface QueryPage extends FolderResponse {
	images: Image[];
}

export function FolderFiles(props: { source: number; path: string[] }) {
	const ctx = useContext(context);
	const [folder, setFolder] = useState((undefined as unknown) as Image);
	const [, setLocation] = useLocation();
	const [lightbox, setLightbox] = useState(undefined as number | undefined);
	let {
		isLoading,
		isFetching,
		error,
		data,
		fetchNextPage,
		isFetchingNextPage,
	}: UseInfiniteQueryResult<QueryPage, Response> = useInfiniteQuery(
		["Folder", props.source, props.path],
		async (context: QueryFunctionContext<QueryKey, number>) => {
			console.log("useInfiniteQuery", context.pageParam);
			const url = new URL(ctx.baseUrl.toString() + "Folder");
			url.searchParams.set("source", props.source.toString());
			url.searchParams.set("path", props.path.join("/"));
			url.searchParams.set(
				"offset",
				context.pageParam ? context.pageParam.toString() : "0"
			);
			console.log(url.toString());
			const res = await axios.get(url.toString());
			if (res.data.folder) {
				setFolder(new Image(res.data.folder));
			}
			const data: FolderResponse = res.data;
			const page: QueryPage = {
				...data,
				images: data.data.map((el: Record<keyof Image, any>) => {
					return new Image({ ...el, baseUrl: ctx.baseUrl.toString() });
				}),
			};
			return page;
		},
		{
			// initialData: [],
			getNextPageParam: (lastPage, pages) => {
				// console.log("getNextPageParam", lastPage.nextOffset);
				return lastPage.nextOffset !== null ? lastPage.nextOffset : undefined;
			},
		}
	);

	const getDeeper = (index: number, photo?: Image) => {
		if (!photo) {
			return;
		}
		console.log(photo.basename);
		if (photo.isDir()) {
			setLocation(getLoc(photo.basename));
		} else {
			setLightbox(index);
		}
	};

	const getLoc = (basename: string) => {
		let path = props.path.length ? props.path.join("/") + "/" : "";
		let location = "/folders/" + props.source + "/" + path + basename;
		return location;
	};

	const loadedImages = () => {
		return (
			data?.pages.reduce(
				(acc: number, page: QueryPage) => acc + page.images.length,
				0
			) ?? 0
		);
	};

	const allImages = () => {
		if (!data) {
			return [];
		}
		const flatList = data.pages.reduce(
			(acc: Image[], page: QueryPage, indexPage: number) => [
				...acc,
				...page.images.map((img: Image, index: number) => img),
			],
			[]
		);
		// console.log('pages', data.pages.length, 'flatList', flatList.length);
		return flatList;
	};

	const upFolder = () => {
		if (!data) {
			return;
		}
		setLocation("/folders/" + props.source + "/" + folder.dirname);
	};

	return (
		<div className="w-full">
			<FoldersHeader
				source={props.source}
				path={props.path}
				isLoading={isLoading}
				isFetching={isFetchingNextPage}
				error={error}
				pages={data?.pages.length ?? 0}
				dataLength={loadedImages()}
				rows={data?.pages[0].rows ?? 0}
			/>
			{isLoading ? <BarLoader loading={true} /> : null}
			{isLoading ? <GridLoader loading={true} /> : null}
			<AxiosError error={error} />
			{data && (
				<ImageGrid
					images={allImages()}
					total={data.pages[0].rows}
					fetchNextPage={fetchNextPage}
					isFetchingNextPage={isFetchingNextPage}
					onClick={(a, b, index, image) => getDeeper(index, image)}
				/>
			)}
			<ImageLightbox
				currentIndex={lightbox ?? 0}
				images={allImages()}
				onClose={() => setLightbox(undefined)}
				viewerIsOpen={typeof lightbox !== "undefined"}
			/>
			<ShortcutHandler
				keyToPress="ArrowUp"
				modifier="ctrlKey"
				handler={upFolder}
			/>
		</div>
	);
}
