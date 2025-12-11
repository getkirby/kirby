import Code from "./Code.vue";
import CodeToken from "./CodeToken.vue";
import Headline from "./Headline.vue";
import Label from "./Label.vue";
import Text from "./Text.vue";

export default {
	install(app) {
		app.component("k-code", Code);
		app.component("k-code-token", CodeToken);
		app.component("k-headline", Headline);
		app.component("k-label", Label);
		app.component("k-text", Text);
	}
};
