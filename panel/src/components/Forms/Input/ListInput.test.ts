import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ListDoc from "@/components/Forms/Writer/Nodes/ListDoc.js";
import ListInput from "./ListInput.vue";

const Writer = {
	name: "k-writer-input",
	props: ["extensions", "marks", "nodes", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(ListInput, {
		props,
		attrs,
		global: {
			stubs: { "k-writer-input": Writer }
		}
	});
}

function writer(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Writer);
}

describe("k-list-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-list-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("marks prop", () => {
		it("passes the marks to the k-writer-input", () => {
			const wrapper = mount({ marks: ["bold"] });
			expect(writer(wrapper).props("marks")).toStrictEqual(["bold"]);
		});
	});

	describe("nodes prop", () => {
		it("offers both lists by default", () => {
			const wrapper = mount();
			expect(writer(wrapper).props("nodes")).toStrictEqual([
				"bulletList",
				"orderedList"
			]);
		});
	});

	describe("value prop", () => {
		it("passes the list to the k-writer-input", () => {
			const wrapper = mount({ value: "<ul><li>A</li></ul>" });
			expect(writer(wrapper).props("value")).toBe("<ul><li>A</li></ul>");
		});
	});

	// computed
	describe("listExtensions computed", () => {
		it("allows only lists in the document", () => {
			const wrapper = mount({ nodes: ["orderedList"] });
			const [doc] = writer(wrapper).props("extensions");
			expect(doc).toBeInstanceOf(ListDoc);
			expect(doc.options).toMatchObject({
				inline: true,
				nodes: ["orderedList"]
			});
		});
	});

	// watch
	describe("value watcher", () => {
		it("passes a new list to the k-writer-input", async () => {
			const wrapper = mount({ value: "<ul><li>A</li></ul>" });
			await wrapper.setProps({ value: "<ol><li>B</li></ol>" } as never);
			expect(writer(wrapper).props("value")).toBe("<ol><li>B</li></ol>");
		});

		it("keeps the list for the value it emitted", async () => {
			const wrapper = mount();
			writer(wrapper).vm.$emit("input", "<ul><li><p>A</p></li></ul>");
			await wrapper.setProps({ value: "<ul><li>A</li></ul>" } as never);
			expect(writer(wrapper).props("value")).toBe("<ul><li><p>A</p></li></ul>");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-writer-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Writer.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("onInput()", () => {
		it("emits the list without paragraphs", () => {
			const wrapper = mount();
			wrapper.vm.onInput("<ul><li><p>A</p></li><li><p>B</p></li></ul>");
			expect(wrapper.emitted("input")).toStrictEqual([
				["<ul><li>A</li><li>B</li></ul>"]
			]);
		});

		it("emits an empty string without a list", () => {
			const wrapper = mount({ value: "<ul><li>A</li></ul>" });
			wrapper.vm.onInput("<p>A</p>");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});

		it("emits an empty string for a list without text", () => {
			const wrapper = mount({ value: "<ul><li>A</li></ul>" });
			wrapper.vm.onInput("<ul><li><p> </p></li></ul>");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	// events
	describe("input event", () => {
		it("emits the list of the k-writer-input", () => {
			const wrapper = mount();
			writer(wrapper).vm.$emit("input", "<ol><li><p>A</p></li></ol>");
			expect(wrapper.emitted("input")).toStrictEqual([["<ol><li>A</li></ol>"]]);
		});
	});
});
