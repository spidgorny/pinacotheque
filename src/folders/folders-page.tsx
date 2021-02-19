import { Sidebar } from "../stream/Sidebar";
import React, { useContext, useState } from "react";
import { Source } from "../App";
import { Link, useLocation } from "wouter";
import { isError, useQuery } from "react-query";
import axios from "redaxios";
import { context } from "../context";
import { Image } from "../model/Image";
import { MyPhoto } from "../stream/MyPhoto";
import { BarLoader, GridLoader } from "react-spinners";
import { UseQueryResult } from "react-query/types/react/types";
import { PhotoProps } from "react-photo-gallery";

export default function FoldersPage(props: {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
	params: any;
}) {
	const [, setLocation] = useLocation();

	const setSource = (x?: number) => {
		if (!x) {
			setLocation("/folders");
			return;
		}
		setLocation("/folders/" + x.toString());
	};

	console.log("params", props.params);
	let path = props.params.slug.split("/") as string[];
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

export function FolderFiles(props: { source: number; path: string[] }) {
	const ctx = useContext(context);
	const [folder, setFolder] = useState((undefined as unknown) as Image);
	const [, setLocation] = useLocation();
	let { isLoading, error, data }: UseQueryResult<Image[], string> = useQuery(
		["Folder", props.source, props.path],
		async () => {
			const url = new URL(ctx.baseUrl.toString() + "Folder");
			url.searchParams.set("source", props.source.toString());
			url.searchParams.set("path", props.path.join("/"));
			// console.log(url.toString());
			const res = await axios.get(url.toString());
			if (res.data.folder) {
				setFolder(new Image(res.data.folder));
			}
			return res.data.data.map((el: Record<keyof Image, any>) => {
				return new Image({ ...el, baseUrl: ctx.baseUrl.toString() });
			});
		},
		{
			initialData: [],
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

	// console.log(data);
	return (
		<div className="w-full">
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
				<div className={isLoading ? "" : "text-gray-300 text-opacity-50"}>
					isLoading
				</div>
				<div className={error ? "" : "text-gray-300 text-opacity-50"}>
					isError
				</div>
				<div>Photos: {data?.length}</div>
			</div>
			{isLoading ? <BarLoader loading={true} /> : null}
			{isLoading ? <GridLoader loading={true} /> : null}
			{error ? <div className="error">{error.toString()}</div> : null}
			<div className="flex flex-row p-2 flex-grow flex-wrap">
				{(data ?? []).map((img: Image) => {
					return (
						<div
							key={img.id}
							style={{ border: "solid 1px silver", width: 256, height: 256 }}
						>
							<MyPhoto
								index={img.id}
								photo={{
									width: img.getThumbWidth(),
									height: img.getThumbHeight(),
									src: img.src,
									image: img,
								}}
								forceInfo={img.isDir()}
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
							/>
						</div>
					);
				})}
			</div>
		</div>
	);
}
