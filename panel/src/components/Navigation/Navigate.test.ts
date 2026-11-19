import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Navigate from "./Navigate.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Navigate, {
		props,
		attrs,
		slots: {
			default: `
				<button>A</button>
				<button disabled>B</button>
				<a href="#c">C</a>
				<label><input class="d" />D</label>
				<button>E</button>
			`
		},
		// focus only works on connected elements
		attachTo: document.body
	});
}

function focused(wrapper: ReturnType<typeof mount>) {
	return wrapper.element.contains(document.activeElement)
		? document.activeElement?.textContent
		: null;
}

async function press(wrapper: ReturnType<typeof mount>, key: string) {
	const event = new KeyboardEvent("keydown", { cancelable: true, key });
	wrapper.element.dispatchEvent(event);
	return event;
}

describe("k-navigate", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-navigate");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("axis prop", () => {
		it("moves with all arrow keys by default", async () => {
			const wrapper = mount();
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowRight");
			expect(focused(wrapper)).toBe("C");
			await press(wrapper, "ArrowDown");
			expect(focused(wrapper)).toBe("E");
			await press(wrapper, "ArrowUp");
			expect(focused(wrapper)).toBe("C");
			await press(wrapper, "ArrowLeft");
			expect(focused(wrapper)).toBe("A");
		});

		it("moves horizontally", async () => {
			const wrapper = mount({ axis: "x" });
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowDown");
			expect(focused(wrapper)).toBe("A");
			await press(wrapper, "ArrowRight");
			expect(focused(wrapper)).toBe("C");
			await press(wrapper, "ArrowLeft");
			expect(focused(wrapper)).toBe("A");
		});

		it("moves vertically", async () => {
			const wrapper = mount({ axis: "y" });
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowRight");
			expect(focused(wrapper)).toBe("A");
			await press(wrapper, "ArrowDown");
			expect(focused(wrapper)).toBe("C");
			await press(wrapper, "ArrowUp");
			expect(focused(wrapper)).toBe("A");
		});

		it("prevents the default of the arrow keys", async () => {
			const wrapper = mount();
			wrapper.vm.focus(0);
			expect((await press(wrapper, "ArrowDown")).defaultPrevented).toBe(true);
			expect((await press(wrapper, "Enter")).defaultPrevented).toBe(false);
		});
	});

	describe("disabled prop", () => {
		it("ignores the arrow keys", async () => {
			const wrapper = mount({ disabled: true });
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowDown");
			expect(focused(wrapper)).toBe("A");
		});
	});

	describe("element prop", () => {
		it("renders the element", () => {
			expect(mount({ element: "nav" }).element.tagName).toBe("NAV");
		});
	});

	describe("select prop", () => {
		it("skips disabled buttons by default", async () => {
			const wrapper = mount();
			wrapper.vm.focus(1);
			expect(focused(wrapper)).toBe("C");
		});

		it("moves between the selected elements", async () => {
			const wrapper = mount({ select: "input, button:not(:disabled)" });
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowDown");
			expect(document.activeElement?.classList.contains("d")).toBe(true);
		});
	});

	// lifecycle
	describe("unmounted hook", () => {
		it("stops listening to the keys", () => {
			const wrapper = mount();
			const remove = vi.spyOn(wrapper.element, "removeEventListener");
			onTestFinished(() => remove.mockRestore());
			const keydown = wrapper.vm.keydown;
			wrapper.unmount();
			expect(remove).toHaveBeenCalledWith("keydown", keydown);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the first element by default", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(focused(wrapper)).toBe("A");
		});

		it("focuses an element by index", () => {
			const wrapper = mount();
			wrapper.vm.focus(2);
			expect(focused(wrapper)).toBe("E");
		});

		it("focuses the first or last element", () => {
			const wrapper = mount();
			wrapper.vm.focus("last");
			expect(focused(wrapper)).toBe("E");
			wrapper.vm.focus("first");
			expect(focused(wrapper)).toBe("A");
		});
	});

	describe("move()", () => {
		it("counts an element with focus inside", async () => {
			const wrapper = mount({
				select: ":where(button, a, label):not(:disabled)"
			});
			(wrapper.find("input").element as HTMLInputElement).focus();
			wrapper.vm.move("prev");
			expect(focused(wrapper)).toBe("C");
		});
	});

	// events
	describe("events", () => {
		it("emits prev before the first element", async () => {
			const wrapper = mount();
			wrapper.vm.focus(0);
			await press(wrapper, "ArrowUp");
			expect(wrapper.emitted("prev")).toHaveLength(1);
			expect(focused(wrapper)).toBe("A");
		});

		it("emits next after the last element", async () => {
			const wrapper = mount();
			wrapper.vm.focus("last");
			await press(wrapper, "ArrowDown");
			expect(wrapper.emitted("next")).toHaveLength(1);
			expect(focused(wrapper)).toBe("E");
		});
	});
});
