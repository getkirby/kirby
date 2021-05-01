
const fs = require("fs");
const glob = require("glob");
const path = require("path");
const docgen = require("vue-docgen-api");
 
glob("src/components/**/*.vue", async (err, files) => {
  if (err) {
    return console.log(err);
  }

  const parsing = files.map(async file => {
    let data = await docgen.parse(file, {
      alias: { "@": path.resolve(__dirname, "../src/") },
    });
    data.srcFile = file;
    return data;
  });

  const components = await Promise.all(parsing);
  fs.writeFileSync(
    path.resolve(__dirname, "../dist/ui.json"), 
    JSON.stringify(components)
  );
});


