import Island, { defaults } from "./island.js";

export default (panel) => {
	const parent = Island(panel, "dialog", defaults());

	return {
		...parent
	};
};
