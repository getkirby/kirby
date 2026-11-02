import { describe, expect, it } from "vitest";
import { failed } from "@test/unit/request";
import RequestError from "./RequestError";

describe("RequestError", () => {
	describe("constructor", () => {
		it("uses response.json.message as error message", () => {
			const error = new RequestError(
				"fallback",
				failed({ json: { message: "From JSON" } })
			);
			expect(error.message).toBe("From JSON");
		});

		it("falls back to response.json.error when no message", () => {
			const error = new RequestError(
				"fallback",
				failed({ json: { error: "From error field" } })
			);
			expect(error.message).toBe("From error field");
		});

		it("falls back to constructor message when json has neither", () => {
			const error = new RequestError("Fallback message", failed());
			expect(error.message).toBe("Fallback message");
		});

		it("stores details from response.json.details", () => {
			const details = { field: "name", message: "required" };
			const error = new RequestError("msg", failed({ json: { details } }));
			expect(error.details).toStrictEqual(details);
		});

		it("stores request and response", () => {
			const options = failed({ json: { message: "ok" } });
			const error = new RequestError("msg", options);
			expect(error.request).toBe(options.request);
			expect(error.response).toBe(options.response);
		});
	});

	describe("dialog()", () => {
		it("opens the validation dialog for form validation errors", () => {
			const details = {
				title: { label: "Title", message: { required: "Required" } }
			};
			const error = new RequestError(
				"msg",
				failed({
					json: {
						message: "Please fix all form errors…",
						exception: "Kirby\\Exception\\FormValidationException",
						key: "error.form.incomplete",
						details
					}
				})
			);

			expect(error.dialog()).toStrictEqual({
				component: "k-validation-error-dialog",
				props: { message: "Please fix all form errors…", fields: details }
			});
		});

		it("opens the validation dialog without debug info", () => {
			const details = {
				title: { label: "Title", message: { required: "Required" } }
			};
			const error = new RequestError(
				"msg",
				failed({
					json: {
						message: "Please fix all form errors…",
						key: "error.form.incomplete",
						details
					}
				})
			);

			expect(error.dialog()).toStrictEqual({
				component: "k-validation-error-dialog",
				props: { message: "Please fix all form errors…", fields: details }
			});
		});

		it("opens the request error dialog for other errors", () => {
			const details = { foo: "bar" };
			const error = new RequestError(
				"msg",
				failed({
					json: {
						message: "Something failed",
						key: "error.general",
						details
					}
				})
			);

			expect(error.dialog()).toMatchObject({
				component: "k-request-error-dialog",
				props: { message: "Something failed", details }
			});
		});
	});

	describe("state()", () => {
		it("returns response.json", () => {
			const json = { message: "ok", details: { field: "name" } };
			const error = new RequestError("msg", failed({ json }));
			expect(error.state()).toStrictEqual(json);
		});
	});
});
