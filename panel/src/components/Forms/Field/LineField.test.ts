import { describe, it } from "@test/unit";
import LineField from "./LineField.vue";

describe("k-line-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(LineField, "HR", "k-line-field");
		it.acceptsClass(LineField);
		it.acceptsStyle(LineField);
		it.inheritsNoAttrs(LineField);
	});
});
