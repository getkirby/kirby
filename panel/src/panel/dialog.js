import Island, { defaults } from "./island.js";

export default (panel) => {
	return Island(panel, "dialog", defaults());
};
