module.exports = {
  extends: [
    "eslint:recommended",
    "plugin:cypress/recommended",
    "plugin:vue/recommended"
  ],
  rules: {
    "vue/component-definition-name-casing": "off",
    "vue/require-default-prop": "off",
    "vue/attributes-order": "error",
    "vue/require-prop-types": "error",
    "vue/max-attributes-per-line": [
      "error",
      {
        "singleline": 3,
        "multiline": {
          "max": 1,
          "allowFirstLine": false
        }
      }
    ],
    "vue/html-closing-bracket-newline": [
      "error",
      {
        "singleline": "never",
        "multiline": "always"
      }
    ]
  }
}