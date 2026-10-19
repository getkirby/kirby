import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "./Table.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Table, {
		props: {
			columns: { title: { label: "Title" } },
			rows: [
				{ id: "a", title: "A" },
				{ id: "b", title: "B" }
			],
			...props
		},
		attrs
	});
}

describe("Table.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-table");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// events
	describe("select event", () => {
		it("adds a checked row", async () => {
			const wrapper = mount({ selecting: true, selected: ["a"] });
			await wrapper.findAll("input")[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["a", "b"]]]);
		});

		it("removes an unchecked row", async () => {
			const wrapper = mount({ selecting: true, selected: ["a", "b"] });
			await wrapper.findAll("input")[0].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});

		it("replaces the selection in single mode", async () => {
			const wrapper = mount({
				selecting: true,
				selected: ["a"],
				selectmode: "single"
			});
			await wrapper.findAll("input")[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});
	});
});
