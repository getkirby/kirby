
const fs        = require("fs");
const path      = require("path");
const util      = require("util");
const postcss   = require("postcss");
const filter    = require("postcss-filter-rules");
const dirPseudo = require("postcss-dir-pseudo-class");

const style = path.resolve(__dirname, "../dist/css/style.css");
const rtl   = path.resolve(__dirname, "../dist/css/rtl.css");
const css   = fs.readFileSync(style, "utf8");

const convertBytes = function(bytes) {
  const sizes = ["Bytes", "KB", "MB", "GB", "TB"]

  if (bytes == 0) {
    return "n/a"
  }

  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))

  if (i == 0) {
    return bytes + " " + sizes[i]
  }

  return (bytes / Math.pow(1024, i)).toFixed(1) + " " + sizes[i]
};

const process = async () => {
  console.log('\nPost-processing CSS...\n');

  await postcss({
    plugins: [
      filter({
        filter: segment => segment.includes(":dir(rtl)")
      }),
      dirPseudo()
    ]
  })
  .process(css, { from: undefined })
  .then(result => fs.writeFileSync(rtl, result.css))

  await postcss({
    plugins: [
      filter({
        filter: segment => !segment.includes(":dir(rtl)")
      }),
      dirPseudo()
    ]
  })
  .process(css, { from: undefined })
  .then(result => fs.writeFileSync(style, result.css));

  [
    "dist/css/style.css",
    "dist/css/rtl.css"
  ].forEach(asset => {
    fs.stat(path.resolve(__dirname, "../" + asset), (err, fileStats) => {
      if (err) {
        console.error(err);
        return
      }

      console.log(util.format(
        "\x1b[33m%s\x1b[0m%s",
        asset.padEnd(22),
        convertBytes(fileStats.size).padStart(10)
      ));
    })
  })
}

process();