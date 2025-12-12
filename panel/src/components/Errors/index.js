import ErrorTrace from "./ErrorTrace.vue";
import ValidationIssues from "./ValidationIssues.vue";

export default {
	install(app) {
		app.component("k-error-trace", ErrorTrace);
		app.component("k-validation-issues", ValidationIssues);
	}
};
