#!/usr/bin/php
<?php error_reporting(E_ALL);

/**
 * Unused CSS Class Finder for Directories
 *
 * Usage:   ./find-unused-css.php [css_directory] [src_directory]
 * Example: ./find-unused-css.php ./src/assets ./src/components
 */

class UnusedCssFinder
{
    const DEFAULT_JSON_FLAG = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    private $cssDir = [];
    private $srcDir = [];
    private $cssFiles = [];
    private $cssClasses = [];
    private $unusedClasses = [];
    private $sourceFiles = [];
    private $config = [
        'extendedMode' => false,
        'outputMode' => null, // ['json','unusedOnly']
        'ignoreSelectors' => [],
        'ignoreFiles' => [],
        'extensions' => [
            'css' => ['css', 'scss'],
            'source' => ['vue', 'js', 'twig'],
        ],
    ];

    public function __construct($cssDir, $srcDir, $config = [])
    {
        if (!is_dir($cssDir) || !is_dir($srcDir)) {
            die("Error: Invalid directory provided.\n");
        }

        $this->cssDir = $cssDir;
        $this->srcDir = $srcDir;

        $this->config = array_replace_recursive($this->config, $config);
        $this->cssFiles = $this->fetchFiles($cssDir, $this->config['extensions']['css']);

        if (empty($this->cssFiles)) {
            die("Error: No CSS files found in: $cssDir.\n");
        }

        $this->cssClasses = $this->extractCssClasses($this->cssFiles);
        $this->unusedClasses = $this->findUnusedClasses($srcDir);

        if (empty($this->sourceFiles)) {
            die("Error: No source files found in: $srcDir.\n");
        }

        $this->printUnusedClasses();
    }

    // Fetch all files in the directory based on allowed extensions
    private function fetchFiles($dir, $extensions)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if (in_array(pathinfo((string) $file, PATHINFO_EXTENSION), $extensions)) {
                $files[] = $file->getPathname();
            }
        }
        if ($dir === $this->srcDir) {
            $this->sourceFiles = $files;
        }
        return $files;
    }

    // Extract CSS/SCSS classes from files
    private function extractCssClasses($files)
    {
        $cssClasses = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            preg_match_all('/\.(\w[\w\-]*)\s*{/', $content, $matches);
            if (!empty($matches[1])) {
                $cssClasses = array_merge($cssClasses, $matches[1]);
            }
        }
        return array_unique($cssClasses);
    }

    // Find unused CSS classes in Vue/JS/Twig files
    private function findUnusedClasses($dir)
    {
        $unusedClasses = [];

        // Fetch all Vue, JS, and Twig files from the directory
        $srcFiles = $this->fetchFiles($dir, $this->config['extensions']['source']);

        // Search each class in Vue/JS/Twig files
        foreach ($this->cssClasses as $class) {
            if (in_array($class, $this->config['ignoreSelectors'])) {
                continue;
            }

            $isUsed = false;
            foreach ($srcFiles as $file) {
                if (in_array($file, $this->config['ignoreFiles'])) {
                    continue;
                }

                $content = file_get_contents($file);
                if (strpos($content, $class) !== false) {
                    $isUsed = true;
                    break;
                }
            }

            if (!$isUsed) {
                $unusedClasses[] = $class;
            }
        }

        return array_unique($unusedClasses);
    }

    private function getStructuredUsed($appendUnused = false)
    {
        if ($this->config['extendedMode'] !== 'true') {
            return null;
        }
        $sourceArray = [
            'selectors'   => preg_filter('/^/', '.', array_values($this->cssClasses)),
            'sourceFiles' => str_replace($this->srcDir, '', $this->sourceFiles),
            'cssFiles'    => str_replace($this->cssDir, '', $this->cssFiles),
            'config'      => $this->config,
        ];
        if ($appendUnused) {
            $sourceArray['unusedSelectors'] = preg_filter('/^/', '.', $this->unusedClasses);
        }
        switch ($this->config['outputMode']) {
            case 'json':
            case 'unusedOnly':
                $output = json_encode($sourceArray, self::DEFAULT_JSON_FLAG);
                break;
            default:
                $output = print_r($sourceArray, true);
                break;
        }
        return $output . PHP_EOL;
    }

    private function prettyPrinted()
    {
        return sprintf(<<<EOT
        ---------------------------------
        Selectors total:   %s
        Unused total:      %s
        Searched in files: %s [%s]
        CSS files total:   %s [%s]
        ---------------------------------
        EOT . PHP_EOL
        /* 1 */, count($this->cssClasses)
        /* 2 */, count($this->unusedClasses)
        /* 3 */, count($this->sourceFiles)
        /* 4 */, implode(', ', $this->config['extensions']['source'])
        /* 5 */, count($this->cssFiles)
        /* 6 */, implode(', ', $this->config['extensions']['css'])
        );
    }

    private function printUnusedClasses()
    {
        $prefixedSelectors = preg_filter('/^/', '.', $this->unusedClasses);

        if ($this->config['outputMode'] === 'unusedOnly') {
            $getStruct = $this->getStructuredUsed(true);
            exit($getStruct ? $getStruct : json_encode($prefixedSelectors, self::DEFAULT_JSON_FLAG));
        }
        echo $this->getStructuredUsed();
        echo $this->prettyPrinted();
        if (empty($this->unusedClasses)) {
            echo "No unused CSS classes found.\n";
        } else {
            echo "\nUnused CSS classes:\n";
            if ($this->config['outputMode'] === 'json') {
                echo json_encode($prefixedSelectors, self::DEFAULT_JSON_FLAG) . PHP_EOL;
            } else {
                foreach ($prefixedSelectors as $class) {
                    echo "  {$class}\n";
                }
            }
        }
    }
}

if ($argc < 2) {
    exit("Usage: ./find-unused-css.php [css_directory] [src_directory]\n");
}

$cssDir = $argv[1];
$srcDir = $argv[2] ?? $cssDir;

if (str_ends_with($cssDir, 'unused_css.json')) {
    $readConfig = json_decode(file_get_contents($cssDir), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data for "config" provided');
    }

    if (isset($readConfig['cssDir'])) {
        $cssDir = $readConfig['cssDir'];
    }

    if (isset($readConfig['srcDir'])) {
        $srcDir = $readConfig['srcDir'];
    }

    $config = $readConfig;
} else {
    parse_str(implode('&', $argv), $argv);

    $config = [
        'outputMode'      => isset($argv['outputMode']) ? $argv['outputMode'] : false,
        'extendedMode'    => isset($argv['extendedMode']) ? $argv['extendedMode'] : false,
        'ignoreFiles'     => isset($argv['ignoreFiles']) ? explode(',', $argv['ignoreFiles']) : [],
        'ignoreSelectors' => isset($argv['ignoreSelectors']) ? explode(',', $argv['ignoreSelectors']) : [],
    ];
}

new UnusedCssFinder($cssDir, $srcDir, $config);
