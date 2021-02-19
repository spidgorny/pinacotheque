import React, { useContext, useState } from "react";
import Popup from "reactjs-popup";
import { useQuery, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { context } from "../context";
import { GridLoader } from "react-spinners";
import { AxiosError, buttonStyle } from "../tailwind";
import { ShortcutToggle } from "../stream/image-lightbox";
import { BigImage } from "../stream/big-image";
// @ts-ignore
import TagsInput from "react-tagsinput";

import "react-tagsinput/react-tagsinput.css";
export default function SingleImage(props: { id: string }) {
	const ctx = useContext(context);
	let { isLoading, error, data }: UseQueryResult<Image, Response> = useQuery(
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
			{data && <BigImage image={data} />}
			<pre>{JSON.stringify(data, null, 2)}</pre>
			<ShortcutToggle keyToPress="t">
				<Popup open={true} position="right center" onClose={() => {}}>
					<TagForm />
				</Popup>
			</ShortcutToggle>
		</div>
	);
}

function TagForm(props: {}) {
	const [tags, setTags] = useState([] as string[]);

	const saveTags = () => {
		console.log(tags);
	};

	return (
		<form>
			<TagsInput value={tags} onChange={(tags: string[]) => setTags(tags)} />
			<button
				type="submit"
				onClick={saveTags}
				className={buttonStyle + " text-right my-2"}
			>
				Save
			</button>
		</form>
	);
}
