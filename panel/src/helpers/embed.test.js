import { describe, expect, it } from "vitest";
import embed from "./embed.js";

describe.concurrent("$helper.embed()", () => {
	const tests = [
		// YouTube
		[
			"https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"http://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"http://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"http://www.youtube.com/embed/d9NF2edxy-M",
			"https://www.youtube.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M"
		],
		[
			"http://www.youtube.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"http://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"http://www.youtube.com/shorts/z-zDhFM_oAo",
			"https://www.youtube.com/embed/z-zDhFM_oAo",
			"https://www.youtube-nocookie.com/embed/z-zDhFM_oAo"
		],
		[
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M"
		],
		[
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M"
		],
		[
			"https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M&t=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"https://www.youtube-nocookie.com/watch?test=value&v=d9NF2edxy-M&t=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&start=10"
		],
		[
			"https://www.youtube-nocookie.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"https://www.youtube-nocookie.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"http://www.youtube.com/watch?v=d9NF2edxy-M",
			"https://www.youtube.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M"
		],
		[
			"http://www.youtube.com/watch?test=value&v=d9NF2edxy-M",
			"https://www.youtube.com/embed/d9NF2edxy-M?test=value",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value"
		],
		[
			"http://www.youtube.com/watch?v=d9NF2edxy-M&t=10",
			"https://www.youtube.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"https://www.youtube.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"https://www.youtube.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys",
			"https://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys"
		],
		[
			"https://www.youtu.be/d9NF2edxy-M",
			"https://www.youtube.com/embed/d9NF2edxy-M",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M"
		],
		[
			"https://www.youtu.be/d9NF2edxy-M?t=10",
			"https://www.youtube.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"https://youtu.be/d9NF2edxy-M?t=10",
			"https://www.youtube.com/embed/d9NF2edxy-M?start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10"
		],
		[
			"https://www.youtu.be/d9NF2edxy-M?test=value&t=10",
			"https://www.youtube.com/embed/d9NF2edxy-M?test=value&start=10",
			"https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&start=10"
		],

		// Vimeo
		[
			"https://vimeo.com/239882943",
			"https://player.vimeo.com/video/239882943",
			"https://player.vimeo.com/video/239882943?dnt=1"
		],
		[
			"https://vimeo.com/239882943?test=value",
			"https://player.vimeo.com/video/239882943?test=value",
			"https://player.vimeo.com/video/239882943?test=value&dnt=1"
		],
		[
			"https://player.vimeo.com/video/239882943",
			"https://player.vimeo.com/video/239882943",
			"https://player.vimeo.com/video/239882943?dnt=1"
		],
		[
			"https://player.vimeo.com/video/239882943?test=value",
			"https://player.vimeo.com/video/239882943?test=value",
			"https://player.vimeo.com/video/239882943?test=value&dnt=1"
		],

		// invalid URLs
		["https://getkirby.com", false, false],
		["https://youtube.com/imprint", false, false],
		["https://www.youtu.be", false, false],
		["https://www.youtube.com/watch?list=zv=21HuwjmuS7A&index=1", false, false],
		["https://youtube.com/watch?v=öööö", false, false],
		["https://vimeo.com", false, false],
		["https://vimeo.com/öööö", false, false]
	];

	it("should create the right embed URLs", () => {
		for (const test of tests) {
			const input = test[0];
			const expected = test[1];
			const result = embed.video(input);

			expect(result).toBe(expected);
		}
	});

	it("should work with doNotTrack flag for youtube and vimeo videos", () => {
		for (const test of tests) {
			const input = test[0];
			const expected = test[2];
			const result = embed.video(input, true);

			expect(result).toBe(expected);
		}
	});
});
