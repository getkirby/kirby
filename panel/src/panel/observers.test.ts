import { afterEach, describe, expect, it, vi } from "vitest";
import Observers from "./observers";

describe("panel.observers", () => {
	afterEach(() => {
		vi.unstubAllGlobals();
	});

	it("should expose a resize observer", () => {
		const observers = Observers();
		expect(observers.resize).toBeInstanceOf(ResizeObserver);
	});

	it("should dispatch a resize CustomEvent for each observed entry", () => {
		// capture the callback that Observers() passes to the ResizeObserver
		let callback: ResizeObserverCallback | undefined;

		vi.stubGlobal(
			"ResizeObserver",
			class {
				constructor(cb: ResizeObserverCallback) {
					callback = cb;
				}
				observe() {}
				unobserve() {}
				disconnect() {}
			}
		);

		Observers();

		const target = document.createElement("div");
		const events: CustomEvent[] = [];
		target.addEventListener("resize", (e) =>
			events.push(e as unknown as CustomEvent)
		);

		callback!(
			[
				{
					target,
					contentRect: { width: 100, height: 50 }
				} as unknown as ResizeObserverEntry
			],
			{} as ResizeObserver
		);

		expect(events).toHaveLength(1);
		expect(events[0].detail).toStrictEqual({ width: 100, height: 50 });
	});

	it("should dispatch a resize event on every entry", () => {
		let callback: ResizeObserverCallback | undefined;

		vi.stubGlobal(
			"ResizeObserver",
			class {
				constructor(cb: ResizeObserverCallback) {
					callback = cb;
				}
				observe() {}
				unobserve() {}
				disconnect() {}
			}
		);

		Observers();

		const a = document.createElement("div");
		const b = document.createElement("div");
		let count = 0;
		a.addEventListener("resize", () => count++);
		b.addEventListener("resize", () => count++);

		callback!(
			[
				{ target: a, contentRect: { width: 10, height: 20 } },
				{ target: b, contentRect: { width: 30, height: 40 } }
			] as unknown as ResizeObserverEntry[],
			{} as ResizeObserver
		);

		expect(count).toStrictEqual(2);
	});
});
