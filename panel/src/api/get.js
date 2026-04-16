export default (api) => {
	return async (path, query, options, silent = false) => {
		if (query) {
			const search = buildQuery(query).toString();

			if (search) {
				path += "?" + search;
			}
		}

		return api.request(path, { ...options, method: "GET" }, silent);
	};
};
