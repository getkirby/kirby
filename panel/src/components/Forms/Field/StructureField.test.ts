import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { nextTick } from "vue";
import StructureField from "./StructureField.vue";

function mount(props = {}) {
	return vueMount(StructureField, {
		props: {
			fields: {
				title: { type: "text" },
				label: { type: "text" }
			},
			...props
		},
		shallow: true,
		global: {
			mocks: {
				$events: { emit: vi.fn(), on: vi.fn(), off: vi.fn() },
				$helper: {
					object: {
						length: (object: object) => Object.keys(object ?? {}).length
					},
					uuid: () => "uuid"
				},
				$t: (key: string) => key
			}
		}
	});
}

function rows(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.items as Record<string, unknown>[];
}

describe("StructureField.vue", () => {
	// watch
	describe("value watcher", () => {
		it("converts the value to items", () => {
			const wrapper = mount({ value: [{ _id: "a", title: "A" }] });

			expect(wrapper.vm.items).toStrictEqual([{ _id: "a", title: "A" }]);
		});

		it("adds missing ids", () => {
			const wrapper = mount({ value: [{ title: "A" }] });

			expect(wrapper.vm.items).toStrictEqual([{ _id: "uuid", title: "A" }]);
		});

		it("syncs rows that are updated in place", async () => {
			// a new row gets added in the block drawer and its items
			// are passed on as value to the field in the block preview
			const drawer = mount();
			rows(drawer).push({ _id: "a", title: "", label: "" });

			const preview = mount({ value: drawer.vm.items });

			expect(rows(preview)[0].title).toBe("");

			// the row gets updated in place via the structure drawer
			rows(drawer)[0].title = "Title";
			await nextTick();

			expect(rows(preview)[0].title).toBe("Title");
		});

		it("keeps the selection when its own rows are updated in place", async () => {
			const wrapper = mount();
			const items = rows(wrapper);
			items.push({ _id: "a", title: "A" });

			await wrapper.setProps({ value: items });
			wrapper.vm.startSelecting();
			wrapper.vm.onSelect(["a"]);

			items[0].title = "B";
			await nextTick();

			expect(wrapper.vm.isSelecting).toBe(true);
			expect(wrapper.vm.selected).toStrictEqual(["a"]);
		});

		it("stops selecting when the rows get replaced", async () => {
			const wrapper = mount({ value: [{ _id: "a", title: "A" }] });
			wrapper.vm.startSelecting();

			await wrapper.setProps({ value: [{ _id: "b", title: "B" }] });

			expect(wrapper.vm.isSelecting).toBe(false);
		});

		it("keeps its own items when its own value is passed back", async () => {
			const wrapper = mount();
			const items = rows(wrapper);

			await wrapper.setProps({ value: items });
			items.push({ _id: "a", title: "A" });
			await nextTick();

			expect(wrapper.vm.items).toBe(items);
		});
	});
});
