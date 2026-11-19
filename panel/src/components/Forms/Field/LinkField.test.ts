import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LinkField from "./LinkField.vue";

function input(name: string) {
	return {
		name,
		props: ["disabled", "id", "pattern", "placeholder", "required", "value"],
		emits: ["input"],
		methods: { focus: vi.fn() },
		template: "<input />"
	};
}

const Button = {
	name: "k-button",
	props: ["disabled", "dropdown", "icon"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)"><slot /></button>`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};
const FileBrowser = {
	name: "k-file-browser",
	props: ["opened", "selected"],
	emits: ["select"],
	template: "<div />"
};
const Input = { name: "k-input", template: "<div><slot /></div>" };
const PageTree = {
	name: "k-page-tree",
	props: ["current", "root", "scrollable"],
	emits: ["select"],
	template: "<div />"
};
const Preview = {
	name: "k-link-field-preview",
	props: ["removable", "type", "value"],
	emits: ["remove"],
	template: `<div><slot name="placeholder" /></div>`
};
const EmailInput = input("k-email-input");
const TelInput = input("k-tel-input");
const TextInput = input("k-text-input");
const UrlInput = input("k-url-input");

const events = { off: vi.fn(), on: vi.fn() };

function mount(props = {}, attrs = {}) {
	return vueMount(LinkField, {
		props,
		attrs,
		attachTo: document.body,
		global: {
			mocks: {
				$events: events,
				$panel: { view: { props: { uuid: "page://current" } } }
			},
			stubs: {
				"k-button": Button,
				"k-dropdown": Dropdown,
				"k-email-input": EmailInput,
				"k-file-browser": FileBrowser,
				"k-input": Input,
				"k-link-field-preview": Preview,
				"k-page-tree": PageTree,
				"k-tel-input": TelInput,
				"k-text-input": TextInput,
				"k-url-input": UrlInput
			}
		}
	});
}

function toggle(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Button);
}

function types(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Dropdown).props("options");
}

describe("k-link-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-link-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("disabled prop", () => {
		it("disables the type toggle and the input", () => {
			const wrapper = mount({ disabled: true });
			expect(toggle(wrapper).props()).toMatchObject({
				disabled: true,
				dropdown: false
			});
			expect(wrapper.findComponent(UrlInput).props("disabled")).toBe(true);
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the input", () => {
			const wrapper = mount({ id: "link" });
			expect(wrapper.attributes("input")).toBe("link");
			expect(wrapper.findComponent(UrlInput).props("id")).toBe("link");
		});
	});

	describe("options prop", () => {
		it("offers all link types by default", () => {
			const wrapper = mount();
			expect(
				types(wrapper).map((type: { icon: string }) => type.icon)
			).toStrictEqual([
				"url",
				"page",
				"file",
				"email",
				"phone",
				"anchor",
				"title"
			]);
		});

		it("offers only the given link types", () => {
			const wrapper = mount({ options: ["email", "url"] });
			expect(
				types(wrapper).map((type: { icon: string }) => type.icon)
			).toStrictEqual(["email", "url"]);
		});

		it("offers no dropdown for a single link type", () => {
			const wrapper = mount({ options: ["url"] });
			expect(toggle(wrapper).props("dropdown")).toBe(false);
		});
	});

	describe("required prop", () => {
		it("requires the input", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.findComponent(UrlInput).props("required")).toBe(true);
		});
	});

	describe("value prop", () => {
		it("shows a URL in the URL input", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(toggle(wrapper).props("icon")).toBe("url");
			expect(wrapper.findComponent(UrlInput).props("value")).toBe(
				"https://getkirby.com"
			);
		});

		it("shows an email without its prefix", () => {
			const wrapper = mount({ value: "mailto:mail@getkirby.com" });
			expect(toggle(wrapper).props("icon")).toBe("email");
			expect(wrapper.findComponent(EmailInput).props("value")).toBe(
				"mail@getkirby.com"
			);
		});

		it("checks a phone number with the pattern of its type", () => {
			const wrapper = mount({ value: "tel:+4930123" });
			const tel = wrapper.findComponent(TelInput);
			expect(tel.props("value")).toBe("+4930123");
			expect(tel.props("pattern")).toBe("[+]{0,1}[0-9]+");
		});

		it("shows a page as preview", () => {
			const wrapper = mount({ value: "page://a" });
			expect(wrapper.findComponent(Preview).props()).toMatchObject({
				removable: true,
				type: "page",
				value: "page://a"
			});
			expect(wrapper.find("input").exists()).toBe(false);
		});

		it("starts with the first link type without value", () => {
			const wrapper = mount({ options: ["email", "url"] });
			expect(wrapper.findComponent(EmailInput).props("value")).toBe("");
		});
	});

	// computed
	describe("activeTypesOptions computed", () => {
		it("marks the current link type", () => {
			const wrapper = mount({ value: "mailto:mail@getkirby.com" });
			const current = types(wrapper).filter(
				(type: { current: boolean }) => type.current
			);
			expect(current.map((type: { icon: string }) => type.icon)).toStrictEqual([
				"email"
			]);
		});

		it("switches the link type on click", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			types(wrapper)[3].click();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(EmailInput).exists()).toBe(true);
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("currentType computed", () => {
		it("falls back to the first link type", () => {
			const wrapper = mount({ options: ["url"], value: "mailto:a@b.de" });
			expect(wrapper.vm.currentType.id).toBe("url");
		});
	});

	// watch
	describe("value watcher", () => {
		it("shows a new value from outside", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			await wrapper.setProps({ value: "mailto:mail@getkirby.com" } as never);
			expect(wrapper.findComponent(EmailInput).props("value")).toBe(
				"mail@getkirby.com"
			);
		});

		it("keeps the link type when its own value comes back", async () => {
			const wrapper = mount();
			await wrapper.vm.switchType("custom");
			wrapper.vm.onInput("https://getkirby.com");
			await wrapper.setProps({ value: "https://getkirby.com" } as never);
			expect(wrapper.vm.currentType.id).toBe("custom");
		});

		it("resets to the first link type for an empty value", async () => {
			const wrapper = mount({ value: "mailto:mail@getkirby.com" });
			await wrapper.setProps({ value: "" } as never);
			expect(wrapper.findComponent(UrlInput).props("value")).toBe("");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("listens to clicks until unmounted", () => {
			const wrapper = mount();
			const handler = wrapper.vm.onOutsideClick;
			expect(events.on).toHaveBeenCalledWith("click", handler);
			wrapper.unmount();
			expect(events.off).toHaveBeenCalledWith("click", handler);
		});
	});

	// methods
	describe("clear()", () => {
		it("empties the input and emits an empty value", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			wrapper.vm.clear();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(UrlInput).props("value")).toBe("");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(UrlInput.methods.focus).toHaveBeenCalledOnce();
		});

		it("does nothing for a page", () => {
			const wrapper = mount({ value: "page://a" });
			expect(() => wrapper.vm.focus()).not.toThrow();
		});
	});

	describe("onInput()", () => {
		it("emits the value of the link type", () => {
			const wrapper = mount({ value: "mailto:a@b.de" });
			wrapper.vm.onInput(" mail@getkirby.com ");
			expect(wrapper.emitted("input")).toStrictEqual([
				["mailto:mail@getkirby.com"]
			]);
		});

		it("emits an empty value for an empty link", () => {
			const wrapper = mount({ value: "mailto:a@b.de" });
			wrapper.vm.onInput("  ");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("onOutsideClick()", () => {
		it("closes the browser for a click outside", async () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.toggle();
			wrapper.vm.onOutsideClick({ target: document.body });
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(PageTree).exists()).toBe(false);
		});

		it("keeps the browser open for a click inside", async () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.toggle();
			wrapper.vm.onOutsideClick({ target: wrapper.element });
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(PageTree).exists()).toBe(true);
		});
	});

	describe("removeModel()", () => {
		it("clears the value and closes the browser", async () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.toggle();
			wrapper.vm.removeModel();
			await wrapper.vm.$nextTick();
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
			expect(wrapper.findComponent(PageTree).exists()).toBe(false);
		});
	});

	describe("selectModel()", () => {
		it("emits the UUID of the model", () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.selectModel({ url: "/b", uuid: "page://b" });
			expect(wrapper.emitted("input")).toStrictEqual([["page://b"]]);
		});

		it("links the URL of a model without UUID", async () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.selectModel({ url: "https://getkirby.com/b" });
			await wrapper.vm.$nextTick();
			expect(wrapper.emitted("input")).toStrictEqual([
				[""],
				["https://getkirby.com/b"]
			]);
			expect(wrapper.findComponent(UrlInput).exists()).toBe(true);
		});
	});

	describe("switchType()", () => {
		it("opens the browser for pages", async () => {
			const wrapper = mount();
			await wrapper.vm.switchType("page");
			expect(wrapper.findComponent(PageTree).exists()).toBe(true);
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});

		it("focuses the input of another link type", async () => {
			const wrapper = mount();
			await wrapper.vm.switchType("email");
			expect(EmailInput.methods.focus).toHaveBeenCalledOnce();
		});

		it("keeps the value for the current link type", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			await wrapper.vm.switchType("url");
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("toggle()", () => {
		it("opens and closes the file browser", async () => {
			const wrapper = mount({ value: "file://a" });
			wrapper.vm.toggle();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(FileBrowser).props()).toMatchObject({
				opened: "page://current",
				selected: "file://a"
			});
			wrapper.vm.toggle();
			await wrapper.vm.$nextTick();
			expect(wrapper.findComponent(FileBrowser).exists()).toBe(false);
		});
	});

	// events
	describe("click event", () => {
		it("opens the link types with the type toggle", async () => {
			const wrapper = mount();
			await toggle(wrapper).trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});

		it("toggles the browser for a single link type", async () => {
			const wrapper = mount({ options: ["page"] });
			await toggle(wrapper).trigger("click");
			expect(wrapper.findComponent(PageTree).props("current")).toBe("");
		});

		it("toggles the browser with a click on the page", async () => {
			const wrapper = mount({ value: "page://a" });
			await wrapper.find(".k-link-input-model").trigger("click");
			expect(wrapper.findComponent(PageTree).props("current")).toBe("page://a");
		});
	});

	describe("input event", () => {
		it("emits the typed link", () => {
			const wrapper = mount();
			wrapper.findComponent(UrlInput).vm.$emit("input", "https://getkirby.com");
			expect(wrapper.emitted("input")).toStrictEqual([
				["https://getkirby.com"]
			]);
		});
	});

	describe("remove event", () => {
		it("clears the selected page", () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.findComponent(Preview).vm.$emit("remove");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("select event", () => {
		it("emits the page selected in the tree", async () => {
			const wrapper = mount({ value: "page://a" });
			wrapper.vm.toggle();
			await wrapper.vm.$nextTick();
			wrapper.findComponent(PageTree).vm.$emit("select", { uuid: "page://b" });
			expect(wrapper.emitted("input")).toStrictEqual([["page://b"]]);
		});

		it("emits the file selected in the browser", async () => {
			const wrapper = mount({ value: "file://a" });
			wrapper.vm.toggle();
			await wrapper.vm.$nextTick();
			wrapper
				.findComponent(FileBrowser)
				.vm.$emit("select", { uuid: "file://b" });
			expect(wrapper.emitted("input")).toStrictEqual([["file://b"]]);
		});
	});
});
