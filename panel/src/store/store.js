import Vue from "vue";
import Vuex from "vuex";

// store modules
import content from "./modules/content.js";

Vue.use(Vuex);

export default new Vuex.Store({
	// eslint-disable-next-line
	strict: process.env.NODE_ENV !== "production",
	modules: {
		content
	}
});
