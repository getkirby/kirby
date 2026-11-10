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

describe("k-error-trace", () => {
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

		it("renders the file and line", () => {
			const wrapper = mount({
				trace: [{ file: "a.php", line: 12 }, { file: "b.php" }]
			});
			const files = wrapper.findAll("li p").map((p) => p.text());

			expect(files).toStrictEqual(["a.php:12", "b.php"]);
		});

		it("renders Internal without a file", () => {
			const wrapper = mount({ trace: [{ function: "array_map" }] });
			expect(wrapper.find("p").text()).toBe("Internal");
		});

		it("renders the function with its class", () => {
			const wrapper = mount({
				trace: [{ class: "Kirby\\Cms\\App", file: "a.php", function: "render" }]
			});
			const call = wrapper.findAll("p")[1].findAll("span");

			expect(call.map((span) => span.text())).toStrictEqual([
				"Kirby\\Cms\\App",
				"::",
				"render",
				"()"
			]);
		});

		it("renders the function with a custom call type", () => {
			const wrapper = mount({
				trace: [
					{
						class: "Kirby\\Cms\\App",
						file: "a.php",
						function: "render",
						type: "->"
					}
				]
			});
			const call = wrapper.findAll("p")[1].findAll("span");

			expect(call.map((span) => span.text())).toStrictEqual([
				"Kirby\\Cms\\App",
				"->",
				"render",
				"()"
			]);
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
