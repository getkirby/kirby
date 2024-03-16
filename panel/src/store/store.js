import { createStore } from "vuex";

// store modules
import content from "./modules/content.js";

export default createStore({
	// eslint-disable-next-line
	strict: process.env.NODE_ENV !== "production",
	modules: {
		content
	}
});
