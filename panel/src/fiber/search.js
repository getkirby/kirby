export default async function (type, query, options = {}) {
	// Skip API call if query empty
	if (query === null || query === "") {
		throw Error("Empty query");
	}

	const response = await this.$fiber.request("search/" + type, {
		query: {
			query: query
		},
		type: "$search",
		...options
	});

	if (response === false) {
		throw Error("JSON parsing failed");
	}

	return response;
}
