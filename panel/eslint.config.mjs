import globals from "globals";
import js from "@eslint/js";
import prettier from "eslint-config-prettier";
import vitest from "@vitest/eslint-plugin";
import vue from "eslint-plugin-vue";

export default [
	js.configs.recommended,
	...vue.configs["flat/vue2-recommended"],

	// Vitest rules for test files
	{
		files: ["**/*.test.js", "**/*.test.ts", "**/*.spec.js", "**/*.spec.ts"],
		plugins: {
			vitest
		},
		rules: {
			...vitest.configs.recommended.rules,
			"vitest/valid-title": "off"
		},
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.node,
				app: "readonly"
			}
		}
	},

	prettier,
	{
		rules: {
			"vue/attributes-order": "error",
			"vue/component-definition-name-casing": "off",
			"vue/html-closing-bracket-newline": [
				"error",
				{
					singleline: "never",
					multiline: "always"
				}
			],
			"vue/multi-word-component-names": "off",
			"vue/require-default-prop": "off",
			"vue/require-explicit-emits": "warn",
			"vue/require-prop-types": "error"
		},
		languageOptions: {
			ecmaVersion: 2022,
			globals: {
				...globals.browser
			}
		}
	}
];
