import ErrorTrace from "./ErrorTrace.vue";

export default {
	install(app) {
		app.component("k-error-trace", ErrorTrace);
	}
};
