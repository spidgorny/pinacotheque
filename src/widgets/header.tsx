import React, { FormEvent } from "react";
import { Link, useLocation } from "wouter";
import { useLocalStorage } from "../use/use-local-storage";
import { searchStyle } from "../tailwind";
import { GiMagnifyingGlass } from "react-icons/all";

function SearchForm(props: {}) {
	const [lastSearch, setLastSearch] = useLocalStorage("lastSearch", "");
	const [, setLocation] = useLocation();

	const search = (e: FormEvent) => {
		e.preventDefault();
		let form = e.target as HTMLFormElement;
		let search = (form.elements[0] as HTMLInputElement).value;
		console.log(search);
		setLastSearch(search);
		setLocation("/search/" + encodeURIComponent(search));
	};

	return (
		<form onSubmit={search} className="">
			<input
				type="search"
				placeholder="search tags, path"
				defaultValue={lastSearch}
				className={searchStyle}
			/>
			<button
				type="submit"
				className="block w-7 h-7 text-center text-xl leading-0 absolute top-2 right-2 text-gray-400 focus:outline-none hover:text-gray-900 transition-colors mr-8 py-2"
			>
				<GiMagnifyingGlass />
			</button>
		</form>
	);
}

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
				<Link href="/folders" style={{ color: "white" }}>
					folders
				</Link>
			</div>
			<div className="px-2">
				<Link href="/browse" style={{ color: "white" }}>
					browse
				</Link>
			</div>
			<div className="px-2">
				<Link href="/visibility" style={{ color: "white" }}>
					visibility
				</Link>
			</div>
			<div className="px-2">
				<Link href="/one-image" style={{ color: "white" }}>
					one-image
				</Link>
			</div>{" "}
			<div className="px-2">
				<Link href="/timeline-test" style={{ color: "white" }}>
					timeline-test
				</Link>
			</div>
			<div className="px-2 flex-grow">
				<SearchForm />
			</div>
		</div>
	);
}
