export function ymd(date: Date) {
	return (
		date.getFullYear() +
		"-" +
		(date.getMonth() + 1).toString().padStart(2, "0") +
		"-" +
		date.getDate().toString().padStart(2, "0")
	);
}

export function basename(str: string, sep: string = "/") {
	return str.substr(str.lastIndexOf(sep) + 1);
}

export function dirname(str: string, sep: string = "/") {
	return str.substr(0, str.lastIndexOf(sep));
}
