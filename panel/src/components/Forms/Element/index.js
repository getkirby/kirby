import Choice from "./Choice.vue";
import Color from "./Color/color.js";
import Range from "./Range.vue";

/** CSS for custom elements */
import "./Color/color.css";

export default {
	install(app) {
		customElements.define("k-color", Color);
		app.component("k-choice", Choice);
		app.component("k-range", Range);
	}
};
