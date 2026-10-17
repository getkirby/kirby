import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "@/components/Layout/Table.vue";
import Collection from "./Collection.vue";
import Items from "./Items.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Collection, {
		props: {
			columns: { title: { label: "Title" } },
			items: [
				{ id: "a", title: "A" },
				{ id: "b", title: "B" }
			],
			layout: "table",
			...props
		},
		attrs,
		global: {
			components: { "k-items": Items, "k-table": Table }
		}
	});
}

describe("Collection.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-collection");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});
});
