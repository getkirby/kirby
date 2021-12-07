module.exports = {
  extends: [
    "eslint:recommended",
    "plugin:cypress/recommended",
    "plugin:vue/recommended",
    "prettier"
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
