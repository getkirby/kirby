import { describe, expect, it } from "vitest";
import ratio from "./ratio";

describe("$helper.ratio()", () => {
	const cases: {
		name: string;
		fraction: unknown;
		vertical?: boolean;
		expected: string;
	}[] = [
		{
			name: "uses the default fraction",
			fraction: undefined,
			expected: "66.67%"
		},
		{
			name: "calculates padding for 16/9",
			fraction: "16/9",
			expected: "56.25%"
		},
		{
			name: "returns 100% when the first part is 0",
			fraction: "0/16",
			expected: "100%"
		},
		{
			name: "returns 100% when the second part is 0",
			fraction: "16/0",
			expected: "100%"
		},
		{
			name: "returns 100% for a non-fraction string",
			fraction: "2",
			expected: "100%"
		},
		{ name: "returns 100% for a number", fraction: 1, expected: "100%" },
		{ name: "returns 100% for an object", fraction: {}, expected: "100%" },
		{
			name: "supports horizontal orientation",
			fraction: "3/2",
			vertical: false,
			expected: "150%"
		}
	];

	it.each(cases)("$name", ({ fraction, vertical, expected }) => {
		expect(ratio(fraction as string | undefined, undefined, vertical)).toBe(
			expected
		);
	});
});
