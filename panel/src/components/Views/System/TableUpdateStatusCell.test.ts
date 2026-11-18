import { describe, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TableUpdateStatusCell from "./TableUpdateStatusCell.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TableUpdateStatusCell, {
		props: { value: "1.0.0", ...props },
		attrs
	});
}

describe("k-table-update-status-cell", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-table-update-status-cell");
		it.inheritsNoAttrs(component);
	});
});
