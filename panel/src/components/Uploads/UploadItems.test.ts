import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UploadItems from "./UploadItems.vue";

const Item = {
	name: "k-upload-item",
	props: ["completed", "extension", "id", "name"],
	emits: ["remove", "rename"],
	template: "<li />"
};

const items = [
	{ extension: "jpg", id: "a", name: "photo" },
	{ completed: true, extension: "pdf", id: "b", name: "report" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(UploadItems, {
		props: { items, ...props },
		attrs,
		global: { stubs: { "k-upload-item": Item } }
	});
}

describe("k-upload-items", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-upload-items");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("items prop", () => {
		it("renders an item per upload", () => {
			const wrapper = mount();
			expect(
				wrapper.findAllComponents(Item).map((item) => item.props())
			).toStrictEqual([
				{ completed: undefined, extension: "jpg", id: "a", name: "photo" },
				{ completed: true, extension: "pdf", id: "b", name: "report" }
			]);
		});
	});

	// events
	describe("events", () => {
		it("emits the renamed upload with its new name", () => {
			const wrapper = mount();
			wrapper.findAllComponents(Item)[1].vm.$emit("rename", "summary");
			expect(wrapper.emitted("rename")).toStrictEqual([[items[1], "summary"]]);
		});

		it("emits the removed upload", () => {
			const wrapper = mount();
			wrapper.findAllComponents(Item)[0].vm.$emit("remove");
			expect(wrapper.emitted("remove")).toStrictEqual([[items[0]]]);
		});
	});
});
