module.exports = {
	root: true,
	env: {
		es2022: true,
		browser: true,
		node: true
	},
	extends: [
		"eslint:recommended",
		"plugin:@typescript-eslint/recommended",
		"plugin:cypress/recommended",
		"plugin:vue/recommended",
		"prettier"
	],
	parser: "@typescript-eslint/parser",
	plugins: ["@typescript-eslint"],
	parserOptions: {
		ecmaVersion: 13,
		sourceType: "module"
	},
	overrides: [
		{
			files: ["*.vue"],
			parser: "vue-eslint-parser",
			parserOptions: {
				parser: "@typescript-eslint/parser"
			},
			rules: {
				"no-undef": "off"
			}
		}
	],
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
	}
};
