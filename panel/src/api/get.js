export default (api) => {
	return async (path, query, options, silent = false) => {
		if (query) {
			path +=
				"?" +
				Object.keys(query)
					.filter((key) => query[key] !== undefined && query[key] !== null)
					.map((key) => key + "=" + query[key])
					.join("&");
		}

		return api.request(
			path,
			Object.assign(options ?? {}, {
				method: "GET"
			}),
			silent
		);
	};
};
