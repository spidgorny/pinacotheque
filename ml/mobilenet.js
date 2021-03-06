const img = document.getElementById('img');
const version = 2;
const alpha = 0.5;

async function run() {
	// Load the model.
	const model = await mobilenet.load({version, alpha});

	// Classify the image.
	const predictions = await model.classify(img);
	console.log('Predictions');
	console.log(predictions);

	// Get the logits.
	const logits = model.infer(img);
	console.log('Logits');
	logits.print(true);

	// Get the embedding.
	const embedding = model.infer(img, true);
	console.log('Embedding');
	embedding.print(true);
}
run();
