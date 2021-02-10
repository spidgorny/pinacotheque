import { useContext } from "react";
import { context } from "../context";
import { QueryObserverBaseResult, useQuery } from "react-query";
import axios from "redaxios";

export interface FileToScan {
	id: number;
}

export default function ScanMeta(props: {
	file: FileToScan;
	autoStart?: boolean;
	onDone?: () => void;
}) {
	const ctx = useContext(context);

	const { isLoading, error, data, refetch } = useQuery(
		["ScanMeta", props.file.id],
		async () => {
			const url = new URL(ctx.baseUrl.toString() + "ScanMeta");
			url.searchParams.set("fileID", props.file.id.toString());
			console.log(url.toString());
			const res = await axios.get(url.toString());
			return res.data;
		},
		{
			refetchOnWindowFocus: false,
			enabled: props.autoStart,
			initialData: {},
		}
	) as QueryObserverBaseResult<
		{
			thumbUrl: string;
			thumbMeta: {
				image: {
					width: number;
					height: number;
				};
			};
		},
		Error
	>;

	// if (isFetching)
	// 	return (
	// 		<div className="inline-block" style={{ width: 256, height: 144 }}>
	// 			<FadeLoader loading={true} />
	// 		</div>
	// 	);

	const scanButton = (
		<button
			className="bg-yellow-300 p-1 rounded m-1"
			onClick={() => refetch()}
			disabled={isLoading}
			style={{ width: 256, height: 144 }}
		>
			Scan Meta ({props.file.id})
		</button>
	);

	if (error)
		return (
			<div className="bg-red-300 p-2">
				<p>An error has occurred: {error.message}</p>
				{scanButton}
			</div>
		);

	if (data) {
		data.thumbUrl && props.onDone && setTimeout(props.onDone, 500);

		return (
			<span>
				{/*<pre>{JSON.stringify(data, null, 2)}</pre>*/}
				{data.thumbUrl && (
					<img
						src={data.thumbUrl}
						alt={data.thumbUrl}
						style={{
							width: data.thumbMeta.image.width ?? 256,
							height: data.thumbMeta.image.height ?? 144,
						}}
						className="inline p-1 m-1"
					/>
				)}
				{!data.thumbUrl && scanButton}
			</span>
		);
	}

	return <div> some shit</div>;
}
