import { describe, expect, it } from "vitest";
import type { PanelResponse } from "@/panel/request";
import RequestError from "./RequestError";

function makeOptions(json: Record<string, unknown> = {}): {
	request: Request;
	response: PanelResponse;
} {
	return {
		request: new Request("https://example.com/api"),
		response: {
			headers: new Headers(),
			json,
			ok: false,
			status: 400,
			statusText: "Bad Request",
			text: JSON.stringify(json),
			url: "https://example.com/api"
		}
	};
}

describe("RequestError", () => {
	it("uses response.json.message as error message", () => {
		const error = new RequestError("fallback", makeOptions({ message: "From JSON" }));
		expect(error.message).toBe("From JSON");
	});

	it("falls back to response.json.error when no message", () => {
		const error = new RequestError("fallback", makeOptions({ error: "From error field" }));
		expect(error.message).toBe("From error field");
	});

	it("falls back to constructor message when json has neither", () => {
		const error = new RequestError("Fallback message", makeOptions());
		expect(error.message).toBe("Fallback message");
	});

	it("stores details from response.json.details", () => {
		const details = { field: "name", message: "required" };
		const error = new RequestError("msg", makeOptions({ details }));
		expect(error.details).toStrictEqual(details);
	});

	it("stores request and response", () => {
		const options = makeOptions({ message: "ok" });
		const error = new RequestError("msg", options);
		expect(error.request).toBe(options.request);
		expect(error.response).toBe(options.response);
	});

	it("state() returns response.json", () => {
		const json = { message: "ok", details: { field: "name" } };
		const error = new RequestError("msg", makeOptions(json));
		expect(error.state()).toStrictEqual(json);
	});
});
