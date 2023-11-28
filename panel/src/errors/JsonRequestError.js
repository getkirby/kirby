import RequestError from "./RequestError.js";

/**
 * A JSON Request error is thrown when
 * the response to a JSON request cannot be
 * parsed. This will result in a fatal window
 * showing the unparsed response text to be able
 * to inspect what went wrong on the server
 * @since 4.0.0
 */
export default class JsonRequestError extends RequestError {
	state() {
		return {
			message: this.message,
			text: this.response.text
		};
	}
}
