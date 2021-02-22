import { Image } from "../model/Image";
import React, { FormEvent, useContext, useState } from "react";
import { context } from "../context";
import { useMutation } from "react-query";
import axios from "redaxios";
import { buttonStyle } from "../tailwind";
import { CircleLoader } from "react-spinners";
// @ts-ignore
import TagsInput from "react-tagsinput";
import "react-tagsinput/react-tagsinput.css";
import { ShortcutHandler } from "../stream/shortcut-handler";

export function TagForm(props: {
	image: Image;
	closePopup: (reload: boolean) => void;
}) {
	const ctx = useContext(context);
	const [tags, setTags] = useState(props.image.tags as string[]);

	const mutation = useMutation(
		(data: string[]) => {
			const url = new URL(ctx.baseUrl.toString() + "Tags");
			url.searchParams.set("id", props.image.id.toString());
			return axios.post(url.toString(), data);
		},
		{
			onSuccess: (data, variables, context) => {
				props.closePopup(true); // refetch
			},
		}
	);

	const saveTags = () => {
		console.log(tags);
		mutation.mutate(tags);
	};

	const onSubmit = (e: FormEvent) => {
		e.preventDefault();
		saveTags();
	};

	return (
		<form onSubmit={onSubmit}>
			<TagsInput value={tags} onChange={(tags: string[]) => setTags(tags)} />
			<button type="submit" className={buttonStyle + " text-right my-2"}>
				{mutation.isLoading && <CircleLoader loading={true} />}
				Save
			</button>
			<ShortcutHandler
				keyToPress="Enter"
				handler={saveTags}
				modifier="ctrlKey"
			/>
		</form>
	);
}
