import { request } from "@/panel/request";
import { ltrim, rtrim } from "@/helpers/string";

export default function (api) {
	return async (path, options = {}) => {
		const method = options.method ?? "GET";
		const url = rtrim(api.endpoint, "/") + "/" + ltrim(path, "/");

		// Rewrite non-GET/POST methods as POST when method override is enabled
		const overrideMethod =
			api.methodOverride && method !== "GET" && method !== "POST";

		const { response } = await request(url, {
			...options,
			csrf: api.csrf,
			method: overrideMethod ? "POST" : method,
			headers: {
				"x-language": api.language,
				...(overrideMethod ? { "x-http-method-override": method } : {}),
				...(options.headers ?? {})
			}
		});

		const data = response.json;

		// simplify the response
		if (data.data && data.type === "model") {
			return data.data;
		}

		return data;
	};
}
