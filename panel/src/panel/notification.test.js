/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Notification from "./notification.js";
import Panel from "./panel.js";

describe("panel.notification", () => {
	it("should have a default state", async () => {
		const notification = Notification();

		const state = {
			context: null,
			details: null,
			icon: null,
			isOpen: false,
			message: null,
			theme: null,
			timeout: null,
			type: null
		};

		expect(notification.key()).toStrictEqual("notification");
		expect(notification.state()).toStrictEqual(state);
	});

	it("should open & close", async () => {
		const notification = Notification();

		notification.open({
			message: "Hello world"
		});

		expect(notification.message).toStrictEqual("Hello world");
		expect(notification.isOpen).toStrictEqual(true);

		notification.close();

		expect(notification.isOpen).toStrictEqual(false);
	});

	it("should return the view context", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.open("test");

		expect(notification.context).toStrictEqual("view");
	});

	it("should return the drawer context", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		await panel.drawer.open({
			component: "k-drawer"
		});

		notification.open("test");

		expect(notification.context).toStrictEqual("drawer");
	});

	it("should return the dialog context", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		await panel.dialog.open({
			component: "k-dialog"
		});

		notification.open("test");

		expect(notification.context).toStrictEqual("dialog");
	});

	it("should return the right icon", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.success("Test");

		expect(notification.icon).toStrictEqual("check");

		notification.error("Test");

		expect(notification.icon).toStrictEqual("alert");

		notification.success({ message: "Test", icon: "smile" });

		expect(notification.icon).toStrictEqual("smile");
	});

	it("should return the right theme", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.success("Test");
		expect(notification.theme).toStrictEqual("positive");

		notification.info("Test");
		expect(notification.theme).toStrictEqual("info");

		notification.error("Test");
		expect(notification.theme).toStrictEqual("negative");

		notification.open({ theme: "love" });
		expect(notification.theme).toStrictEqual("love");
	});

	it("should set a timer for success notifications", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.success("Test");
		expect(notification.timeout).toStrictEqual(4000);
	});

	it("should not set a timer for error notifications", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.error("Test");
		expect(notification.timeout).toStrictEqual(null);
	});

	it("should reset the timer when closing notifications", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.success("Test");
		expect(notification.timer.interval).toBeTypeOf("object");

		notification.close();
		expect(notification.timer.interval).toStrictEqual(null);
	});

	it("should convert Error objects", async () => {
		const panel = Panel.create(app);
		const notification = Notification(panel);

		notification.error(new Error("test"));

		expect(notification.type).toStrictEqual("error");
		expect(notification.message).toStrictEqual("test");
	});
});
