import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { h } from "vue";
import Blocks from "./Blocks.vue";

function block(name: string) {
	return {
		name,
		emits: ["update"],
		inheritAttrs: false,
		render: () => h("div", { class: name })
	};
}

const DefaultBlock = block("k-block");
const CustomBlock = block("my-block");

function mount(props: Record<string, unknown> = {}) {
	return vueMount(Blocks, {
		global: {
			components: {
				"k-block": DefaultBlock,
				"k-draggable": {
					inheritAttrs: false,
					setup: (_, { slots }) => {
						return () => h("div", slots.default?.());
					}
				},
				"my-block": CustomBlock
			},
			mocks: {
				$events: { on: vi.fn(), off: vi.fn() }
			}
		},
		props: {
			fieldsets: { text: { name: "Text", type: "text" } },
			value: [
				{ id: "a", type: "text", content: { text: "A" } },
				{ id: "b", type: "text", content: { text: "B" } }
			],
			...props
		}
	});
}

describe("Blocks.vue", () => {
	describe("blockComponent", () => {
		it("renders each block with k-block by default", () => {
			const wrapper = mount();

			expect(wrapper.findAll(".k-block")).toHaveLength(2);
			expect(wrapper.findAll(".my-block")).toHaveLength(0);
		});

		it("renders each block with the given component", () => {
			const wrapper = mount({ blockComponent: "my-block" });

			expect(wrapper.findAll(".my-block")).toHaveLength(2);
			expect(wrapper.findAll(".k-block")).toHaveLength(0);
		});

		it("passes the block to the given component", () => {
			const wrapper = mount({ blockComponent: "my-block" });
			const attrs = wrapper.findComponent(CustomBlock).vm.$attrs;

			expect(attrs.id).toBe("a");
			expect(attrs.type).toBe("text");
			expect(attrs.content).toStrictEqual({ text: "A" });
		});

		it("listens to the events of the given component", () => {
			const wrapper = mount({ blockComponent: "my-block" });

			wrapper.findComponent(CustomBlock).vm.$emit("update", { text: "C" });

			const emitted = wrapper.emitted("input") as unknown[][];
			const [blocks] = emitted[0] as [{ content: { text: string } }[]];

			expect(emitted).toHaveLength(1);
			expect(blocks[0].content.text).toBe("C");
		});
	});
});
