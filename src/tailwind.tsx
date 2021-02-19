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
