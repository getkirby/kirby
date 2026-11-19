import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import BlockTitle from "../Elements/BlockTitle.vue";
import Default from "./Default.vue";

const fieldset = {
	name: "Heading",
	tabs: {
		content: { fields: { text: { type: "writer" } } },
		seo: { fields: { level: { type: "select" } } }
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(Default, {
		props: { content: { text: "Hello" }, fieldset, ...props },
		attrs,
		global: {
			components: { "k-block-title": BlockTitle },
			mixins: [preserveListeners]
		}
	});
}

describe("k-block-type-default", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-title");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("shows the block title", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(BlockTitle).props()).toStrictEqual({
				content: { text: "Hello" },
				fieldset
			});
		});
	});

	// methods
	describe("field()", () => {
		it("finds a field in any tab", () => {
			const wrapper = mount();
			expect(wrapper.vm.field("text")).toStrictEqual({ type: "writer" });
			expect(wrapper.vm.field("level")).toStrictEqual({ type: "select" });
		});

		it("returns the fallback for a missing field", () => {
			const wrapper = mount();
			expect(wrapper.vm.field("alt", { type: "text" } as never)).toStrictEqual({
				type: "text"
			});
			expect(wrapper.vm.field("alt")).toBe(null);
		});

		it("returns the fallback without tabs", () => {
			const wrapper = mount({ fieldset: { name: "Line" } });
			expect(wrapper.vm.field("text", {} as never)).toStrictEqual({});
		});
	});

	describe("open()", () => {
		it("emits open", () => {
			const wrapper = mount();
			wrapper.vm.open();
			expect(wrapper.emitted("open")).toHaveLength(1);
		});
	});

	describe("update()", () => {
		it("emits the content with the changes", () => {
			const wrapper = mount({ content: { level: "h2", text: "Hello" } });
			wrapper.vm.update({ text: "Hi" });
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ level: "h2", text: "Hi" }]
			]);
		});
	});

	// events
	describe("events", () => {
		it("emits open on double click", async () => {
			const wrapper = mount();
			await wrapper.trigger("dblclick");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("keeps native events apart from block events", async () => {
			const onCopy = vi.fn();
			const wrapper = mount({}, { onCopy });
			await wrapper.trigger("copy");
			expect(onCopy).not.toHaveBeenCalled();
		});
	});
});
