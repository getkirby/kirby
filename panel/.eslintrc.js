module.exports = {
  extends: [
    "eslint:recommended",
    "plugin:cypress/recommended",
    "plugin:vue/recommended",
    "prettier"
  ],
  rules: {
    "vue/component-definition-name-casing": "off",
    "vue/require-default-prop": "off",
    "vue/attributes-order": "error",
    "vue/require-prop-types": "error",
    "vue/html-closing-bracket-newline": [
      "error",
      {
        singleline: "never",
        multiline: "always"
      }
    ]
  }
};
