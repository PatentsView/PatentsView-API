const webpack = require("webpack")
const path = require("path")
const VueLoaderPlugin = require("vue-loader/lib/plugin")

const config = {
    entry: "./header_footer_src/index.js",
    output: {
        path: path.resolve(__dirname, "querymodule/public_html/js"),
        filename: "header_footer.js"
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: "vue-loader"
            },
            {
                test: /\.js$/,
                use: "babel-loader",
                exclude: /node_modules/
            },
            {
                test: /\.css$/,
                use: ["vue-style-loader", "css-loader"]
            },
            {
                test: /\.svg$/,
                use: "file-loader"
            },
            {
                test: /\.png$/,
                use: [
                    {
                        loader: "url-loader",
                        options: {
                            mimetype: "image/png"
                        }
                    }
                ]
            }
        ]
    },
    resolve: {
        extensions: [".js", ".vue"]
    },
    plugins: [new VueLoaderPlugin()]
}

module.exports = config
