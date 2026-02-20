import js from "@eslint/js";
import prettier from "eslint-config-prettier";
import tseslint from "typescript-eslint";
import vue from "eslint-plugin-vue";

export default [
	js.configs.recommended,
	...tseslint.configs.recommended.map((config) => ({
		...config,
		files: ["**/*.ts"]
	})),
	...vue.configs["flat/vue2-recommended"],
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
			"vue/multi-word-component-names": "off",
			"vue/require-default-prop": "off",
			"vue/require-prop-types": "error"
		},
		languageOptions: {
			ecmaVersion: 2022
		}
	}
];
