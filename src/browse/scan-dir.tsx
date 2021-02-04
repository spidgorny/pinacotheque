import { Source } from "../App";
import { useContext, useState } from "react";
import { context } from "../context";
// @ts-ignore
import ndjsonStream from "can-ndjson-stream";
import { GoInfo } from "react-icons/all";
import moment from "moment";
import { CircleLoader } from "react-spinners";
const momentDurationFormatSetup = require("moment-duration-format");

momentDurationFormatSetup(moment);

export default function ScanDir(props: {
	source: Source;
	reloadSources: () => void;
}) {
	const ctx = useContext(context);
	const [loading, setLoading] = useState(false);
	const [progress, setProgress] = useState(0);
	const [max, setMax] = useState(0);
	const [error, setError] = useState("");
	const [startTime, setStart] = useState((undefined as unknown) as Date);
	const [file, setFile] = useState("");
	const [res, setRes] = useState("");

	const parseStream = async (exampleReader: any) => {
		let result:
			| {
					done: boolean;
					value?: {
						status: string;
						file: string;
						progress: number;
						max: number;
						res: string;
						inserted?: number;
					};
			  }
			| undefined;
		while (!result || !result.done) {
			result = await exampleReader.read();
			// console.log(result);
			if (result && result.value) {
				// skip errors (status === 'err')
				if ("file" in result.value) {
					setFile(result.value.file);
				}
				if ("res" in result.value) {
					setRes(result.value.res);
				}
				if (result.value.status === "line") {
					setProgress(result.value.progress);
					setMax(result.value.max);
				}
				if (result.value.status === "done") {
					setError(result.value?.inserted?.toString() ?? "");
				}
			}
		}
	};

	const fetchData = async () => {
		setProgress(0);
		setLoading(true);
		setError("");
		setStart(new Date());
		setFile("");
		setRes("");
		console.log(loading, progress, max, error);
		const urlCheck = new URL("ScanDirApi", ctx.baseUrl);
		urlCheck.searchParams.set("id", props.source.id.toString());

		let res;
		try {
			res = await fetch(urlCheck.toString());
			console.warn(urlCheck.toString(), res.status, res.statusText);
			if (res.status !== 200) {
				setError(res.statusText);
				setLoading(false);
				return;
			}
		} catch (e) {
			setError(e.message);
			setLoading(false);
			return;
		}
		const exampleReader = ndjsonStream(res.body).getReader();

		try {
			await parseStream(exampleReader);
		} catch (e) {
			console.warn(e);
			setError(e.message);
			setLoading(false);
		}

		setLoading(false);
		props.reloadSources();
	};

	const diff = moment().diff(moment(startTime));
	// @ts-ignore
	const dur = moment.duration(diff).format();

	return (
		<div className="">
			<button
				className="bg-yellow-300 p-1 rounded"
				onClick={fetchData}
				disabled={loading}
			>
				Scan Dir
			</button>
			<GoInfo
				style={{ display: "inline", verticalAlign: "top" }}
				title="Import all media files from folders to the database. Without meta-data."
			/>
			{loading && <CircleLoader loading={true} size={16} />}
			<div className="flex flex-row">
				<div className="flex-grow py-1">
					<progress value={progress} max={max} className="w-full" />
				</div>
				<div className="w-32 mx-2">
					{progress} / {max}
				</div>
			</div>
			<div>
				Dur: <pre className="inline">{dur}</pre>
			</div>
			<pre>{file}</pre>
			<pre style={{ whiteSpace: "pre-wrap" }}>{res}</pre>
			{error && <div className="bg-red-300 p-2">{error}</div>}
		</div>
	);
}
