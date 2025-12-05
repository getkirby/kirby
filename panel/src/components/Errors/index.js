import ErrorDetails from "./ErrorDetails.vue";
import ErrorTrace from "./ErrorTrace.vue";

export default {
	install(app) {
		app.component("k-error-details", ErrorDetails);
		app.component("k-error-trace", ErrorTrace);
	}
};
