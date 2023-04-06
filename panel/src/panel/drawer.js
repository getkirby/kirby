import Island, { defaults } from "./island.js";

export default (panel) => {
	const parent = Island(panel, "drawer", defaults());

	return {
		...parent
	};
};
