/**
 * RegExp.escape(string)
 */
RegExp.escape = (string: string): string =>
	string.replace(new RegExp("[-/\\\\^$*+?.()[\\]{}]", "gu"), "\\$&");

declare global {
	interface RegExpConstructor {
		escape(string: string): string;
	}
}

export {};
