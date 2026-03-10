/**
 * Adapted from https://github.com/bubkoo/natsort
 *
 * Copyright (c) 2016 W.Y.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
export type SortOptions = {
	/*
	 * Desc sorting.
	 */
	desc?: boolean;
	/*
	 * Case-Insensitive sorting.
	 */
	insensitive?: boolean;
};

/* eslint-disable no-control-regex, no-useless-escape */
export default function natsort(options: SortOptions = {}) {
	const ore = /^0/;
	const sre = /\s+/g;
	const tre = /^\s+|\s+$/g;
	// unicode
	const ure = /[^\x00-\x80]/;
	// hex
	const hre = /^0x[0-9a-f]+$/i;
	// numeric
	const nre =
		/(0x[\da-fA-F]+|(^[\+\-]?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?(?=\D|\s|$))|\d+)/g;
	// datetime
	const dre =
		/(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/; // tslint:disable-line
	const toLowerCase =
		String.prototype.toLocaleLowerCase || String.prototype.toLowerCase;

	const GREATER = options.desc ? -1 : 1;
	const SMALLER = -GREATER;
	const normalize = options.insensitive
		? (s: string | number) => toLowerCase.call(`${s}`).replace(tre, "")
		: (s: string | number) => `${s}`.replace(tre, "");

	function tokenize(s: string): string[] {
		return s
			.replace(nre, "\0$1\0")
			.replace(/\0$/, "")
			.replace(/^\0/, "")
			.split("\0");
	}

	function parse(s: string, l: number) {
		// normalize spaces; find floats not starting with '0',
		// string or 0 if not defined (Clint Priest)
		return (
			((!s.match(ore) || l === 1) && parseFloat(s)) ||
			s.replace(sre, " ").replace(tre, "") ||
			0
		);
	}

	return function (a: string | number, b: string | number): number {
		// trim pre-post whitespace
		const aa = normalize(a);
		const bb = normalize(b);

		// return immediately if at least one of the values is empty.
		// empty string < any others
		if (!aa && !bb) {
			return 0;
		}

		if (!aa && bb) {
			return SMALLER;
		}

		if (aa && !bb) {
			return GREATER;
		}

		// tokenize: split numeric strings and default strings
		const aArr = tokenize(aa);
		const bArr = tokenize(bb);

		// hex or date detection
		const aHex = aa.match(hre);
		const bHex = bb.match(hre);
		const av =
			aHex && bHex
				? parseInt(aHex[0], 16)
				: aArr.length !== 1 && Date.parse(aa);
		const bv =
			aHex && bHex
				? parseInt(bHex[0], 16)
				: (av && bb.match(dre) && Date.parse(bb)) || null;

		// try and sort Hex codes or Dates
		if (bv) {
			if (av === bv) {
				return 0;
			}

			if ((av as number) < bv) {
				return SMALLER;
			}

			if ((av as number) > bv) {
				return GREATER;
			}
		}

		const al = aArr.length;
		const bl = bArr.length;

		// handle numeric strings and default strings
		for (let i = 0, l = Math.max(al, bl); i < l; i += 1) {
			const af = parse(aArr[i] || "", al);
			const bf = parse(bArr[i] || "", bl);

			// handle numeric vs string comparison.
			// numeric < string
			if (isNaN(af as number) !== isNaN(bf as number)) {
				return isNaN(af as number) ? GREATER : SMALLER;
			}

			// if unicode use locale comparison
			if (
				ure.test((af as string) + (bf as string)) &&
				(af as string).localeCompare
			) {
				const comp = (af as string).localeCompare(bf as string);

				if (comp > 0) {
					return GREATER;
				}

				if (comp < 0) {
					return SMALLER;
				}

				if (i === l - 1) {
					return 0;
				}
			}

			if (af < bf) {
				return SMALLER;
			}

			if (af > bf) {
				return GREATER;
			}

			if (`${af}` < `${bf}`) {
				return SMALLER;
			}

			if (`${af}` > `${bf}`) {
				return GREATER;
			}
		}

		return 0;
	};
}
