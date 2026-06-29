/**
 * RegExp.escape(string)
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
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
