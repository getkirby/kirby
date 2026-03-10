import { describe, expect, it } from "vitest";
import Events from "./events";
import Panel from "./panel.js";

describe("panel.events", () => {
	describe("drag & drop", () => {
		const panel = Panel.create();
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

	describe("keychain", () => {
		const panel = Panel.create();
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
		const panel = Panel.create();
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
		const panel = Panel.create();
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
		const panel = Panel.create() as TODO;
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
		const panel = Panel.create();
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
		const panel = Panel.create();
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
		const panel = Panel.create();
		const events = Events(panel);

		it("should call panel.search on keydown.cmd.shift.f", () => {
			let searched = false;
			panel.search = () => (searched = true);

			events.keydown({
				metaKey: true,
				shiftKey: true,
				key: "f"
			} as unknown as KeyboardEvent);

			expect(searched).toStrictEqual(true);
		});

		it("should call panel.search on keydown.cmd./", () => {
			let searched = false;
			panel.search = () => (searched = true);

			events.keydown({
				metaKey: true,
				key: "/"
			} as unknown as KeyboardEvent);

			expect(searched).toStrictEqual(true);
		});
	});
});
