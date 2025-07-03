const Encore = require('@symfony/webpack-encore');
const { defineConfig } = require('@vue/compiler-sfc')

// Runtime-Umgebung setzen
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/app.js')

    .copyFiles({
      from: './assets/images/',
      to: 'images/[name].[ext]', // <== kein [hash]
      pattern: /\.(png|ico|svg|jpg|jpeg|webp)$/i
    })

    .enableVueLoader(() => {}, { runtimeCompilerBuild: false })

    .configureWatchOptions((watchOptions) => {
      watchOptions.poll = 250; // Nur wenn Docker/DDEV nötig
      watchOptions.ignored = /node_modules|public|\.git|\.idea|\.vscode/;;
    })

    .enablePostCssLoader() // 👈 WICHTIG für Tailwind!
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())


    .configureDefinePlugin((options) => {
      options.__VUE_OPTIONS_API__ = true
      options.__VUE_PROD_DEVTOOLS__ = false
      options.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false
    });

module.exports = Encore.getWebpackConfig();
