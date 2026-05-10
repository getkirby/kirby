import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ValidationIssues from "./ValidationIssues.vue";

function mount(
	props: Record<string, unknown> = {},
	attrs: Record<string, unknown> = {}
) {
	return vueMount(ValidationIssues, {
		props,
		attrs,
		shallow: true
	});
}

describe("ValidationIssues.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) => mount({}, attrs);

		it.rendersAs(component, "K-DEFINITIONS", "k-validation-issues");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("fields prop", () => {
		it("renders a k-definition for each field", () => {
			const wrapper = mount({
				fields: {
					title: { label: "Title", issues: {} },
					slug: { label: "Slug", issues: {} }
				}
			});
			expect(wrapper.findAll("k-definition")).toHaveLength(2);
		});

		it("uses field label as the definition term", () => {
			const wrapper = mount({
				fields: {
					title: { label: "Page Title", issues: {} }
				}
			});
			expect(wrapper.find("k-definition").attributes("term")).toBe(
				"Page Title"
			);
		});

		it("renders issues from field.issues", () => {
			const wrapper = mount({
				fields: {
					title: {
						label: "Title",
						issues: {
							required: "Title is required",
							minLength: "Title is too short"
						}
					}
				}
			});
			const items = wrapper.findAll("li");
			expect(items).toHaveLength(2);
			expect(items[0].text()).toBe("Title is required");
			expect(items[1].text()).toBe("Title is too short");
		});

		it("falls back to field.message when issues is absent", () => {
			const wrapper = mount({
				fields: {
					slug: {
						label: "Slug",
						message: ["Slug must be lowercase", "Slug is too long"]
					}
				}
			});
			const items = wrapper.findAll("li");
			expect(items).toHaveLength(2);
			expect(items[0].text()).toBe("Slug must be lowercase");
			expect(items[1].text()).toBe("Slug is too long");
		});

		it("renders no definitions when fields is empty", () => {
			const wrapper = mount({ fields: {} });
			expect(wrapper.findAll("k-definition")).toHaveLength(0);
		});
	});

	// checklist
	describe("checklist", () => {
		it("uses negative theme", () => {
			const wrapper = mount({
				fields: {
					title: { label: "Title", issues: { required: "Title is required" } }
				}
			});
			expect(wrapper.find("k-checklist").attributes("theme")).toBe("negative");
		});
	});
});
