export async function toJson(response: Response) {
	const text = await response.text();
	let data;

	try {
		data = JSON.parse(text);
	} catch (e) {
		window.panel.$vue.$api.onParserError({ html: text });
		return false;
	}

	return data;
}

export default { toJson };
