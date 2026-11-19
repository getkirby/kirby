import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { clone } from "@/helpers/object";
import ModelsFieldPreview from "./ModelsFieldPreview.vue";

const Tags = {
	name: "k-tags-field-preview",
	props: ["removable", "value"],
	emits: ["remove"],
	template: "<ul />"
};

const helper = {
	clone,
	items: vi.fn()
};

function mount(props = {}, attrs = {}) {
	return vueMount(ModelsFieldPreview, {
		props,
		attrs,
		global: {
			mocks: {
				$helper: helper,
				$panel: { language: { code: "en" }, urls: { api: "/api" } }
			},
			stubs: { "k-tags-field-preview": Tags }
		}
	});
}

describe("k-models-field-preview", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-models-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders the tags preview", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Tags).props()).toStrictEqual({
				removable: false,
				value: []
			});
		});
	});

	// props
	describe("html prop", () => {
		it("is deprecated", () => {
			const deprecated = vi.mocked(window.panel.deprecated);
			deprecated.mockClear();
			mount({ html: true });
			expect(deprecated).toHaveBeenCalledOnce();
		});
	});

	describe("removable prop", () => {
		it("makes the tags removable", () => {
			const wrapper = mount({ removable: true });
			expect(wrapper.findComponent(Tags).props("removable")).toBe(true);
		});
	});

	describe("value prop", () => {
		it("shows item objects without loading them", () => {
			const items = [{ id: "a", text: "A" }];
			const wrapper = mount({ value: items });
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual(items);
			expect(helper.items).not.toHaveBeenCalled();
		});

		it("shows a loading tag until an ID is loaded", async () => {
			helper.items.mockResolvedValueOnce([{ text: "A" }]);
			const wrapper = mount({
				field: { endpoints: { field: "pages/x/fields/related" } },
				value: ["page://a"]
			});

			expect(wrapper.vm.tags).toStrictEqual([
				{
					id: "page://a",
					image: { color: "var(--tag-color-disabled-text)", icon: "loader" }
				}
			]);

			await flushPromises();
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual([
				{ id: "page://a", text: "A" }
			]);
		});
	});

	// watch
	describe("value watcher", () => {
		it("drops the tags of removed items", async () => {
			const wrapper = mount({
				value: [
					{ id: "a", text: "A" },
					{ id: "b", text: "B" }
				]
			});
			await wrapper.setProps({ value: [{ id: "b", text: "B" }] } as never);
			expect(wrapper.vm.tags).toStrictEqual([{ id: "b", text: "B" }]);
		});
	});

	// methods
	describe("tag()", () => {
		it("returns the item as tag", () => {
			const wrapper = mount();
			const item = { id: "a", text: "A" };
			expect(wrapper.vm.tag(item)).toBe(item);
		});
	});

	describe("unknown()", () => {
		it("shows the ID as negative tag", () => {
			const wrapper = mount();
			expect(wrapper.vm.unknown("page://a")).toStrictEqual({
				id: "page://a",
				image: { icon: "alert" },
				text: "page://a",
				theme: "negative"
			});
		});
	});

	// events
	describe("events", () => {
		it("emits remove from the tags", () => {
			const wrapper = mount({ removable: true });
			wrapper.findComponent(Tags).vm.$emit("remove", 1);
			expect(wrapper.emitted("remove")).toStrictEqual([[1]]);
		});
	});
});
