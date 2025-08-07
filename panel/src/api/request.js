import { responder } from "@/panel/request.js";
import { toLowerKeys } from "@/helpers/object.js";
import { ltrim, rtrim } from "@/helpers/string";

export default (api) => {
	return async (path, options = {}) => {
		// create options object
		options = {
			cache: "no-store",
			credentials: "same-origin",
			mode: "same-origin",
			...options
		};

		// make sure to keep essential headers
		// unless they are explicitely overwritten
		options.headers = {
			"content-type": "application/json",
			"x-csrf": api.csrf,
			"x-language": api.language,
			...toLowerKeys(options.headers ?? {})
		};

		// adapt headers for all non-GET and non-POST methods
		if (
			api.methodOverride &&
			options.method !== "GET" &&
			options.method !== "POST"
		) {
			options.headers["x-http-method-override"] = options.method;
			options.method = "POST";
		}

		// remove null headers
		for (const key in options.headers) {
			if (options.headers[key] === null) {
				delete options.headers[key];
			}
		}

		// build the request URL
		options.url = rtrim(api.endpoint, "/") + "/" + ltrim(path, "/");

		// The request object is a nice way to access all the
		// important parts later in errors for example
		const request = new Request(options.url, options);

		// fetch the resquest's response
		const { response } = await responder(request, await fetch(request));

		let data = response.json;

		// simplify the response
		if (data.data && data.type === "model") {
			data = data.data;
		}

		return data;
	};
};
