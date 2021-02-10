import React from "react";
import { Link } from "wouter";

export function Header(props: {}) {
	return (
		<div
			className="flex flex-row"
			style={{
				background: "#33C3F0",
				color: "white",
				padding: "0.5em",
			}}
		>
			<div className="px-2">
				<Link href="/" style={{ color: "white" }}>
					stream
				</Link>
			</div>
			<div className="px-2">
				<Link href="/browse" style={{ color: "white" }}>
					browse
				</Link>
			</div>
			<div className="" />
		</div>
	);
}
