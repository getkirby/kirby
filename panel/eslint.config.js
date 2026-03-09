import globals from "globals";
import js from "@eslint/js";
import prettier from "eslint-config-prettier";
import vitest from "@vitest/eslint-plugin";
import tseslint from "typescript-eslint";
import vue from "eslint-plugin-vue";

export default [
	js.configs.recommended,
	...tseslint.configs.recommended.map((config) => ({
		...config,
		files: ["**/*.ts"]
	})),
	...vue.configs["flat/recommended"],

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
		ignores: ["src/libraries/dayjs*.ts"],
		rules: {
			"no-restricted-imports": [
				"error",
				{
					paths: [
						{
							name: "dayjs",
							message: "Import dayjs from '@/libraries/dayjs' instead."
						}
					]
				}
			]
		}
	},
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
			"vue/html-indent": "off",
			"vue/multi-word-component-names": "off",
			"vue/require-default-prop": "off",
			"vue/require-explicit-emits": "warn",
			"vue/require-prop-types": "error"
		},
		languageOptions: {
			sourceType: "module",
			ecmaVersion: 2022,
			globals: {
				...globals.browser
			}
		}
	}
];
