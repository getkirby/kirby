import { describe, expect, it, vi } from "vitest";
import type { PanelResponse } from "@/panel/request";
import AuthError from "@/errors/AuthError";
import JsonRequestError from "@/errors/JsonRequestError";
import RequestError from "@/errors/RequestError";
import Notification from "./notification";
import Panel from "./panel.js";

function makeRequestOptions(
	json: Record<string, unknown> = {},
	text = ""
): {
	request: Request;
	response: PanelResponse;
} {
	return {
		request: new Request("https://example.com/api"),
		response: {
			headers: new Headers(),
			json,
			ok: false,
			status: 500,
			statusText: "Internal Server Error",
			text,
			url: "https://example.com/api"
		}
	};
}

describe("panel.notification", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const notification = Notification({});

			const state = {
				context: null,
				details: {},
				icon: null,
				isOpen: false,
				message: null,
				theme: null,
				timeout: 0,
				type: null
			};

			expect(notification.key()).toStrictEqual("notification");
			expect(notification.state()).toStrictEqual(state);
		});
	});

	describe("close()", () => {
		it("should reset the state", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.success("Test");
			expect(notification.isOpen).toStrictEqual(true);

			notification.close();
			expect(notification.isOpen).toStrictEqual(false);
			expect(notification.message).toStrictEqual(null);
		});

		it("should reset the timer", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.success("Test");
			expect(notification.timer.isRunning).toBeTruthy();

			notification.close();
			expect(notification.timer.isRunning).toBeFalsy();
		});
	});

	describe("context", () => {
		it("should return the view context", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.open("test");

			expect(notification.context).toStrictEqual("view");
		});

		it("should return the drawer context", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			// @ts-expect-error panel.js is not typed
			await panel.drawer.open({
				component: "k-drawer"
			});

			notification.open("test");

			expect(notification.context).toStrictEqual("drawer");
		});

		it("should return the dialog context", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			// @ts-expect-error panel.js is not typed
			await panel.dialog.open({
				component: "k-dialog"
			});

			notification.open("test");

			expect(notification.context).toStrictEqual("dialog");
		});
	});

	describe("deprecated()", () => {
		it("should log deprecation warnings", async () => {
			const notification = Notification({});
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});

			notification.deprecated("OldMethod");

			expect(warn).toHaveBeenCalledWith("Deprecated: OldMethod");
			warn.mockRestore();
		});
	});

	describe("error()", () => {
		it("should convert error strings", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error("Something specific went wrong");

			expect(notification.type).toStrictEqual("error");
			expect(notification.message).toStrictEqual(
				"Something specific went wrong"
			);
		});

		it("should convert error objects", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error(new Error("test"));

			expect(notification.type).toStrictEqual("error");
			expect(notification.message).toStrictEqual("test");
		});

		it("should convert plain objects with a message", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error({ message: "Something specific went wrong" });

			expect(notification.type).toStrictEqual("error");
			expect(notification.message).toStrictEqual(
				"Something specific went wrong"
			);
		});

		it("should fall back to a generic message for errors without a message", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error(new Error());

			expect(notification.message).toStrictEqual("Something went wrong");
		});

		it("should fall back to a generic message for plain objects without a message", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error({});

			expect(notification.type).toStrictEqual("error");
			expect(notification.message).toStrictEqual("Something went wrong");
		});

		it("should treat AuthError as a normal error when user is not authenticated", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error(new AuthError("Unauthorized", makeRequestOptions()));

			expect(notification.type).toStrictEqual("error");
			expect(notification.theme).toStrictEqual("negative");
		});

		it("should escalate JsonRequestError to fatal", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error(new JsonRequestError("error", makeRequestOptions()));

			expect(notification.type).toStrictEqual("fatal");
		});

		it("should open a request error dialog for RequestError in view context", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);
			// @ts-expect-error panel.js is not typed
			const open = vi.spyOn(panel.dialog, "open");

			notification.error(
				new RequestError("Something failed", makeRequestOptions())
			);

			expect(open).toHaveBeenCalledWith(
				expect.objectContaining({ component: "k-request-error-dialog" })
			);
			expect(notification.type).toStrictEqual("error");
		});

		it("should open a generic error dialog for plain errors in view context", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);
			// @ts-expect-error panel.js is not typed
			const open = vi.spyOn(panel.dialog, "open");

			notification.error(new Error("Something failed"));

			expect(open).toHaveBeenCalledWith(
				expect.objectContaining({ component: "k-error-dialog" })
			);
			expect(notification.type).toStrictEqual("error");
		});

		it("should set error icon and theme", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error("Test");

			expect(notification.icon).toStrictEqual("alert");
			expect(notification.theme).toStrictEqual("negative");
		});

		it("should not set a timer", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.error("Test");
			expect(notification.timeout).toStrictEqual(0);
		});
	});

	describe("fatal()", () => {
		it("should convert error strings", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.fatal("Something broke");

			expect(notification.type).toStrictEqual("fatal");
			expect(notification.message).toStrictEqual("Something broke");
		});

		it("should convert error objects", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.fatal(new Error("Fatal error"));

			expect(notification.type).toStrictEqual("fatal");
			expect(notification.message).toStrictEqual("Fatal error");
		});

		it("should use response text for JsonRequestError", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.fatal(
				new JsonRequestError(
					"error",
					makeRequestOptions({}, "<html>PHP error</html>")
				)
			);

			expect(notification.type).toStrictEqual("fatal");
			expect(notification.message).toStrictEqual("<html>PHP error</html>");
		});

		it("should fall back to a generic message for errors without a message", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.fatal(new Error());

			expect(notification.type).toStrictEqual("fatal");
			expect(notification.message).toStrictEqual("Something went wrong");
		});

		it("should not set a timer", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.fatal("test");

			expect(notification.timeout).toStrictEqual(0);
		});
	});

	describe("info()", () => {
		it("should return the right icon and theme", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.info("test");

			expect(notification.icon).toStrictEqual("info");
			expect(notification.theme).toStrictEqual("info");
			expect(notification.message).toStrictEqual("test");
		});

		it("should allow overriding defaults", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.info({ message: "test", icon: "live" });

			expect(notification.icon).toStrictEqual("live");
		});
	});

	describe("isFatal", () => {
		it("should detect fatal notifications", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			expect(notification.isFatal).toStrictEqual(false);

			notification.fatal("test");

			expect(notification.isFatal).toStrictEqual(true);
		});
	});

	describe("open()", () => {
		it("should open & close", async () => {
			const notification = Notification({});

			notification.open({
				message: "Hello world"
			});

			expect(notification.message).toStrictEqual("Hello world");
			expect(notification.isOpen).toStrictEqual(true);

			notification.close();

			expect(notification.isOpen).toStrictEqual(false);
		});

		it("should open a string notification as success", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.open("test");

			expect(notification.theme).toStrictEqual("positive");
			expect(notification.icon).toStrictEqual("check");
			expect(notification.message).toStrictEqual("test");
		});
	});

	describe("success()", () => {
		it("should return the right icon and theme", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.success("Test");

			expect(notification.icon).toStrictEqual("check");
			expect(notification.theme).toStrictEqual("positive");
		});

		it("should allow overriding defaults", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.success({ message: "Test", icon: "live" });

			expect(notification.icon).toStrictEqual("live");
		});

		it("should set a timer", async () => {
			const panel = Panel.create(app);
			const notification = Notification(panel);

			notification.success("Test");
			expect(notification.timeout).toStrictEqual(4000);
		});
	});
});
