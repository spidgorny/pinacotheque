import React, { useContext, useState } from "react";
import Popup from "reactjs-popup";
import { useMutation, useQuery, UseQueryResult } from "react-query";
import axios from "redaxios";
import { Image } from "../model/Image";
import { context } from "../context";
import { CircleLoader, GridLoader } from "react-spinners";
import { AxiosError, buttonStyle } from "../tailwind";
import { ShortcutToggle } from "../stream/image-lightbox";
import { BigImage } from "../stream/big-image";
// @ts-ignore
import TagsInput from "react-tagsinput";

import "react-tagsinput/react-tagsinput.css";

export default function SingleImage(props: { id: string }) {
	const ctx = useContext(context);
	const [popup, setPopup] = useState(false as boolean);

	let {
		isLoading,
		error,
		data,
		refetch,
	}: UseQueryResult<Image, Response> = useQuery(
		["Image", props.id],
		async () => {
			const url = new URL(ctx.baseUrl.toString() + "Image");
			url.searchParams.set("id", props.id);
			console.log(url.toString());
			const res = await axios.get(url.toString());
			const img = new Image({
				...res.data.file,
				baseUrl: ctx.baseUrl.toString(),
			});
			console.log(img.thumbURL);
			return img;
		}
	);

	const closing = (reload: boolean = false) => {
		setPopup(false);
		if (reload) {
			console.log("refetch");
			refetch();
		}
	};

	return (
		<div>
			<div>{props.id}</div>
			{isLoading ? <GridLoader loading={true} /> : null}
			<AxiosError error={error} />
			{data && <BigImage image={data} />}
			<pre>{JSON.stringify(data, null, 2)}</pre>
			{data && (
				<ShortcutToggle keyToPress="t" onVisible={() => setPopup(true)}>
					<Popup open={popup} position="right center" onClose={closing}>
						<TagForm image={data} closePopup={closing} />
					</Popup>
				</ShortcutToggle>
			)}
		</div>
	);
}

function TagForm(props: {
	image: Image;
	closePopup: (reload: boolean) => void;
}) {
	const ctx = useContext(context);
	const [tags, setTags] = useState(props.image.tags as string[]);

	const mutation = useMutation((data: string[]) => {
		const url = new URL(ctx.baseUrl.toString() + "Tags");
		url.searchParams.set("id", props.image.id.toString());
		return axios.post(url.toString(), data);
	});

	const saveTags = (e: React.MouseEvent) => {
		e.preventDefault();
		console.log(tags);
		mutation.mutate(tags);
		if (mutation.isSuccess) {
			props.closePopup(true); // refetch
		}
	};

	return (
		<form>
			<TagsInput value={tags} onChange={(tags: string[]) => setTags(tags)} />
			<button
				type="submit"
				onClick={saveTags}
				className={buttonStyle + " text-right my-2"}
			>
				{mutation.isLoading && <CircleLoader loading={true} />}
				Save
			</button>
		</form>
	);
}
