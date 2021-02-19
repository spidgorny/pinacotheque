import { Source } from "../app";
import { useQuery } from "react-query";
import axios from "redaxios";
import { useContext } from "react";
import { context } from "../context";
import { CircleLoader } from "react-spinners";

export default function SourceMeta(props: { source: Source }) {
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
	} = useQuery("SourceMeta", async () => {
		const url = new URL(ctx.baseUrl.toString() + "SourceMeta");
		url.searchParams.set("id", props.source.id.toString());
		const res = await axios.get(url.toString());
		return res.data;
	});
	return (
		<div>
			<pre>{JSON.stringify(data, null, 2)}</pre>
			{isLoading && <CircleLoader loading={true} size={16} />}
			{error && (
				<div className="bg-red-300 p-2">
					<p>An error has occurred: {error.message}</p>
					<button
						className="bg-yellow-300 p-1 rounded"
						onClick={refetch}
						disabled={isLoading}
					>
						Refetch
					</button>
				</div>
			)}
		</div>
	);
}
