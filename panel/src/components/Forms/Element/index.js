import Choice from "./Choice.vue";
import Color from "./Color/color.js";
import Coords from "./Coords/coords.js";
import Range from "./Range.vue";

/** CSS for custom elements */
import "./Color/color.css";
import "./Coords/coords.css";

export default {
	install(app) {
		customElements.define("k-color", Color);
		customElements.define("k-coords", Coords);
		app.component("k-choice", Choice);
		app.component("k-range", Range);
	}
};
