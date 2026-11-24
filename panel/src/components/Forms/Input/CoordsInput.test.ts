import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import CoordsInput from "./CoordsInput.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(CoordsInput, {
		props: { value: { x: 20, y: 40 }, ...props },
		attrs,
		slots,
		// focus only works on connected elements
		attachTo: document.body
	});
}

function thumb(wrapper: ReturnType<typeof mount>) {
	return wrapper.find(".k-coords-input-thumb");
}

// happy-dom has no layout, so the box gets a fixed size
function box(wrapper: ReturnType<typeof mount>) {
	const rect = vi.spyOn(wrapper.element, "getBoundingClientRect");
	rect.mockReturnValue({
		bottom: 120,
		height: 100,
		left: 10,
		right: 210,
		top: 20,
		width: 200,
		x: 10,
		y: 20,
		toJSON: () => ({})
	});
}

describe("k-coords-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-coords-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("positions the thumb", () => {
			const button = thumb(mount()).element as HTMLElement;
			expect(button.tagName).toBe("BUTTON");
			expect(button.style.left).toBe("20%");
			expect(button.style.top).toBe("40%");
		});

		it("keeps the coordinates in a hidden input", () => {
			const input = mount().find("input");
			expect(input.attributes("aria-hidden")).toBe("true");
			expect(input.attributes("tabindex")).toBe("-1");
			expect(input.element.value).toBe("20,40");
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the thumb", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("aria-disabled")).toBe("true");
			expect(thumb(wrapper).attributes("disabled")).toBe("");
		});

		it("ignores the arrow keys", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("keydown", { key: "ArrowRight" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("id prop", () => {
		it("sets the id of the thumb", () => {
			const wrapper = mount({ id: "focus" });
			expect(thumb(wrapper).attributes("id")).toBe("focus");
		});
	});

	describe("name prop", () => {
		it("names the hidden input", () => {
			const wrapper = mount({ name: "focus" });
			expect(wrapper.find("input").attributes("name")).toBe("focus");
		});
	});

	describe("required prop", () => {
		it("requires the hidden input", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("input").attributes("required")).toBe("");
		});
	});

	describe("value prop", () => {
		it("is not empty with coordinates", () => {
			expect(mount().attributes("data-empty")).toBe("false");
		});

		it("is empty without value", () => {
			const wrapper = mount({ value: null });
			const button = thumb(wrapper).element as HTMLElement;
			expect(wrapper.attributes("data-empty")).toBe("true");
			expect(wrapper.find("input").element.value).toBe("");
			expect(button.style.left).toBe("0%");
			expect(button.style.top).toBe("0%");
		});
	});

	// watch
	describe("value watcher", () => {
		it("moves the thumb", async () => {
			const wrapper = mount();
			await wrapper.setProps({ value: { x: 75, y: 5 } } as never);
			const button = thumb(wrapper).element as HTMLElement;
			expect(button.style.left).toBe("75%");
			expect(button.style.top).toBe("5%");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the thumb", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(thumb(wrapper).element);
		});
	});

	describe("getCoords()", () => {
		it("returns the position in the box", () => {
			const wrapper = mount();
			const bounds = { height: 100, left: 10, top: 20, width: 200 } as DOMRect;
			expect(
				wrapper.vm.getCoords({ clientX: 60, clientY: 45 }, bounds)
			).toStrictEqual({ x: 50, y: 25 });
		});

		it("keeps the position in the box", () => {
			const wrapper = mount();
			const bounds = { height: 100, left: 10, top: 20, width: 200 } as DOMRect;
			expect(
				wrapper.vm.getCoords({ clientX: 0, clientY: 500 }, bounds)
			).toStrictEqual({ x: 0, y: 100 });
		});
	});

	describe("parse()", () => {
		it("returns coordinates as they are", () => {
			const value = { x: 1, y: 2 };
			expect(mount().vm.parse(value)).toBe(value);
		});

		it("reads keywords", () => {
			const wrapper = mount();
			expect(wrapper.vm.parse("top left")).toStrictEqual({ x: 0, y: 0 });
			expect(wrapper.vm.parse("center")).toStrictEqual({ x: 50, y: 50 });
			expect(wrapper.vm.parse("bottom right")).toStrictEqual({
				x: 100,
				y: 100
			});
		});
	});

	// events
	describe("events", () => {
		it("moves by one step with an arrow key", async () => {
			const wrapper = mount();
			await wrapper.trigger("keydown", { key: "ArrowRight" });
			await wrapper.trigger("keydown", { key: "ArrowUp" });
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ x: 21, y: 40 }],
				[{ x: 21, y: 39 }]
			]);
		});

		it("moves by ten steps with shift", async () => {
			const wrapper = mount();
			await wrapper.trigger("keydown", { key: "ArrowLeft", shiftKey: true });
			await wrapper.trigger("keydown", { key: "ArrowDown", shiftKey: true });
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ x: 10, y: 40 }],
				[{ x: 10, y: 50 }]
			]);
		});

		it("stays within the box", async () => {
			const wrapper = mount({ value: { x: 95, y: 3 } });
			await wrapper.trigger("keydown", { key: "ArrowRight", shiftKey: true });
			await wrapper.trigger("keydown", { key: "ArrowUp", shiftKey: true });
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ x: 100, y: 3 }],
				[{ x: 100, y: 0 }]
			]);
		});

		it("ignores other keys", async () => {
			const wrapper = mount();
			await wrapper.trigger("keydown", { key: "a" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("moves the thumb to the click and focuses it", async () => {
			const wrapper = mount();
			box(wrapper);
			await wrapper.trigger("click", { clientX: 60, clientY: 45 });
			expect(wrapper.emitted("input")).toStrictEqual([[{ x: 25, y: 25 }]]);
			expect(document.activeElement).toBe(thumb(wrapper).element);
		});

		it("follows the mouse while dragging", async () => {
			const wrapper = mount();
			box(wrapper);
			await wrapper.trigger("mousedown", { button: 0 });
			window.dispatchEvent(
				new MouseEvent("mousemove", { clientX: 110, clientY: 70 })
			);
			window.dispatchEvent(new MouseEvent("mouseup"));
			window.dispatchEvent(
				new MouseEvent("mousemove", { clientX: 10, clientY: 20 })
			);
			expect(wrapper.emitted("input")).toStrictEqual([[{ x: 50, y: 50 }]]);
		});

		it("drags only with the main button", async () => {
			const wrapper = mount();
			box(wrapper);
			const add = vi.spyOn(window, "addEventListener");
			onTestFinished(() => add.mockRestore());
			await wrapper.trigger("mousedown", { button: 2 });
			expect(add).not.toHaveBeenCalled();
		});

		it("removes the value on delete", async () => {
			const wrapper = mount();
			await thumb(wrapper).trigger("keydown", { key: "Delete" });
			expect(wrapper.emitted("input")).toStrictEqual([[null]]);
		});

		it("keeps a required value on delete", async () => {
			const wrapper = mount({ required: true });
			await thumb(wrapper).trigger("keydown", { key: "Delete" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("keeps the value on delete without reset", async () => {
			const wrapper = mount({ reset: false });
			await thumb(wrapper).trigger("keydown", { key: "Backspace" });
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content below the thumb", () => {
			const wrapper = mount({}, {}, { default: `<img src="a.jpg" />` });
			expect(wrapper.element.firstElementChild?.tagName).toBe("IMG");
		});
	});
});
