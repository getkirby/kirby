import { describe, expect, it } from "vitest";
import Modal, { defaults } from "./modal";
import Panel from "./panel.js";
import Vue from "vue";

describe("panel.modal", () => {
	// @ts-expect-error Vue 2 test setup
	window.Vue = Vue;

	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

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

			expect(modal.key()).toStrictEqual("test");
			expect(modal.state()).toStrictEqual(state);
		});
	});

	describe("cancel()", () => {
		it("should emit cancel event", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let cancelled = false;

			await modal.open({
				component: "k-test",
				on: {
					cancel() {
						cancelled = true;
					}
				}
			});

			expect(modal.isOpen).toStrictEqual(true);
			expect(cancelled).toStrictEqual(false);

			modal.cancel();

			expect(cancelled).toStrictEqual(true);
		});

		it("should do nothing when not open", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let cancelled = false;

			modal.on.cancel = () => {
				cancelled = true;
			};

			await modal.cancel();

			expect(cancelled).toStrictEqual(false);
		});
	});

	describe("close()", () => {
		it("should close the modal", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let closed = false;

			await modal.open({
				component: "k-test",
				on: {
					close() {
						closed = true;
					}
				}
			});

			expect(modal.isOpen).toStrictEqual(true);

			await modal.close();

			expect(modal.isOpen).toStrictEqual(false);
			expect(closed).toStrictEqual(true);
		});

		it("should do nothing when not open", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.close();

			expect(modal.isOpen).toStrictEqual(false);
		});

		it("should do nothing when id does not match", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({ component: "k-test" });

			await modal.close("other-id");

			expect(modal.isOpen).toStrictEqual(true);
		});
	});

	describe("goTo()", () => {
		it("should do nothing for an unknown id", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({ component: "k-test" });

			modal.goTo("unknown");

			expect(modal.component).toStrictEqual("k-test");
		});
	});

	describe("input()", () => {
		it("should do nothing when not open", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			modal.input({ foo: "bar" });

			expect(modal.props.value).toStrictEqual({});
		});

		it("should change value", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let input: unknown = null;

			await modal.open({
				component: "k-test",
				on: {
					input(value) {
						input = value;
					}
				}
			});

			// eslint-disable-next-line @typescript-eslint/no-explicit-any
			modal.input("foo" as any);

			expect(modal.props.value).toStrictEqual("foo");
			expect(modal.value).toStrictEqual("foo");
			expect(input).toStrictEqual("foo");
		});
	});

	describe("listeners()", () => {
		it("should include built-in handlers", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			const listeners = modal.listeners();

			expect(typeof listeners.cancel).toStrictEqual("function");
			expect(typeof listeners.close).toStrictEqual("function");
			expect(typeof listeners.input).toStrictEqual("function");
			expect(typeof listeners.submit).toStrictEqual("function");
			expect(typeof listeners.success).toStrictEqual("function");
		});
	});

	describe("open()", () => {
		it("should not set isOpen when no component is given", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({});

			expect(modal.isOpen).toStrictEqual(false);
		});

		it("should emit open event", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let opened = false;

			await modal.open({
				component: "k-test",
				on: {
					open() {
						opened = true;
					}
				}
			});

			expect(modal.isOpen).toStrictEqual(true);
			expect(opened).toStrictEqual(true);
		});

		it("should close a previous notification", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			// @ts-expect-error panel.js is not typed
			panel.notification.error("Foo");

			expect(modal.isOpen).toStrictEqual(false);
			// @ts-expect-error panel.js is not typed
			expect(panel.notification.isOpen).toStrictEqual(true);

			await modal.open({ component: "k-test" });

			expect(modal.isOpen).toStrictEqual(true);
			// @ts-expect-error panel.js is not typed
			expect(panel.notification.isOpen).toStrictEqual(false);
		});

		it("should not close a previous notification on re-open", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({ component: "k-test" });

			// @ts-expect-error panel.js is not typed
			panel.notification.error("Test");

			// @ts-expect-error panel.js is not typed
			expect(panel.notification.isOpen).toStrictEqual(true);

			await modal.open({ component: "k-test" });

			// @ts-expect-error panel.js is not typed
			expect(panel.notification.isOpen).toStrictEqual(true);
		});
	});

	describe("reload()", () => {
		it("should return false when no path is set", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			expect(await modal.reload()).toStrictEqual(false);
		});
	});

	describe("set()", () => {
		it("should assign a unique id", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			modal.set({ component: "k-test" });

			expect(modal.id).toBeTypeOf("string");
		});
	});

	describe("submit()", () => {
		it("should call submit listener", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let submitted = false;

			await modal.open({
				component: "k-test",
				on: {
					submit() {
						submitted = true;
					}
				}
			});

			await modal.submit({});

			expect(submitted).toStrictEqual(true);
		});

		it("should close when no submit handler and no path", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({ component: "k-test" });

			expect(modal.isOpen).toStrictEqual(true);

			await modal.submit({});

			expect(modal.isOpen).toStrictEqual(false);
		});

		it("should do nothing when already loading", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let submitted = false;

			await modal.open({
				component: "k-test",
				on: {
					submit() {
						submitted = true;
					}
				}
			});

			modal.isLoading = true;
			await modal.submit({});

			expect(submitted).toStrictEqual(false);
		});
	});

	describe("success()", () => {
		it("should send a notification", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			modal.success({ message: "Test" });

			// @ts-expect-error panel.js is not typed
			expect(panel.notification.message).toStrictEqual("Test");
			// @ts-expect-error panel.js is not typed
			expect(panel.notification.theme).toStrictEqual("positive");
		});

		it("should emit panel events", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			const emitted: string[] = [];

			// @ts-expect-error panel.js is not typed
			panel.events.on("success", () => {
				emitted.push("success");
			});

			// @ts-expect-error panel.js is not typed
			panel.events.on("user.deleted", () => {
				emitted.push("user.deleted");
			});

			modal.success({ event: "user.deleted" });

			expect(emitted).toStrictEqual(["user.deleted", "success"]);
		});

		it("should close the modal", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			await modal.open({ component: "k-test" });

			expect(modal.isOpen).toStrictEqual(true);

			modal.success({});

			expect(modal.isOpen).toStrictEqual(false);
		});

		it("should call success listener", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			let succeeded = false;

			await modal.open({
				component: "k-test",
				on: {
					success() {
						succeeded = true;
					}
				}
			});

			modal.success({});

			expect(succeeded).toStrictEqual(true);
		});

		it("should handle string success as notification", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());

			modal.success("Well done");

			// @ts-expect-error panel.js is not typed
			expect(panel.notification.message).toStrictEqual("Well done");
			// @ts-expect-error panel.js is not typed
			expect(panel.notification.theme).toStrictEqual("positive");
		});

		it("should skip panel success event when emit is false", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			const emitted: string[] = [];

			// @ts-expect-error panel.js is not typed
			panel.events.on("success", () => {
				emitted.push("success");
			});

			modal.success({ emit: false });

			expect(emitted).toStrictEqual([]);
		});

		it("should emit multiple panel events", async () => {
			const panel = Panel.create(app);
			const modal = Modal(panel, "test", defaults());
			const emitted: string[] = [];

			// @ts-expect-error panel.js is not typed
			panel.events.on("user.created", () => {
				emitted.push("user.created");
			});

			// @ts-expect-error panel.js is not typed
			panel.events.on("page.created", () => {
				emitted.push("page.created");
			});

			modal.success({ event: ["user.created", "page.created"], emit: false });

			expect(emitted).toStrictEqual(["user.created", "page.created"]);
		});
	});
});
