import Headline from "./Headline.vue";
import Text from "./Text.vue";

export default {
	install(app) {
		app.component("k-headline", Headline);
		app.component("k-text", Text);
	}
};
