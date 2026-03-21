import { reactive } from "vue";
import AuthError from "@/errors/AuthError";
import JsonRequestError from "@/errors/JsonRequestError";
import RequestError from "@/errors/RequestError";
import State from "./state";
import Timer from "@/helpers/timer";

type NotificationState = {
	context: "dialog" | "drawer" | "view" | null;
	details: Record<string, unknown>;
	icon: string | null;
	isOpen: boolean;
	message: string | null;
	theme: string | null;
	timeout: number;
	type: "error" | "fatal" | null;
};

export function defaults(): NotificationState {
	return {
		context: null,
		details: {},
		icon: null,
		isOpen: false,
		message: null,
		theme: null,
		timeout: 0,
		type: null
	};
}

/**
 * Normalizes an unknown value to an Error instance
 */
function toError(error: unknown): Error {
	if (typeof error === "string") {
		return new Error(error);
	}

	if (error instanceof Error) {
		return error;
	}

	const obj = error as { message?: unknown; details?: unknown };
	const message =
		typeof obj?.message === "string" ? obj.message : "Something went wrong";

	return Object.assign(new Error(message), { details: obj?.details ?? {} });
}

/**
 * Manages the Panel's notifications
 * @since 4.0.0
 */
export default function Notification(panel: TODO) {
	const parent = State("notification", defaults());

	return reactive({
		...parent,

		/**
		 * Closes the notification by setting the inactive state (defaults)
		 */
		close(): Prettify<NotificationState> {
			// stop any previous timers
			this.timer.stop();

			// reset the defaults
			this.reset();

			// return the closed state
			return this.state();
		},

		/**
		 * Sends a deprecation warning to the console
		 */
		deprecated(message: string): void {
			console.warn("Deprecated: " + message);
		},

		/**
		 * Converts an error object or string into an error notification
		 *
		 * @example
		 * panel.notification.error("Something went wrong");
		 * panel.notification.error(new Error("Something went wrong"));
		 */
		error(error: unknown): Prettify<NotificationState> {
			if (error instanceof AuthError) {
				// only redirect to logout if panel state actually
				// assumes the user is authenticated, otherwise
				// continue to show a normal error notification
				if (panel.user.id) {
					return panel.redirect("logout");
				}
			}

			if (error instanceof JsonRequestError) {
				return this.fatal(error);
			}

			const err = toError(error);

			// open the error dialog in views
			if (panel.context === "view") {
				if (err instanceof RequestError) {
					panel.dialog.open(err.dialog());
				} else {
					panel.dialog.open({
						component: "k-error-dialog",
						props: err
					});
				}
			}

			// show the error notification bar
			return this.open({
				message: err.message || "Something went wrong",
				icon: "alert",
				theme: "negative",
				type: "error"
			});
		},

		/**
		 * Shortcut to create an info notification.
		 * You can pass a simple string or a state object.
		 *
		 * @example
		 * panel.notification.info("The file has been uploaded");
		 * panel.notification.info({ message: "The file has been uploaded", icon: "upload" });
		 */
		info(
			info: Partial<Prettify<NotificationState>> | string
		): Prettify<NotificationState> {
			if (typeof info === "string") {
				info = { message: info };
			}

			return this.open({
				icon: "info",
				theme: "info",
				...info
			});
		},

		/**
		 * Checks if the notification is a fatal error.
		 * Those are displayed in the <k-fatal> component
		 * which sends them to an isolated iframe.
		 * This will happen when API responses cannot be parsed at all.
		 */
		get isFatal(): boolean {
			return this.type === "fatal";
		},

		/**
		 * Creates a fatal error based on an Error object or string
		 *
		 * @example
		 * panel.notification.fatal("The response could not be parsed");
		 */
		fatal(error: Error | string): Prettify<NotificationState> {
			if (typeof error === "string") {
				return this.open({
					message: error,
					type: "fatal"
				});
			}

			if (error instanceof JsonRequestError) {
				return this.open({
					message: error.response.text,
					type: "fatal"
				});
			}

			return this.open({
				message: error.message || "Something went wrong",
				type: "fatal"
			});
		},

		/**
		 * Opens the notification.
		 * The context will determine where it will be shown.
		 *
		 * @example
		 * panel.notification.open("Saved");
		 * panel.notification.open({ message: "Saved", icon: "check", theme: "positive" });
		 */
		open(
			notification: Partial<Prettify<NotificationState>> | string
		): Prettify<NotificationState> {
			// stop any previous timers
			this.timer.stop();

			// simple success notifications
			if (typeof notification === "string") {
				return this.success(notification);
			}

			// add timeout if not error or fatal notification
			if (notification.type !== "error" && notification.type !== "fatal") {
				notification.timeout ||= 4000;
			}

			// set the new state
			this.set({
				// add the current editing context
				context: panel.context,
				...notification
			});

			// open the notification
			this.isOpen = true;

			// start a timer to auto-close the notification
			this.timer.start(this.timeout, () => this.close());

			// returns the new open state
			return this.state();
		},

		/**
		 * Shortcut to create a success notification.
		 * You can pass a simple string or a state object.
		 *
		 * @example
		 * panel.notification.success("Saved");
		 * panel.notification.success({ message: "Saved", icon: "star" });
		 */
		success(
			success: Partial<Prettify<NotificationState>> | string
		): Prettify<NotificationState> {
			if (typeof success === "string") {
				success = { message: success };
			}

			return this.open({
				icon: "check",
				theme: "positive",
				...success
			});
		},

		/**
		 * Holds the timer object
		 */
		timer: new Timer()
	});
}
