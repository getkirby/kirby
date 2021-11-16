const fs = require("fs");

process.env.VUE_APP_DEV_SERVER = "https://sandbox.kirby.test";

module.exports = {
  host: "sandbox.kirby.test",
  https: {
    key: fs.readFileSync('/Users/luX/Library/Application Support/Caddy/certificates/local/sandbox.kirby.test/sandbox.kirby.test.key'),
    cert: fs.readFileSync('/Users/luX/Library/Application Support/Caddy/certificates/local/sandbox.kirby.test/sandbox.kirby.test.crt')
  }
};
