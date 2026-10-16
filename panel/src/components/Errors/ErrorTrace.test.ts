import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ErrorTrace from "./ErrorTrace.vue";

function mount(
	props: Record<string, unknown> = {},
	attrs: Record<string, unknown> = {}
) {
	return vueMount(ErrorTrace, {
		props,
		attrs
	});
}

describe("ErrorTrace.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-error-trace");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("trace prop", () => {
		it("renders an item per trace entry", () => {
			const wrapper = mount({
				trace: [{ file: "a.php" }, { file: "b.php" }]
			});
			expect(wrapper.findAll("li")).toHaveLength(2);
		});

		it("links to the item url", () => {
			const wrapper = mount({
				trace: [{ file: "a.php", url: "vscode://file/a.php:1" }]
			});
			expect(wrapper.find("a").attributes("href")).toBe(
				"vscode://file/a.php:1"
			);
		});

		it("renders no href without url", () => {
			const wrapper = mount({ trace: [{ file: "a.php" }] });
			expect(wrapper.find("a").attributes("href")).toBeUndefined();
		});

		it("renders no href for a dangerous url", () => {
			const wrapper = mount({
				trace: [{ file: "a.php", url: "javascript:alert(1)" }]
			});
			expect(wrapper.find("a").attributes("href")).toBeUndefined();
		});
	});

	// methods
	describe("href()", () => {
		it("returns the url", () => {
			const wrapper = mount();
			expect(wrapper.vm.href("phpstorm://open?file=a.php")).toBe(
				"phpstorm://open?file=a.php"
			);
		});

		it("returns null for an empty url", () => {
			const wrapper = mount();
			expect(wrapper.vm.href(undefined)).toBeNull();
			expect(wrapper.vm.href("")).toBeNull();
		});

		it("returns null for dangerous schemes", () => {
			const wrapper = mount();
			expect(wrapper.vm.href("javascript:alert(1)")).toBeNull();
			expect(wrapper.vm.href(" java\nscript:alert(1)")).toBeNull();
			expect(wrapper.vm.href("data:text/html,<script>")).toBeNull();
		});
	});
});
