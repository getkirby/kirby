import { describe, expect, it, vi } from "vitest";
import Dialog from "./dialog";
import Panel from "./panel.js";

describe("panel.dialog", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);
			const state = {
				component: null,
				id: null,
				isLoading: false,
				on: {},
				path: null,
				props: { value: {} },
				query: {},
				referrer: null,
				timestamp: null
			};

			expect(dialog.key()).toStrictEqual("dialog");
			expect(dialog.state()).toStrictEqual(state);
		});
	});

	describe("open()", () => {
		it("should open a dialog component", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);

			await dialog.open({ component: "k-remove-dialog" });

			expect(dialog.isOpen).toStrictEqual(true);
			expect(dialog.component).toStrictEqual("k-remove-dialog");
		});

		it("should add to history when a component is given", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);

			await dialog.open({ component: "k-remove-dialog" });

			expect(dialog.history.isEmpty()).toStrictEqual(false);
			expect(dialog.id).toBeTypeOf("string");
		});

		it("should not mark as open when no component is given", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);

			await dialog.open({});

			expect(dialog.isOpen).toStrictEqual(false);
		});

		it("should track multiple dialogs in history", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);

			await dialog.open({ component: "k-first-dialog" });
			await dialog.open({ component: "k-second-dialog" });

			expect(dialog.history.milestones).toHaveLength(2);
		});

		it("should replace the current history entry when replace is true", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);

			await dialog.open({ component: "k-first-dialog" });
			await dialog.open({ component: "k-second-dialog", replace: true });

			expect(dialog.history.milestones).toHaveLength(1);
			expect(dialog.component).toStrictEqual("k-second-dialog");
		});

		it("should prefix string paths with /dialogs/", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);
			const load = vi.spyOn(dialog, "load").mockResolvedValue(dialog.state());

			await dialog.open("some/dialog");

			expect(load).toHaveBeenCalledWith(
				"/dialogs/some/dialog",
				expect.anything()
			);

			load.mockRestore();
		});

		it("should handle a dialog object with a url property", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);
			const load = vi.spyOn(dialog, "load").mockResolvedValue(dialog.state());

			await dialog.open({ url: "some/dialog" });

			expect(load).toHaveBeenCalledWith(
				"/dialogs/some/dialog",
				expect.anything()
			);

			load.mockRestore();
		});

		it("should pass options when using a dialog object with a url property", async () => {
			const panel = Panel.create(app);
			const dialog = Dialog(panel);
			const load = vi.spyOn(dialog, "load").mockResolvedValue(dialog.state());
			const onSubmit = vi.fn();

			await dialog.open({ url: "some/dialog", on: { submit: onSubmit } });

			expect(load).toHaveBeenCalledWith(
				"/dialogs/some/dialog",
				expect.objectContaining({ on: { submit: onSubmit } })
			);

			load.mockRestore();
		});
	});

	describe("dialog.save event", () => {
		it("should submit the dialog on dialog.save event", async () => {
			const panel = Panel.create(app);
			const event = { preventDefault: vi.fn() };
			let submitted = false;

			// @ts-expect-error panel.js is not typed
			await panel.dialog.open({
				component: "k-test",
				on: {
					submit() {
						submitted = true;
					}
				}
			});

			// @ts-expect-error panel.js is not typed
			panel.events.emit("dialog.save", event);

			expect(submitted).toStrictEqual(true);
			expect(event.preventDefault).toHaveBeenCalled();
		});
	});
});
