import { Image } from "../model/Image";
import React from "react";
import { PhotoProps } from "react-photo-gallery";
import { GridImage } from "./grid-image";

export function ImageGrid(props: {
	images: Image[];
	total: number;
	fetchNextPage: () => void;
	isFetchingNextPage: boolean;
	onClick: (
		e: React.MouseEvent,
		photo: PhotoProps,
		index: number,
		image?: Image
	) => void;
}) {
	const setVisible = (img: Image, isVisible: boolean, index: number) => {
		if (props.images && isVisible) {
			let pageSize = 50; //data.pages[0].images.length;
			let loadedImages = props.images.length;
			let minus25 = loadedImages - pageSize * 0.5;
			// console.log(
			// 	"index",
			// 	index,
			// 	"pageSize",
			// 	pageSize,
			// 	"loadedImages",
			// 	loadedImages(),
			// 	"minus25",
			// 	minus25
			// );
			if (index > minus25) {
				let rows = props.total;
				console.log(index, "is visible", "rows: ", rows);
				if (loadedImages < rows && !props.isFetchingNextPage) {
					props.fetchNextPage();
				}
			}
		}
	};

	return (
		<div className="flex flex-row p-2 flex-grow flex-wrap">
			{props.images.map((img: Image, index: number) => {
				return (
					<GridImage
						key={img.id}
						img={img}
						onClick={props.onClick}
						setVisible={(img: Image, isVisible: boolean, index: number) =>
							setVisible(img, isVisible, index)
						}
						index={index}
					/>
				);
			})}
		</div>
	);
}
