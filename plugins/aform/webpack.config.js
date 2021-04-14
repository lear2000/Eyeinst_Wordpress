var path = require('path');
var webpack = require('webpack');

module.exports = {
  entry: {
     'export.submissions' : './assets/_build/vue/export.submissions.js',
     'integration.mc' : './assets/_build/vue/integration.mc.js'
  },
  output: {
    path: path.resolve(__dirname, 'assets/vue'),
    publicPath: './assets/vue',
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            'scss': 'vue-style-loader!css-loader!sass-loader',
            'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
          }
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/
      },
    ]
  },
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.min.js'
    }
  },
  performance: {
    hints: false
  },
  plugins: [
    new webpack.optimize.UglifyJsPlugin({
      //include: /\.min\.js$/,
      minimize: true
    })
  ],
  devtool: '#eval-source-map',
};