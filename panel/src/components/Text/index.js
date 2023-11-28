import Code from "./Code.vue";
import Headline from "./Headline.vue";
import Label from "./Label.vue";
import Text from "./Text.vue";

export default {
	install(app) {
		app.component("k-code", Code);
		app.component("k-headline", Headline);
		app.component("k-label", Label);
		app.component("k-text", Text);
	}
};
