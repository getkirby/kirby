import Island, { defaults } from "./island.js";

export default (panel) => {
	return Island(panel, "drawer", defaults());
};
