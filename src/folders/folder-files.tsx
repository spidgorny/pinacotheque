import { Sidebar } from "../stream/Sidebar";
import React, { useContext, useState } from "react";
import { Source } from "../App";
import { useLocation } from "wouter";
import {
	QueryFunctionContext,
	useInfiniteQuery,
	UseInfiniteQueryResult,
	useQuery,
} from "react-query";
import axios from "redaxios";
import { context } from "../context";
import { Image } from "../model/Image";
import { BarLoader, GridLoader } from "react-spinners";
import { UseQueryResult } from "react-query/types/react/types";
import { PhotoProps } from "react-photo-gallery";
import { FoldersHeader } from "./folders-header";
import { GridImage } from "./grid-image";
import { QueryKey } from "react-query/types/core/types";

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

interface FolderResponse {
	status: string;
	path: string;
	offset: number;
	folder: Record<keyof Image, any>;
	query: string;
	data: Record<keyof Image, any>[];
	nextOffset: number;
	duration: number;
}

interface QueryPage extends FolderResponse {
	images: Image[];
}

function FolderFiles(props: { source: number; path: string[] }) {
	const ctx = useContext(context);
	const [folder, setFolder] = useState((undefined as unknown) as Image);
	const [, setLocation] = useLocation();
	let {
		isLoading,
		error,
		data,
		fetchNextPage,
	}: UseInfiniteQueryResult<QueryPage, string> = useInfiniteQuery(
		["Folder", props.source, props.path],
		async (context: QueryFunctionContext<QueryKey, number>) => {
			console.log(context);
			const url = new URL(ctx.baseUrl.toString() + "Folder");
			url.searchParams.set("source", props.source.toString());
			url.searchParams.set("path", props.path.join("/"));
			url.searchParams.set(
				"offset",
				context.pageParam ? context.pageParam.toString() : "0"
			);
			// console.log(url.toString());
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
			getNextPageParam: (lastPage, pages) => lastPage.nextOffset,
		}
	);

	const getDeeper = (photo: Image) => {
		console.log(photo.basename);
		if (photo.isDir()) {
			setLocation(getLoc(photo.basename));
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

	const setVisible = (img: Image, isVisible: boolean, index: number) => {
		if (data) {
			let pageSize = data.pages[0].images.length;
			let remainder = index - data?.pages.length * pageSize;
			if (index > pageSize * 0.5 && isVisible) {
				console.log(index, "is visible");
				fetchNextPage();
			}
		}
	};

	// console.log(data);
	return (
		<div className="w-full">
			<FoldersHeader
				source={props.source}
				path={props.path}
				isLoading={isLoading}
				error={error}
				pages={data?.pages.length ?? 0}
				dataLength={loadedImages()}
			/>
			{isLoading ? <BarLoader loading={true} /> : null}
			{isLoading ? <GridLoader loading={true} /> : null}
			{error ? <div className="error">{error.toString()}</div> : null}
			<div className="flex flex-row p-2 flex-grow flex-wrap">
				{data &&
					data.pages.map((page, indexPage: number) => (
						<React.Fragment key={indexPage}>
							{page.images.map((img: Image, index: number) => {
								return (
									<GridImage
										key={img.id}
										img={img}
										onClick={(
											e: React.MouseEvent,
											photo: PhotoProps,
											index: number,
											image?: Image
										) => {
											if (image) {
												getDeeper(image);
											}
										}}
										setVisible={(
											img: Image,
											isVisible: boolean,
											index: number
										) => setVisible(img, isVisible, index)}
										index={indexPage + index}
									/>
								);
							})}
						</React.Fragment>
					))}
			</div>
		</div>
	);
}
