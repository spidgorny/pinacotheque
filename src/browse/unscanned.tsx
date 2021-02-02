import { Source } from "../App";
import { useContext, useState } from "react";
import { context } from "../context";
import { useQuery } from "react-query";
import axios from "redaxios";
import ScanMeta, { FileToScan } from "./scan-meta";

function Top10(props: { filesToScan: FileToScan[] }) {
	return (
		<>
			<hr />
			{props.filesToScan.slice(0, 10).map((el: FileToScan) => (
				<ScanMeta key={el.id} file={el} autoStart={false} />
			))}
		</>
	);
}

function MetaScanProgress(props: { data: FileToScan[] }) {
	const [current, setCurrent] = useState(0);
	const [playState, setPlayState] = useState(false);

	const play = () => {
		setPlayState(true);
		next();
	};

	const stop = () => {
		setPlayState(false);
	};

	const next = () => {
		console.log("next");
		if (playState && current < props.data.length - 1) {
			setCurrent((current) => current + 1);
		}
	};

	if (!props.data.length) {
		return <div>Done</div>;
	}

	return (
		<div>
			<progress value={current} max={props.data.length} className="w-full" />
			<div>
				<button
					onClick={play}
					disabled={playState}
					className="bg-yellow-300 p-1 m-1 rounded"
				>
					Play
				</button>
				<button
					onClick={stop}
					disabled={!playState}
					className="bg-red-300 p-1 m-1 rounded"
				>
					Stop
				</button>
				<button
					onClick={next}
					disabled={!playState}
					className="bg-green-300 p-1 m-1 rounded"
				>
					Next
				</button>
			</div>
			{current > 0 && (
				<ScanMeta file={props.data[current - 1]} autoStart={true} />
			)}
			<ScanMeta
				file={props.data[current]}
				autoStart={playState}
				onDone={next}
			/>
		</div>
	);
}

export default function Unscanned(props: { source: Source }) {
	const ctx = useContext(context);

	const {
		isLoading,
		error,
		data,
		refetch,
	}: {
		isLoading: boolean;
		error: Error | null;
		data: any;
		refetch: () => void;
	} = useQuery("GetUnscanned", async () => {
		const url = new URL(ctx.baseUrl.toString() + "GetUnscanned");
		url.searchParams.set("id", props.source.id.toString());
		const res = await axios.get(url.toString());
		return res.data;
	});

	if (isLoading) return <div>Loading...</div>;

	const refetchButton = (
		<button
			className="bg-yellow-300 p-1 rounded"
			onClick={refetch}
			disabled={isLoading}
		>
			Refetch
		</button>
	);

	if (error)
		return (
			<div className="bg-red-300 p-2">
				<p>An error has occurred: {error.message}</p>
				{refetchButton}
			</div>
		);

	return (
		<div>
			<p>Unscanned: {data.count}</p>
			{refetchButton}
			{/*<pre>{JSON.stringify(data, null, 2)}</pre>*/}
			{/*<Top10 data={data.filesToScan} />*/}
			<MetaScanProgress data={data.filesToScan} />
		</div>
	);
}
