import React from "react";
import { Source } from "../app";
import { useCallback, useContext, useEffect, useState } from "react";
import { context } from "../context";
import axios from "redaxios";
import { CircleLoader } from "react-spinners";

interface CheckSourceState {
	status: "ok" | "error";
	error?: string;
	files: number;
	folders: number;
}

export function CheckSource(props: { source: Source }) {
	const ctx = useContext(context);
	const [state, setState] = useState(
		(null as unknown) as CheckSourceState | null
	);
	const [error, setError] = useState((null as unknown) as string);

	const fetchData = useCallback(async () => {
		setState(null);
		const urlCheck = new URL("CheckSource", ctx.baseUrl);
		urlCheck.searchParams.set("id", props.source.id.toString());
		try {
			const res = await axios.get(urlCheck.toString());
			setTimeout(() => setState(res.data), 0);
		} catch (e) {
			setError(e.message);
		}
	}, [ctx.baseUrl, props.source.id]);

	useEffect(() => {
		// noinspection JSIgnoredPromiseFromCall
		fetchData();
	}, [props.source, fetchData]);

	if (!state) {
		return (
			<button className="bg-yellow-300 p-1 rounded" onClick={fetchData}>
				<CircleLoader loading={true} size={16} />
				<span> Loading</span>
			</button>
		);
	}
	if (error || state?.status !== "ok") {
		return (
			<button className="bg-red-300 p-1 rounded" onClick={fetchData}>
				{error} {state.error}
			</button>
		);
	}
	return (
		<button className="bg-blue-300 p-1 rounded" onClick={fetchData}>
			Accessible
		</button>
	);
}
