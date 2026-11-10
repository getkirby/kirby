import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import ModelPickerField from "./ModelPickerField.vue";

// renders the options slot of each item like the real collection
const Collection = {
	props: ["items"],
	emits: ["sort"],
	template:
		'<div><slot v-for="(_, index) in items" name="options" :index="index" /></div>'
};

function panel() {
	return reactive({
		api: { get: vi.fn() },
		language: { code: "en" }
	});
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(ModelPickerField, {
		props: {
			endpoints: { field: "pages/test/fields/related" },
			...props
		},
		attrs,
		global: {
			// registered globally in the Panel
			components: { "k-collection": Collection },
			mocks: {
				// the abstract base leaves `emptyProps` to its subclasses
				emptyProps: {},
				$panel
			}
		}
	});
}

/**
 * The items passed to the collection
 */
function items(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Collection).props("items");
}

describe("k-model-picker-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-models-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// watch
	describe("$panel.language.code watcher", () => {
		it("reloads the items", async () => {
			const $panel = panel();
			$panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Home" }]);

			const wrapper = mount({ value: ["a"] }, {}, $panel);
			await flushPromises();

			expect(wrapper.vm.selected).toStrictEqual([{ id: "a", text: "Home" }]);

			$panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Startseite" }]);
			$panel.language.code = "de";
			await flushPromises();

			expect($panel.api.get).toHaveBeenCalledTimes(2);
			expect(wrapper.vm.selected).toStrictEqual([
				{ id: "a", text: "Startseite" }
			]);
		});
	});

	describe("value watcher", () => {
		const endpoint = "pages/test/fields/related/items";

		it("fetches only the items that are not loaded yet", async () => {
			const $panel = panel();
			$panel.api.get.mockResolvedValueOnce([{ id: "a", text: "A" }]);

			const wrapper = mount({ value: ["a"] }, {}, $panel);
			await flushPromises();

			$panel.api.get.mockResolvedValueOnce([{ id: "b", text: "B" }]);
			await wrapper.setProps({ value: ["a", "b"] });

			// a placeholder holds the place of the item while it loads
			expect(items(wrapper)).toStrictEqual([
				{ id: "a", text: "A" },
				{ id: "b", theme: "skeleton", image: { icon: "loader" } }
			]);

			await flushPromises();

			expect($panel.api.get).toHaveBeenLastCalledWith(endpoint, {
				items: ["b"]
			});
			expect(items(wrapper)).toStrictEqual([
				{ id: "a", text: "A" },
				{ id: "b", text: "B" }
			]);
		});

		it("keeps the order of the value", async () => {
			const $panel = panel();

			// the items arrive in a different order
			$panel.api.get.mockResolvedValueOnce([{ id: "b" }, { id: "a" }]);

			const wrapper = mount({ value: ["a", "b"] }, {}, $panel);
			await flushPromises();

			expect(items(wrapper)).toStrictEqual([{ id: "a" }, { id: "b" }]);

			await wrapper.setProps({ value: ["b", "a"] });
			await flushPromises();

			expect($panel.api.get).toHaveBeenCalledOnce();
			expect(items(wrapper)).toStrictEqual([{ id: "b" }, { id: "a" }]);
		});

		it("matches the items by their uuid", async () => {
			const a = { id: "a", uuid: "page://a" };
			const b = { id: "b", uuid: "page://b" };
			const $panel = panel();
			$panel.api.get.mockResolvedValueOnce([a]);

			const wrapper = mount({ value: ["page://a"] }, {}, $panel);
			await flushPromises();

			expect(items(wrapper)).toStrictEqual([a]);

			$panel.api.get.mockResolvedValueOnce([b]);
			await wrapper.setProps({ value: ["page://a", "page://b"] });
			await flushPromises();

			expect($panel.api.get).toHaveBeenLastCalledWith(endpoint, {
				items: ["page://b"]
			});
			expect(items(wrapper)).toStrictEqual([a, b]);
		});
	});

	// methods
	describe("onSort()", () => {
		it("emits the uuids or ids of the sorted items", () => {
			const wrapper = mount();
			wrapper
				.findComponent(Collection)
				.vm.$emit("sort", [{ id: "b", uuid: "page://b" }, { id: "a" }]);

			expect(wrapper.emitted("input")).toStrictEqual([[["page://b", "a"]]]);
		});
	});

	describe("remove()", () => {
		it("removes the item of the clicked button", async () => {
			const $panel = panel();
			$panel.api.get.mockResolvedValueOnce([
				{ id: "a" },
				{ id: "b" },
				{ id: "c" }
			]);

			const wrapper = mount({ value: ["a", "b", "c"] }, {}, $panel);
			await flushPromises();
			await wrapper.findAll("k-button")[1].trigger("click");

			expect(wrapper.emitted("input")).toStrictEqual([[["a", "c"]]]);
		});
	});
});
