let mix = require("laravel-mix");
let path = require("path");

mix
  .setPublicPath("dist")
  .js("resources/js/entry.js", "js")
  .vue({ version: 3 })
  .webpackConfig({
    externals: {
      vue: "Vue",
    },
    output: {
      uniqueName: "outl1ne/nova-account-settings",
    },
  })
  .alias({
    "laravel-nova": path.join(
      __dirname,
      "vendor/laravel/nova/resources/js/mixins/packages.js"
    ),
  });
