/**
 * Builds a YouTube embed URL
 *
 * @param url - The YouTube video URL
 * @param doNotTrack - Whether to include the DNT parameter
 */
export function youtube(url: string, doNotTrack = false): string | false {
	if (!url.match("youtu")) {
		return false;
	}

	let uri: URL;

	try {
		uri = new URL(url);
	} catch {
		return false;
	}

	const path = uri.pathname.split("/").filter((item) => item !== "");
	const first = path[0];
	const second = path[1];
	const host =
		"https://" +
		(doNotTrack === true ? "www.youtube-nocookie.com" : uri.host) +
		"/embed";

	const isYoutubeId = (id: string | null): boolean => {
		if (!id) {
			return false;
		}

		return id.match(/^[a-zA-Z0-9_-]+$/) !== null;
	};

	const query = uri.searchParams;

	// build the correct base URL for the embed,
	// the query params are appended below
	let src: string | null = null;

	switch (path.join("/")) {
		case "embed/videoseries":
		case "playlist":
			if (isYoutubeId(query.get("list")) === true) {
				src = host + "/videoseries";
			}
			break;
		case "watch":
			if (isYoutubeId(query.get("v")) === true) {
				src = host + "/" + query.get("v");

				if (query.has("t") === true) {
					query.set("start", query.get("t")!);
				}

				query.delete("v");
				query.delete("t");
			}

			break;
		default:
			// short URLs
			if (
				uri.host.includes("youtu.be") === true &&
				isYoutubeId(first) === true
			) {
				if (doNotTrack === true) {
					src = "https://www.youtube-nocookie.com/embed/" + first;
				} else {
					src = "https://www.youtube.com/embed/" + first;
				}

				if (query.has("t") === true) {
					query.set("start", query.get("t")!);
				}

				query.delete("t");
			} else if (
				["embed", "shorts"].includes(first) === true &&
				isYoutubeId(second) === true
			) {
				src = host + "/" + second;
			}
	}

	if (!src) {
		return false;
	}

	const queryString = query.toString();

	if (queryString.length) {
		src += "?" + queryString;
	}

	return src;
}

/**
 * Builds a Vimeo embed URL
 *
 * @param url - The Vimeo video URL
 * @param doNotTrack - Whether to include the DNT parameter
 */
export function vimeo(url: string, doNotTrack = false): string | false {
	let uri: URL;

	try {
		uri = new URL(url);
	} catch {
		return false;
	}

	const path = uri.pathname.split("/").filter((item) => item !== "");

	const query = uri.searchParams;
	let id: string | undefined;

	if (doNotTrack === true) {
		query.append("dnt", "1");
	}

	switch (uri.host) {
		case "vimeo.com":
		case "www.vimeo.com":
			id = path[0];
			break;
		case "player.vimeo.com":
			id = path[1];
			break;
	}

	if (!id || !id.match(/^[0-9]*$/)) {
		return false;
	}

	let src = "https://player.vimeo.com/video/" + id;

	const queryString = query.toString();

	if (queryString.length) {
		src += "?" + queryString;
	}

	return src;
}

/**
 * Builds an embed URL for the given video URL
 *
 * @param url - The video URL
 * @param doNotTrack - Whether to include the DNT parameter
 */
export function video(url: string, doNotTrack = false): string | false {
	if (url.includes("youtu") === true) {
		return youtube(url, doNotTrack);
	}

	if (url.includes("vimeo") === true) {
		return vimeo(url, doNotTrack);
	}

	return false;
}

export default {
	youtube,
	vimeo,
	video
};
