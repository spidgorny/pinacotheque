import React, { useState } from "react";
import { useQuery } from "react-query";
import axios from "redaxios";
import { useContext } from "react";
import { context } from "../context";
import { BarLoader } from "react-spinners";

export default function Histogram(props: {
	children: (data: Record<string, number>) => JSX.Element;
}) {
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
		const url = new URL(ctx.baseUrl.toString() + "Histogram");
		const res = await axios.get(url.toString());
		return res.data;
	});
	if (isLoading) {
		return <BarLoader loading={true} />;
	}
	if (error) {
		return <div>{error}</div>;
	}
	console.log(Object.values(data.histogram).length);
	return <div>{props.children(data.histogram)}</div>;
}
