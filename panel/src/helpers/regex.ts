/**
 * RegExp.escape(string)
 */
RegExp.escape = function (string) {
	return string.replace(new RegExp("[-/\\\\^$*+?.()[\\]{}]", "gu"), "\\$&");
};

declare global {
	interface RegExpConstructor {
		escape(string: string): string;
	}
}

export {};
