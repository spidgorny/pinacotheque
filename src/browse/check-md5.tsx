import { Source } from "../App";
import React, { useContext, useEffect, useState } from "react";
import { context } from "../context";
// @ts-ignore
import ndjsonStream from "can-ndjson-stream";
import { GoInfo } from "react-icons/all";
import moment from "moment";
import { CircleLoader } from "react-spinners";
const momentDurationFormatSetup = require("moment-duration-format");
const ColorHash = require("color-hash");

momentDurationFormatSetup(moment);

export default function CheckMD5(props: { source: Source }) {
	const ctx = useContext(context);
	const [loading, setLoading] = useState(false);
	const [folders, setFolders] = useState([] as string[]);
	const [md5, setMD5] = useState("");
	const [error, setError] = useState((null as unknown) as string);
	const [startTime, setStart] = useState((undefined as unknown) as Date);

	const fetchData = async () => {
		setFolders([]);
		setLoading(true);
		setError("");
		setStart(new Date());
		const urlCheck = new URL("SourceScan", ctx.baseUrl);
		urlCheck.searchParams.set("id", props.source.id.toString());
		const res = await fetch(urlCheck.toString());
		console.warn(urlCheck.toString(), res.status, res.statusText);
		if (res.status !== 200) {
			setError(res.statusText);
			setLoading(false);
			return;
		}
		const exampleReader = ndjsonStream(res.body).getReader();

		let result:
			| {
					done: boolean;
					value?: { status: string; file: string[]; md5?: string };
			  }
			| undefined;
		while (!result || !result.done) {
			result = await exampleReader.read();
			// console.log(result);
			if (result && result.value) {
				// skip errors (status === 'err')
				if ("file" in result.value && result.value.status === "lines") {
					const newFile = result.value.file;
					setFolders((folders) => folders.concat(...newFile));
				}
				if ("md5" in result.value) {
					setMD5(result.value.md5 ?? "");
					setLoading(false);
				}
			}
		}
	};

	// autostart fetchData() is disabled as this is heavy load on the server
	useEffect(() => {
		// noinspection JSIgnoredPromiseFromCall
		// fetchData();
	}, [props.source]);

	const diff = moment().diff(moment(startTime));
	// @ts-ignore
	const dur = moment.duration(diff).format();

	const colorHash = new ColorHash();
	return (
		<div className="">
			<button
				className="bg-yellow-300 p-1 rounded inline"
				onClick={fetchData}
				disabled={loading}
			>
				Rescan
			</button>
			<GoInfo
				style={{ display: "inline", verticalAlign: "top" }}
				title="find all subdirectories and check md5 hash of their name to quickly see if this folder requires re-scanning"
			/>
			{loading && <CircleLoader loading={true} size={16} />}
			<div>
				<div>
					Dir: {folders?.length}/{props.source.folders}
				</div>
				<div>Dur: {dur}</div>
				<progress
					value={folders.length}
					max={props.source.folders}
					className="w-full"
				/>
				<div>
					MD5:{" "}
					<span
						className={md5 === props.source.md5 ? "bg-green-300" : "bg-red-300"}
					>
						{md5}
					</span>
					<div
						style={{
							width: "2em",
							minHeight: "2em",
							backgroundColor: colorHash.hex(props.source.md5),
						}}
					>
						&nbsp;
					</div>
				</div>
			</div>
			{error && <div className="bg-red-300">{error}</div>}
		</div>
	);
}
