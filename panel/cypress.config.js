const { defineConfig } = require("cypress");

module.exports = defineConfig({
	video: false,

	e2e: {
		baseUrl: "http://sandbox.test",
		specPattern: "src/**/*.e2e.js",
		supportFile: false
	}
});
