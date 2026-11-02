import { describe, expect, it } from "vitest";
import { failed } from "@test/unit/request";
import JsonRequestError from "./JsonRequestError";
import RequestError from "./RequestError";

describe("JsonRequestError", () => {
	it("is an instance of RequestError", () => {
		const error = new JsonRequestError(
			"Parse error",
			failed({ text: "<html>" })
		);
		expect(error).toBeInstanceOf(RequestError);
	});

	it("state() returns message and raw response text", () => {
		const error = new JsonRequestError(
			"Invalid JSON response",
			failed({ text: "<html>Internal Server Error</html>" })
		);
		expect(error.state()).toStrictEqual({
			message: "Invalid JSON response",
			text: "<html>Internal Server Error</html>"
		});
	});
});
