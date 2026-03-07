import { describe, expect, it } from "vitest";
import type { PanelResponse } from "@/panel/request";
import JsonRequestError from "./JsonRequestError";
import RequestError from "./RequestError";

function makeOptions(text: string): {
	request: Request;
	response: PanelResponse;
} {
	return {
		request: new Request("https://example.com/api"),
		response: {
			headers: new Headers(),
			json: {},
			ok: false,
			status: 200,
			statusText: "OK",
			text,
			url: "https://example.com/api"
		}
	};
}

describe("JsonRequestError", () => {
	it("is an instance of RequestError", () => {
		const error = new JsonRequestError("Parse error", makeOptions("<html>"));
		expect(error).toBeInstanceOf(RequestError);
	});

	it("state() returns message and raw response text", () => {
		const error = new JsonRequestError(
			"Invalid JSON response",
			makeOptions("<html>Internal Server Error</html>")
		);
		expect(error.state()).toStrictEqual({
			message: "Invalid JSON response",
			text: "<html>Internal Server Error</html>"
		});
	});
});
