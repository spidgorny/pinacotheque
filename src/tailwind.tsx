import React from "react";

export const alertError =
	"bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative";

export function AxiosError(props: { error?: Response | null }) {
	return (
		<>
			{props.error ? (
				<div className={alertError}>
					<p>
						{props.error.status} {props.error.statusText}
					</p>
					<a target="_blank" href={props.error.url}>
						{props.error.url}
					</a>
				</div>
			) : null}
		</>
	);
}

export const buttonStyle =
	"bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded";

export const badge =
	"mr-2 bg-blue-600 text-white p-2 rounded leading-none items-center";
