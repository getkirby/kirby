import { describe, expect, it, onTestFinished, vi } from "vitest";
import Events from "./events";
import Panel from "./panel";

describe("panel.events", () => {
	describe("clipboard.write", () => {
		it("should copy the value and confirm it", async () => {
			const panel = Panel.create(app);
			const events = Events(panel);
			const execCommand = document.execCommand;
			let copied: string | undefined;

			document.execCommand = vi.fn(() => {
				copied = document.querySelector("textarea")?.value;
				return true;
			});

			onTestFinished(() => {
				document.execCommand = execCommand;
			});

			events.emit("clipboard.write", "https://getkirby.com");

			expect(copied).toStrictEqual("https://getkirby.com");
			expect(panel.notification.message).toStrictEqual("copy.success!");
		});
	});

	describe("drag & drop", () => {
		const panel = Panel.create(app);
		const events = Events(panel);
		const event = (e: object = {}): DragEvent => e as unknown as DragEvent;

		it("should keep track of target", async () => {
			let fired = false;

			const eventA = event({
				target: "targetA",
				preventDefault: () => {},
				stopPropagation: () => {}
			});

			const eventB = event({
				...eventA,
				target: "targetB"
			});

			events.on("dragleave", () => {
				fired = true;
			});

			events.dragenter(eventA);
			expect(events.entered).toStrictEqual("targetA");

			// should not fire because it's a different target
			events.dragleave(eventB);
			expect(fired).toStrictEqual(false);

			// should fire because it's the same target
			events.dragleave(eventA);
			expect(fired).toStrictEqual(true);
		});
	});

	describe("global events", () => {
		/**
		 * Subscribes a new event bus to the global
		 * events and spies on the given event
		 */
		function subscribe(type: string) {
			const events = Events(Panel.create(app));
			const handler = vi.fn();

			events.subscribe();
			events.on(type, handler);
			onTestFinished(() => events.unsubscribe());

			return { events, handler };
		}

		it.each([
			["blur", () => new FocusEvent("blur")],
			["click", () => new MouseEvent("click", { bubbles: true })],
			["copy", () => new ClipboardEvent("copy", { bubbles: true })],
			["focus", () => new FocusEvent("focus")],
			["paste", () => new ClipboardEvent("paste", { bubbles: true })]
		])("should forward %s events of any element", (type, create) => {
			const { handler } = subscribe(type);
			const input = document.createElement("input");
			const event = create();

			document.body.append(input);
			input.dispatchEvent(event);

			expect(handler).toHaveBeenCalledExactlyOnceWith(event);
		});

		it.each([
			["beforeunload", () => new Event("beforeunload", { cancelable: true })],
			["offline", () => new Event("offline")],
			["online", () => new Event("online")],
			["popstate", () => new PopStateEvent("popstate")]
		])("should forward %s events of the window", (type, create) => {
			const { handler } = subscribe(type);
			const event = create();

			window.dispatchEvent(event);

			expect(handler).toHaveBeenCalledExactlyOnceWith(event);
		});

		it.each(["dragover", "drop"])(
			"should forward %s events and prevent their default",
			(type) => {
				const { handler } = subscribe(type);
				const event = new DragEvent(type, { bubbles: true, cancelable: true });

				document.body.dispatchEvent(event);

				expect(handler).toHaveBeenCalledExactlyOnceWith(event);
				expect(event.defaultPrevented).toStrictEqual(true);
			}
		);

		it("should forget the entered element on drop", () => {
			const { events } = subscribe("drop");
			const init = { bubbles: true, cancelable: true };

			document.body.dispatchEvent(new DragEvent("dragenter", init));
			expect(events.entered).toBe(document.body);

			document.body.dispatchEvent(new DragEvent("drop", init));
			expect(events.entered).toStrictEqual(null);
		});
	});

	describe("keychain", () => {
		const panel = Panel.create(app);
		const events = Events(panel);
		const event = (e: object = {}): KeyboardEvent =>
			e as unknown as KeyboardEvent;

		it("should only add the type", async () => {
			const result = events.keychain("keydown", event());
			expect(result).toStrictEqual("keydown");
		});

		it("should add shift", async () => {
			const result = events.keychain("keydown", event({ shiftKey: true }));
			expect(result).toStrictEqual("keydown.shift");
		});

		it("should add shift & key", async () => {
			const result = events.keychain(
				"keydown",
				event({ shiftKey: true, key: "s" })
			);
			expect(result).toStrictEqual("keydown.shift.s");
		});

		it("should add cmd", async () => {
			// Cmd
			const cmd = events.keychain("keydown", event({ metaKey: true }));
			expect(cmd).toStrictEqual("keydown.cmd");

			// Ctrl
			const ctrl = events.keychain("keydown", event({ ctrlKey: true }));
			expect(ctrl).toStrictEqual("keydown.cmd");
		});

		it("should add cmd & key", async () => {
			const result = events.keychain(
				"keydown",
				event({ metaKey: true, key: "s" })
			);
			expect(result).toStrictEqual("keydown.cmd.s");
		});

		it("should add alt", async () => {
			const result = events.keychain("keydown", event({ altKey: true }));
			expect(result).toStrictEqual("keydown.alt");
		});

		it("should add alt & key", async () => {
			const result = events.keychain(
				"keydown",
				event({ altKey: true, key: "v" })
			);
			expect(result).toStrictEqual("keydown.alt.v");
		});

		it("should add key replacement", async () => {
			const esc = events.keychain("keydown", event({ key: "escape" }));
			expect(esc).toStrictEqual("keydown.esc");

			const up = events.keychain("keydown", event({ key: "arrowUp" }));
			expect(up).toStrictEqual("keydown.up");

			const down = events.keychain("keydown", event({ key: "arrowDown" }));
			expect(down).toStrictEqual("keydown.down");

			const left = events.keychain("keydown", event({ key: "arrowLeft" }));
			expect(left).toStrictEqual("keydown.left");

			const right = events.keychain("keydown", event({ key: "arrowRight" }));
			expect(right).toStrictEqual("keydown.right");
		});

		it("should add combo", async () => {
			const result = events.keychain(
				"keydown",
				event({ metaKey: true, shiftKey: true, altKey: true, key: "escape" })
			);
			expect(result).toStrictEqual("keydown.cmd.alt.shift.esc");
		});
	});

	describe("keydown", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should fire keydown event with modifiers", async () => {
			let fired = false;

			events.on("keydown.cmd.s", () => {
				fired = true;
			});

			events.keydown({ metaKey: true, key: "s" } as unknown as KeyboardEvent);

			expect(fired).toStrictEqual(true);
		});
	});

	describe("keyup", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should fire keyup event with modifiers", async () => {
			let fired = false;

			events.on("keyup.cmd.s", () => {
				fired = true;
			});

			events.keyup({ metaKey: true, key: "s" } as unknown as KeyboardEvent);

			expect(fired).toStrictEqual(true);
		});
	});

	describe("online/offline", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should set panel.isOffline to true on offline", () => {
			events.emit("offline");
			expect(panel.isOffline).toStrictEqual(true);
		});

		it("should set panel.isOffline to false on online", () => {
			panel.isOffline = true;
			events.emit("online");
			expect(panel.isOffline).toStrictEqual(false);
		});
	});

	describe("prevent", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should stop propagation and prevent default", () => {
			let stopped = false;
			let prevented = false;

			events.prevent({
				stopPropagation: () => (stopped = true),
				preventDefault: () => (prevented = true)
			} as unknown as Event);

			expect(stopped).toStrictEqual(true);
			expect(prevented).toStrictEqual(true);
		});
	});

	describe("save", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should emit view.save on keydown.cmd.s", () => {
			let fired = false;

			events.on("view.save", () => {
				fired = true;
			});

			events.keydown({ metaKey: true, key: "s" } as unknown as KeyboardEvent);

			expect(fired).toStrictEqual(true);
		});
	});

	describe("search", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should call panel.search on keydown.cmd.shift.f", () => {
			const search = vi.spyOn(panel, "search").mockResolvedValue(undefined);

			events.keydown({
				metaKey: true,
				shiftKey: true,
				key: "f"
			} as unknown as KeyboardEvent);

			expect(search).toHaveBeenCalled();
		});

		it("should call panel.search on keydown.cmd./", () => {
			const search = vi.spyOn(panel, "search").mockResolvedValue(undefined);

			events.keydown({
				metaKey: true,
				key: "/"
			} as unknown as KeyboardEvent);

			expect(search).toHaveBeenCalled();
		});
	});

	describe("subscribe / unsubscribe", () => {
		const panel = Panel.create(app);
		const events = Events(panel);

		it("should remove every listener it registered", () => {
			type Call = [string, unknown, boolean];
			const added: Call[] = [];
			const removed: Call[] = [];

			const capture = (options?: boolean | EventListenerOptions) =>
				typeof options === "boolean" ? options : options?.capture === true;

			const spies = [
				vi
					.spyOn(document, "addEventListener")
					.mockImplementation((type, listener, options) => {
						added.push([type, listener, capture(options)]);
					}),
				vi
					.spyOn(window, "addEventListener")
					.mockImplementation((type, listener, options) => {
						added.push([type, listener, capture(options)]);
					}),
				vi
					.spyOn(document, "removeEventListener")
					.mockImplementation((type, listener, options) => {
						removed.push([type, listener, capture(options)]);
					}),
				vi
					.spyOn(window, "removeEventListener")
					.mockImplementation((type, listener, options) => {
						removed.push([type, listener, capture(options)]);
					})
			];

			events.subscribe();
			events.unsubscribe();

			// a listener is only removed with the same function
			// reference and the same capture flag it was added with
			const leaked = added.filter(
				([type, listener, flag]) =>
					removed.some(
						([t, l, f]) => t === type && l === listener && f === flag
					) === false
			);

			expect(added.length).toBeGreaterThan(0);
			expect(leaked).toStrictEqual([]);

			for (const spy of spies) {
				spy.mockRestore();
			}
		});
	});
});
