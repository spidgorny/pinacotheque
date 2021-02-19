import React, { useContext } from "react";
import Popup from "reactjs-popup";
import { useQuery, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { context } from "../context";
import { GridLoader } from "react-spinners";
import { AxiosError } from "../tailwind";
import { ShortcutToggle } from "../stream/image-lightbox";
import { BigImage } from "../stream/big-image";

export default function SingleImage(props: { id: string }) {
	const ctx = useContext(context);
	let {
		isLoading,
		isFetching,
		error,
		data,
	}: UseQueryResult<Image, Response> = useQuery(
		["Image", props.id],
		async () => {
			const url = new URL(ctx.baseUrl.toString() + "Image");
			url.searchParams.set("id", props.id);
			console.log(url.toString());
			const res = await axios.get(url.toString());
			const img = new Image({
				...res.data.file.props,
				meta: res.data.file.meta,
				baseUrl: ctx.baseUrl.toString(),
			});
			console.log(img.thumbURL);
			return img;
		}
	);
	return (
		<div>
			<div>{props.id}</div>
			{isLoading ? <GridLoader loading={true} /> : null}
			<AxiosError error={error} />
			{/*{data && <ImageThumb photo={getPhotoFromImage(data)} />}*/}
			{data && <BigImage image={data} />}
			<pre>{JSON.stringify(data, null, 2)}</pre>
			<ShortcutToggle keyToPress="t">
				<Popup open={true} position="right center">
					<div>Popup content here !!</div>
				</Popup>
			</ShortcutToggle>
		</div>
	);
}
