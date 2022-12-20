const path = require('path');
module.exports = {
    entry: [
        __dirname + '/src/js/app.js',
        __dirname + '/src/scss/app.scss'
    ],
    output: {
        path: path.resolve(__dirname, '.'), 
        filename: 'js/script.js',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [],
            }, {
                test: /\.scss$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'file-loader',
                        options: { outputPath: 'css/', name: 'style.css'}
                    },
                    'sass-loader'
                ]
            }
        ]
    },
    watchOptions: {
        ignored: /node_modules/,
    },
    
};