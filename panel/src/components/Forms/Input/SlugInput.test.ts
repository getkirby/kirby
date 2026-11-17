import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import SlugInput from "./SlugInput.vue";
import StringInput from "./StringInput.vue";

function panel(rules = {}) {
	return {
		language: { rules },
		system: { ascii: { ß: "ss" }, slugs: { ö: "oe" } }
	};
}

function mount(props = {}, $panel = panel()) {
	return vueMount(SlugInput, {
		props,
		// focus and selection only work on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-string-input": StringInput },
			directives: { direction: {} },
			mocks: { $panel }
		}
	});
}

describe("k-slug-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-slug-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});

		it("turns off autocomplete and spell checking", () => {
			const wrapper = mount({ autocomplete: "on", spellcheck: true });
			expect(wrapper.attributes("autocomplete")).toBe("off");
			expect(wrapper.attributes("spellcheck")).toBe("false");
		});
	});

	// props
	describe("allow prop", () => {
		it("replaces other characters by default", () => {
			const wrapper = mount({ value: "file.txt" });
			expect(wrapper.element.value).toBe("file-txt");
		});

		it("keeps the allowed characters", () => {
			const wrapper = mount({ allow: "a-z0-9.", value: "file.txt" });
			expect(wrapper.element.value).toBe("file.txt");
		});
	});

	describe("value prop", () => {
		it("shows the value as slug", () => {
			const wrapper = mount({ value: "Hello World" });
			expect(wrapper.element.value).toBe("hello-world");
		});
	});

	// watch
	describe("formData watcher", () => {
		// a value that matches the synced field keeps the
		// value watcher from undoing the sync on mount
		function synced(props = {}) {
			return mount({
				formData: { title: "Hello" },
				sync: "title",
				value: "hello",
				...props
			});
		}

		it("emits the slug when the synced field changes", async () => {
			const wrapper = synced();
			await wrapper.setProps({ formData: { title: "Hello World" } } as never);
			expect(wrapper.emitted("input")?.at(-1)).toStrictEqual(["hello-world"]);
		});

		it("ignores changes to other fields", async () => {
			const wrapper = synced();
			const count = wrapper.emitted("input")?.length;
			await wrapper.setProps({
				formData: { text: "Text", title: "Hello" }
			} as never);
			expect(wrapper.emitted("input")?.length).toBe(count);
		});

		it("ignores a missing synced field", () => {
			const wrapper = mount({ formData: { text: "Text" }, sync: "title" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("does not sync without a synced field", () => {
			const wrapper = mount({ formData: { title: "Hello" } });
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("does not sync when disabled", async () => {
			const wrapper = synced({ disabled: true });
			await wrapper.setProps({ formData: { title: "Hello World" } } as never);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("value watcher", () => {
		it("emits the slug of a new value", async () => {
			const wrapper = mount({ value: "hello" });
			await wrapper.setProps({ value: "Hello World" } as never);
			expect(wrapper.emitted("input")).toStrictEqual([["hello-world"]]);
			expect(wrapper.element.value).toBe("hello-world");
		});

		it("emits nothing when the slug stays the same", async () => {
			const wrapper = mount({ value: "hello" });
			await wrapper.setProps({ value: "Hello" } as never);
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("emits nothing for a slug on mount", () => {
			const wrapper = mount({ value: "hello-world" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.element);
		});
	});

	describe("onInput()", () => {
		it("emits the slug of the value", async () => {
			const wrapper = mount();
			wrapper.vm.onInput("Hello World");
			await wrapper.vm.$nextTick();
			expect(wrapper.emitted("input")).toStrictEqual([["hello-world"]]);
			expect(wrapper.element.value).toBe("hello-world");
		});
	});

	describe("sluggify()", () => {
		it("applies the slug rules of the language", () => {
			const wrapper = mount({}, panel({ ö: "o" }));
			expect(wrapper.vm.sluggify("Öl")).toBe("ol");
		});

		it("falls back to the slug rules of the system", () => {
			const wrapper = mount();
			expect(wrapper.vm.sluggify("Öl")).toBe("oel");
		});

		it("applies the ascii rules", () => {
			const wrapper = mount();
			expect(wrapper.vm.sluggify("Straße")).toBe("strasse");
		});
	});

	// events
	describe("input event", () => {
		// the value watcher turns it into a slug once it comes back as value
		it("emits the typed value as is", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("Hello W");
			expect(wrapper.emitted("input")).toStrictEqual([["Hello W"]]);
		});
	});
});
