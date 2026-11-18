import { describe, it } from "@test/unit";
import GapField from "./GapField.vue";

describe("k-gap-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(GapField, "DIV", "k-gap-field");
		it.acceptsClass(GapField);
		it.acceptsStyle(GapField);
		it.inheritsNoAttrs(GapField);
	});
});
