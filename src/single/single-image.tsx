import React, { useContext, useEffect, useState } from "react";
import Popup from "reactjs-popup";
import { useQuery, useQueryClient, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { context } from "../context";
import { GridLoader } from "react-spinners";
import { AxiosError, badge } from "../tailwind";
import { ShortcutToggle } from "../stream/image-lightbox";
import { BigImage } from "../stream/big-image";
import { ShortcutHandler } from "../stream/shortcut-handler";
import { TagForm } from "./tag-form";
import { useLocation } from "wouter";

export default function SingleImage(props: { id: string }) {
	const ctx = useContext(context);
	const [popup, setPopup] = useState(false as boolean);
	const [surround, setSurround] = useState([]);
	const [, setLocation] = useLocation();

	let fetchImage = async () => {
		const url = new URL("Image", ctx.baseUrl);
		url.searchParams.set("id", props.id);
		// console.log(url.toString());
		const res = await axios.get(url.toString());
		setSurround(res.data.folder);
		const img = new Image({
			...res.data.file,
			baseUrl: ctx.baseUrl.toString(),
		});
		// console.log(img.thumbURL);
		return img;
	};

	const queryClient = useQueryClient();

	let {
		isLoading,
		error,
		data,
		refetch,
	}: UseQueryResult<Image, Response> = useQuery(
		["Image", props.id],
		fetchImage,
		{
			onSuccess: async () => {
				const nextID = adjacentImage(+1);
				// console.log("useEffect", nextID);
				if (nextID) {
					await queryClient.prefetchQuery(["Image", nextID], fetchImage);
					const nextImage = await queryClient.fetchQuery(
						["Image", nextID],
						fetchImage
					);
					// console.log("res", nextImage);
					if (nextImage !== null) {
						const img = new global.Image();
						console.log("preload", nextImage.thumbURL);
						img.src = nextImage.thumbURL;
						img.onload = () => {
							console.log("preload done");
						};
					}
				}
			},
		}
	);

	useEffect(() => {}, []);

	const closing = (reload: boolean = false) => {
		setPopup(false);
		if (reload) {
			console.log("refetch");
			refetch().then((r) => {});
		}
	};

	const adjacentImage = (plusMinus: number): Image | undefined => {
		const index = surround.findIndex((el) => el === props.id);
		console.log("prevImage", index);
		return surround[index + plusMinus];
	};

	const prevImage = () => {
		const newID = adjacentImage(-1);
		if (newID) {
			setLocation("/image/" + newID);
		}
	};

	const nextImage = () => {
		const newID = adjacentImage(+1);
		if (newID) {
			setLocation("/image/" + newID);
		}
	};

	const upFolder = () => {
		if (!data) {
			return;
		}
		setLocation("/folders/" + data.source + "/" + data.dirname);
	};

	const index = surround.findIndex((el) => el === props.id);

	return (
		<div>
			<div>
				{index} / {surround.length} [id={props.id}]
			</div>
			{isLoading ? <GridLoader loading={true} /> : null}
			<AxiosError error={error} />
			{data && <BigImage image={data} />}
			{data && (
				<div className="flex flex-row p-1">
					Tags:{" "}
					{data.tags.map((el) => (
						<span key={el} className={badge}>
							{el}
						</span>
					))}
				</div>
			)}
			<pre>{JSON.stringify(data, null, 2)}</pre>
			{data && (
				<>
					<ShortcutToggle keyToPress="t" onVisible={() => setPopup(true)}>
						<Popup open={popup} position="right center" onClose={closing}>
							<TagForm image={data} closePopup={closing} />
						</Popup>
					</ShortcutToggle>
					<ShortcutHandler
						keyToPress="ArrowLeft"
						modifier="ctrlKey"
						handler={prevImage}
					/>
					<ShortcutHandler
						keyToPress="ArrowRight"
						modifier="ctrlKey"
						handler={nextImage}
					/>
					<ShortcutHandler
						keyToPress="ArrowUp"
						modifier="ctrlKey"
						handler={upFolder}
					/>
				</>
			)}
		</div>
	);
}
