let mix = require("laravel-mix");
let del = require("del");

let inProduction = mix.inProduction();

mix.options({
  extractVueStyles: inProduction,
  postCss: [
    require('autoprefixer'),
  ]
});

mix.webpackConfig({
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  module: {
    rules: [
        {
            test: /\.scss$/,
            loader: "sass-loader",
            options: {
                data: `
                    @import "~@/index.scss";
                `,
            }
        }
    ],
  },
});

mix.extract();
mix.js("src/index.js", "dist/");

if (inProduction) {

  mix.copy("css/vue-styles.css", "dist/index.css");
  mix.copy("src/assets/*.*", "dist/");

  const dir = "css";

  mix.then(async () => {
    try {
      await del(dir);
    } catch (err) {
      console.error(`Error while deleting ${dir}.`);
    }
  });

}

