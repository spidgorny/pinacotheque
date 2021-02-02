import { Source } from "../App";
import { useContext } from "react";
import { context } from "../context";
import { useQuery } from "react-query";
import axios from "redaxios";
import ScanMeta, { FileToScan } from "./scan-meta";

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
			<hr />
			{data.filesToScan.slice(0, 10).map((el: FileToScan) => (
				<ScanMeta
					key={el.id}
					source={props.source}
					file={el}
					autoStart={false}
				/>
			))}
		</div>
	);
}
