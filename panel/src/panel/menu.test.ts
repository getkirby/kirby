import { describe, expect, it, beforeEach, afterEach, vi } from "vitest";
import Menu from "./menu";

const createMenu = (isMobile = false) => {
	vi.spyOn(window, "matchMedia").mockReturnValue({
		matches: isMobile,
		addEventListener: vi.fn()
	} as unknown as MediaQueryList);

	const panel = { events: { on: vi.fn() } };
	return Menu(panel);
};

describe("panel.menu", () => {
	beforeEach(() => {
		localStorage.clear();
		document.body.innerHTML = "";
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("registers escape and click handlers on panel events", () => {
		const on = vi.fn();
		vi.spyOn(window, "matchMedia").mockReturnValue({
			matches: false,
			addEventListener: vi.fn()
		} as unknown as MediaQueryList);

		Menu({ events: { on } });

		expect(on).toHaveBeenCalledWith("keydown.esc", expect.any(Function));
		expect(on).toHaveBeenCalledWith("click", expect.any(Function));
	});

	describe("defaults()", () => {
		it("returns default state", () => {
			const menu = createMenu();
			expect(menu.defaults()).toStrictEqual({
				hover: false,
				isOpen: false,
				items: []
			});
		});
	});

	describe("blur()", () => {
		const createMenuWithDOM = (isMobile: boolean) => {
			document.body.innerHTML = `
				<div class="k-panel-menu"></div>
				<button class="k-panel-menu-proxy"></button>
			`;
			return createMenu(isMobile);
		};

		it("closes the menu when clicking outside on mobile", () => {
			const menu = createMenuWithDOM(true);
			menu.open();
			const outside = document.createElement("div");
			document.body.appendChild(outside);
			menu.blur({ target: outside } as unknown as Event);
			expect(menu.isOpen).toStrictEqual(false);
		});

		it("does not close when clicking inside the menu on mobile", () => {
			const menu = createMenuWithDOM(true);
			menu.open();
			const inside = document.querySelector(".k-panel-menu")!;
			menu.blur({ target: inside } as unknown as Event);
			expect(menu.isOpen).toStrictEqual(true);
		});

		it("does not close when clicking the toggle on mobile", () => {
			const menu = createMenuWithDOM(true);
			menu.open();
			const toggle = document.querySelector(".k-panel-menu-proxy")!;
			menu.blur({ target: toggle } as unknown as Event);
			expect(menu.isOpen).toStrictEqual(true);
		});

		it("does nothing on desktop", () => {
			const menu = createMenuWithDOM(false);
			menu.open();
			const outside = document.createElement("div");
			document.body.appendChild(outside);
			menu.blur({ target: outside } as unknown as Event);
			expect(menu.isOpen).toStrictEqual(true);
		});
	});

	describe("close()", () => {
		it("closes the menu", () => {
			const menu = createMenu();
			menu.open();
			menu.close();
			expect(menu.isOpen).toStrictEqual(false);
		});

		it("sets localStorage item on desktop", () => {
			const menu = createMenu(false);
			menu.close();
			expect(localStorage.getItem("kirby$menu")).toStrictEqual("true");
		});

		it("does not touch localStorage on mobile", () => {
			const menu = createMenu(true);
			menu.close();
			expect(localStorage.getItem("kirby$menu")).toStrictEqual(null);
		});
	});

	describe("escape()", () => {
		it("closes the menu on mobile", () => {
			const menu = createMenu(true);
			menu.open();
			menu.escape();
			expect(menu.isOpen).toStrictEqual(false);
		});

		it("does nothing on desktop", () => {
			const menu = createMenu(false);
			menu.open();
			menu.escape();
			expect(menu.isOpen).toStrictEqual(true);
		});
	});

	describe("open()", () => {
		it("opens the menu", () => {
			const menu = createMenu();
			menu.open();
			expect(menu.isOpen).toStrictEqual(true);
		});

		it("removes localStorage item on desktop", () => {
			localStorage.setItem("kirby$menu", "true");
			const menu = createMenu(false);
			menu.open();
			expect(localStorage.getItem("kirby$menu")).toStrictEqual(null);
		});

		it("does not touch localStorage on mobile", () => {
			localStorage.setItem("kirby$menu", "true");
			const menu = createMenu(true);
			menu.open();
			expect(localStorage.getItem("kirby$menu")).toStrictEqual("true");
		});
	});

	describe("resize()", () => {
		it("closes the menu on mobile", () => {
			const menu = createMenu(true);
			menu.open();
			menu.resize();
			expect(menu.isOpen).toStrictEqual(false);
		});

		it("opens the menu on desktop when no localStorage item", () => {
			const menu = createMenu(false);
			menu.resize();
			expect(menu.isOpen).toStrictEqual(true);
		});

		it("keeps menu closed on desktop when localStorage item is set", () => {
			localStorage.setItem("kirby$menu", "true");
			const menu = createMenu(false);
			menu.resize();
			expect(menu.isOpen).toStrictEqual(false);
		});
	});

	describe("set()", () => {
		it("sets items", () => {
			const menu = createMenu();
			const items = [{ label: "Home", link: "/" }, "-" as const];
			menu.set(items);
			expect(menu.items).toStrictEqual(items);
		});

		it("calls resize after setting items", () => {
			// on desktop with no localStorage item, resize opens the menu
			const menu = createMenu(false);
			menu.set([]);
			expect(menu.isOpen).toStrictEqual(true);
		});
	});

	describe("toggle()", () => {
		it("opens a closed menu", () => {
			const menu = createMenu();
			expect(menu.isOpen).toStrictEqual(false);
			menu.toggle();
			expect(menu.isOpen).toStrictEqual(true);
		});

		it("closes an open menu", () => {
			const menu = createMenu();
			menu.open();
			menu.toggle();
			expect(menu.isOpen).toStrictEqual(false);
		});
	});
});
