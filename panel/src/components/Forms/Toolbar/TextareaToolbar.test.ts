import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TextareaToolbar from "./TextareaToolbar.vue";

type Button = {
	click: () => void;
	dropdown?: Button[];
	icon: string;
	shortcut?: string;
};

const Toolbar = {
	name: "k-toolbar",
	props: ["buttons"],
	methods: { close: vi.fn() },
	template: "<nav />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(TextareaToolbar, {
		props,
		attrs,
		global: {
			stubs: { "k-toolbar": Toolbar }
		}
	});
}

function layout(wrapper: ReturnType<typeof mount>): (Button | "|")[] {
	return wrapper.findComponent(Toolbar).props("buttons");
}

function icons(wrapper: ReturnType<typeof mount>) {
	return layout(wrapper).map((button) => (button === "|" ? "|" : button.icon));
}

function button(wrapper: ReturnType<typeof mount>, icon: string): Button {
	return layout(wrapper).find(
		(button) => button !== "|" && button.icon === icon
	) as Button;
}

describe("k-textarea-toolbar", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { ...window.panel, plugins: {} });
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-textarea-toolbar");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("buttons prop", () => {
		it("shows the default buttons", () => {
			const wrapper = mount();
			expect(icons(wrapper)).toStrictEqual([
				"title",
				"|",
				"bold",
				"italic",
				"code",
				"|",
				"url",
				"email",
				"attachment",
				"|",
				"list-bullet",
				"list-numbers"
			]);
		});

		it("shows the given buttons and separators", () => {
			const wrapper = mount({ buttons: ["italic", "|", "unknown", "bold"] });
			expect(icons(wrapper)).toStrictEqual(["italic", "|", "bold"]);
		});

		it("shows no buttons when turned off", () => {
			const wrapper = mount({ buttons: false });
			expect(layout(wrapper)).toStrictEqual([]);
		});
	});

	describe("uploads prop", () => {
		it("offers no upload without uploads", () => {
			const wrapper = mount();
			expect(button(wrapper, "attachment").dropdown).toBeUndefined();
		});

		it("offers to select or upload a file", () => {
			const wrapper = mount({ uploads: true });
			const file = button(wrapper, "attachment");
			file.dropdown?.[0].click();
			file.dropdown?.[1].click();
			expect(wrapper.emitted("command")).toStrictEqual([["file"], ["upload"]]);
		});
	});

	// computed
	describe("commands computed", () => {
		it.each([
			["bold", ["toggle", "**"]],
			["italic", ["toggle", "*"]],
			["code", ["toggle", "`"]],
			["url", ["dialog", "link"]],
			["email", ["dialog", "email"]],
			["attachment", ["file"]]
		])("emits the command of the %s button", (icon, command) => {
			const wrapper = mount();
			button(wrapper, icon).click();
			expect(wrapper.emitted("command")).toStrictEqual([command]);
		});

		it("prepends the headline markers", () => {
			const wrapper = mount();
			for (const heading of button(wrapper, "title").dropdown ?? []) {
				heading.click();
			}
			expect(wrapper.emitted("command")).toStrictEqual([
				["prepend", "#"],
				["prepend", "##"],
				["prepend", "###"]
			]);
		});

		it.each([
			["list-bullet", "- a\n- b"],
			["list-numbers", "1. a\n2. b"]
		])("turns the selected lines into a %s list", (icon, expected) => {
			const wrapper = mount();
			button(wrapper, icon).click();
			const [name, text] = wrapper.emitted("command")?.[0] as [
				string,
				(input: unknown, selection: string) => string
			];
			expect(name).toBe("insert");
			expect(text(null, "a\nb")).toBe(expected);
		});
	});

	describe("layout computed", () => {
		it("adds buttons from plugins", () => {
			const click = vi.fn(function (this: { command: (name: string) => void }) {
				this.command("highlight");
			});
			vi.stubGlobal("panel", {
				...window.panel,
				plugins: { textareaButtons: { highlight: { click, icon: "pen" } } }
			});
			const wrapper = mount({ buttons: ["highlight"] });
			button(wrapper, "pen").click();
			expect(wrapper.emitted("command")).toStrictEqual([["highlight"]]);
		});
	});

	// methods
	describe("close()", () => {
		it("closes the k-toolbar", () => {
			const wrapper = mount();
			wrapper.vm.close();
			expect(Toolbar.methods.close).toHaveBeenCalledOnce();
		});
	});

	describe("command()", () => {
		it("emits the command with its arguments", () => {
			const wrapper = mount();
			wrapper.vm.command("wrap", "<", ">");
			expect(wrapper.emitted("command")).toStrictEqual([["wrap", "<", ">"]]);
		});
	});

	describe("shortcut()", () => {
		it("runs the command of the shortcut", () => {
			const wrapper = mount();
			const event = new KeyboardEvent("keydown", { cancelable: true });
			wrapper.vm.shortcut("b", event);
			expect(wrapper.emitted("command")).toStrictEqual([["toggle", "**"]]);
			expect(event.defaultPrevented).toBe(true);
		});

		it("ignores keys without command", () => {
			const wrapper = mount();
			const event = new KeyboardEvent("keydown", { cancelable: true });
			wrapper.vm.shortcut("x", event);
			expect(wrapper.emitted("command")).toBeUndefined();
			expect(event.defaultPrevented).toBe(false);
		});
	});
});
