import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import SlugField from "./SlugField.vue";

const Field = {
	name: "k-field",
	props: ["help"],
	template: `<div>
		<div v-if="$slots.options" class="options"><slot name="options" /></div>
		<slot />
	</div>`
};
const Input = {
	name: "k-input",
	props: ["icon", "id", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};
const wizard = { field: "Title", text: "From title" };

function mount(props = {}, attrs = {}) {
	return vueMount(SlugField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field(props = {}) {
	return vueMount(SlugField, {
		props,
		global: {
			stubs: { "k-field": Field, "k-input": Input }
		}
	});
}

describe("k-slug-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-slug-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a slug k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("slug");
		});
	});

	// props
	describe("icon prop", () => {
		it("defaults to url", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("icon")).toBe("url");
		});

		it("passes the icon to the k-input", () => {
			const wrapper = mount({ icon: "page" });
			expect(wrapper.findComponent(Input).props("icon")).toBe("page");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "slug" });
			expect(wrapper.attributes("input")).toBe("slug");
			expect(wrapper.findComponent(Input).props("id")).toBe("slug");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "hello-world" });
			expect(wrapper.findComponent(Input).props("value")).toBe("hello-world");
		});
	});

	describe("wizard prop", () => {
		it("renders no wizard button by default", () => {
			const wrapper = field();
			expect(wrapper.find(".options").exists()).toBe(false);
		});

		it("renders a wizard button with the text", () => {
			const wrapper = field({ wizard });
			const button = wrapper.find(".options k-button");
			expect(button.attributes("text")).toBe("From title");
			expect(button.attributes("icon")).toBe("sparkling");
		});

		it("renders no wizard button without text", () => {
			const wrapper = field({ wizard: { field: "title" } });
			expect(wrapper.find(".options").exists()).toBe(false);
		});

		it("fills in the wizard field on click", async () => {
			const wrapper = field({ formData: { title: "Hello World" }, wizard });
			await wrapper.find(".options k-button").trigger("click");
			expect(wrapper.findComponent(Input).props("value")).toBe("Hello World");
		});
	});

	// computed
	describe("preview computed", () => {
		it("prefers the help text", () => {
			const wrapper = mount({ help: "Some help", path: "/blog/" });
			expect(wrapper.attributes("help")).toBe("Some help");
		});

		it("adds the value to the path", () => {
			const wrapper = mount({ path: "/blog/", value: "hello-world" });
			expect(wrapper.attributes("help")).toBe("/blog/hello-world");
		});

		it("is null without help and path", () => {
			const wrapper = mount({ value: "hello-world" });
			expect(wrapper.vm.preview).toBe(null);
		});

		it("shows the path without value", () => {
			const wrapper = mount({ path: "/blog/" });
			expect(wrapper.attributes("help")).toBe("/blog/");
		});
	});

	// watch
	describe("value watcher", () => {
		it("passes a new value to the k-input", async () => {
			const wrapper = mount({ value: "hello" });
			await wrapper.setProps({ value: "hello-world" } as never);
			expect(wrapper.findComponent(Input).props("value")).toBe("hello-world");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("onWizard()", () => {
		it("passes the value of the wizard field to the k-input", async () => {
			const wrapper = mount({ formData: { title: "Hello World" }, wizard });
			wrapper.vm.onWizard();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(Input).props("value")).toBe("Hello World");
		});

		it("keeps the value for an empty wizard field", async () => {
			const wrapper = mount({
				formData: { title: "" },
				value: "hello",
				wizard
			});
			wrapper.vm.onWizard();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(Input).props("value")).toBe("hello");
		});

		it("keeps the value without wizard field", async () => {
			const wrapper = mount({
				formData: { title: "Hello World" },
				value: "hello",
				wizard: { text: "From title" }
			});
			wrapper.vm.onWizard();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(Input).props("value")).toBe("hello");
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "hello-world");
			expect(wrapper.emitted("input")).toStrictEqual([["hello-world"]]);
		});
	});
});
