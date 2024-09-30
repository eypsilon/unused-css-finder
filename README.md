# Unused CSS Finder for Twig or Vue Projects

Authors: ChatGPT by OpenAI & Engin Ypsilon by classic::Parents()

## Overview

`UnusedCssFinder` is a lightweight PHP-based tool designed to help developers clean up unused CSS classes in projects that use Vue.js, Twig, or similar templating systems. By scanning both the CSS and component files, this tool identifies CSS selectors that are defined but not used, making it easier to reduce file size, optimize performance, and maintain clean code.

It's an efficient, no-frills solution that doesn't require additional dependencies or frameworks, ideal for quickly assessing unused CSS without heavy setup.

## Features

- Recursively fetches and reads all CSS classes from `.css` or `.scss` files.
  - Note: This tool currently supports `.css` and `.scss` files. `.sass` files are not fully supported due to differences in syntax.
- Scans `.vue`, `.js`, and `.twig` files to detect unused CSS classes.
- Outputs a list of CSS classes that are not used in your project, which can likely be removed.
- Allows custom configuration, including ignored selectors and files.
- Optional extended mode for more detailed output, including scanned files and class details.

## Requirements

- PHP 7.4 or higher
- A project containing Vue.js, Twig, or similar component-based files (`.vue`, `.js`, or `.twig`)
- CSS/SCSS files with class definitions

### Installation

- Clone the repository:

```bash
git clone https://github.com/eypsilon/unused-css-finder.git
cd unused-css-finder
```

- Make the script executable:

```bash
chmod +x find-unused-css.php
```

__Usage__

To use the script, run the following command in your terminal:

```bash
# The more precise the directories are set,
# the more accurate results you will get.
./find-unused-css.php <path/to/css> <path/to/components>
```

__Example:__

```bash
./find-unused-css.php ./src/assets ./src/components
```

The tool will output a list of unused CSS classes or a message indicating that no unused classes were found.

#### Optional Config

You can specify additional configurations by passing parameters such as ignored selectors or files:

```bash
# available
$config = [
    'extendedMode'    => (string) 'false',                // if 'true' returns the lists of scanned files and a list with all found selectors
    'outputMode'      => null,                            // set to "json" to get the resulting response as JSON Object, `unusedOnly` for a valid JSON response
    'ignoreSelectors' => ['mt-button', 'some-box-class'], // example of ignored selectors
    'ignoreFiles'     => ['/path/to/ignore/file.vue'],    // example of ignored files
];
```

#### Set an Alias (Optional)

For convenience, you can create an alias to run the tool easily:

1. Open `.bash_aliases`:

```bash
sudo nano ~/.bash_aliases
```

2. Add the following alias:

```bash
alias GetUnusedCss='~/unused-css-finder/find-unused-css.php'
```

3. Refresh your aliases:

```bash
source ~/.bash_aliases
```

Now you can use the alias to run the script:

```bash
GetUnusedCss <path/to/css> <path/to/components>
```

To use with optional configurations:

```bash
GetUnusedCss <path/to/css> <path/to/components> extendedMode='true' outputMode='json' ignoreSelectors='mt-button,some-box-class' ignoreFiles='/path/to/ignore/file.vue'
```

#### Configuration file

Instead of passing the configuration options via arguments in the terminal, you can also create a JSON configuration file with all the necessary data for your project. This makes it easy to manage and reuse different configurations for multiple projects or environments. The filename needs to end with `'unused_css.json'`.

__Example Configuration__

```json
{
    "cssDir": "/path/to/css",         // The directory where your CSS files are located
    "srcDir": "/path/to/components",  // The directory where your Vue components are located
    "extendedMode": "true",           // If true, provides additional details on searched files and CSS files
    "outputMode": "json",             // ['json','unusedOnly'] Specify output format ('json' or plain text)
    "ignoreSelectors": [],            // A list of CSS selectors to ignore during the search (e.g., framework-specific classes)
    "ignoreFiles": [],                // A list of specific files to exclude from the search
    "extensions": {
        "css": [
            "css",
            "scss"
        ], // Supported file extensions for CSS; .sass files are not fully supported due to differences in syntax.
        "source": [
            "vue",
            "js",
            "twig"
        ] // Supported file extensions for source files (Vue, JS, Twig, etc.)
    }
}
```

__Using the Configuration File__

Once you've created the configuration file, you can pass it as an argument when running the script. The script will use the settings defined in the JSON file instead of arguments passed directly in the terminal.

__Example Command:__

```bash
# Using an alias
GetUnusedCss /path/to/config/unused_css.json

# Without an alias, using the script directly
./find-unused-css.php /path/to/config/unused_css.json
```

__Configuration Options Explained__

- `cssDir`: The directory where your CSS/SCSS files are stored. The script will recursively search this directory for all files with the extensions defined in the `extensions` → `css` field.
- `srcDir`: The directory where your Vue, JS, or Twig component files are stored. This is where the script will search for CSS class usage.
- `extendedMode`: When set to `true`, the script will output additional details about the files it has scanned, including the CSS selectors and source files. This can be helpful for debugging or tracking what files were included in the search.
- `outputMode`: Defines how the output will be displayed. Set it to `'json'` for structured JSON output, which is easier to integrate into automated systems or use `'plain'` for a standard text output. If you only want a list with unused selectors in a JSON array, set `'unusedOnly'`.
- `ignoreSelectors`: This is a list of CSS selectors that should be excluded from the search. For example, if you're using a UI framework like Bootstrap or Tailwind, you may want to exclude some of its global classes (e.g., `btn-primary`, `text-lg`) from the search to avoid false positives.
- `ignoreFiles`: A list of specific files that should be skipped during the search. You can use this to exclude generated files, temporary files, or any files that you don't want the script to analyze.
- `extensions`:
    - `css`: Specifies which CSS-related file extensions the script should look for in the `cssDir`. Typically includes `css` and `scss`.
    - `source`: Specifies which file extensions the script should search in the `srcDir` for class usage. This typically includes `vue`, `js`, and `twig` but can be extended to other formats like `html`.

__Examples__

Example 1: Basic Usage with Arguments

```bash
./find-unused-css.php ./src/assets ./src/components
```
Example 2: Advanced Usage with Configuration File

```bash
GetUnusedCss ./src/unused_css.json
```

Example 3: Ignoring Selectors and Files

```json
{
    "cssDir": "/path/to/css",
    "srcDir": "/path/to/components",
    "extendedMode": "false",
    "outputMode": "plain",
    "ignoreSelectors": ["mt-button", "hidden"],
    "ignoreFiles": ["/path/to/components/temp.vue"],
    "extensions": {
        "css": [
            "css",
            "scss"
        ],
        "source": [
            "vue",
            "js",
            "twig"
        ]
    }
}
```

You can then run this configuration with:

```bash
./find-unused-css.php /path/to/config/unused_css.json
```

###### Disclaimer

Please note that this tool is a lightweight, approximately 200-line script designed to give a general overview of potentially unused CSS selectors in your projects. While it does a good job of finding most unused classes, it may miss certain scenarios, such as:

- Dynamically generated class names (e.g., classes added via JavaScript, Vue mixins, or other logic).
- Classes used in inline styles or within style attributes.
- Classes generated by CSS frameworks or libraries where the class names are dynamically assembled (e.g., Tailwind CSS, Bootstrap with mixins).
- Files not covered by the defined extensions (e.g., templates using another engine).

This tool is not a replacement for more comprehensive code quality solutions, but rather a fast and simple utility to help developers clean up unused CSS. It should be used in combination with manual review or other tools for more complex use cases.

###### Contribution

Feel free to submit issues or pull requests if you'd like to improve or extend this tool. Contributions are always welcome!

###### License

This project is licensed under the MIT License.

