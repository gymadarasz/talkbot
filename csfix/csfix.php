<?php declare(strict_types = 1);

if (php_sapi_name() !== 'cli') {
    throw new RuntimeException('Test can run only from command line.');
}

function fixit(string $phpfile): string
{
    $replaces = [
        '/\*\s*\@param\s+(.+)\s+\$([a-zA-Z0-9_]+)\s*\n/' => '* @param $1 \$$2 $2\n',
        '/\barray<([a-zA-Z0-9_\[\]]+)>/' => '$1[]',
        '/\/\*\*[\s*\*]*\@([.\s\*\@\w\d_\-\[\]\$\|\<\>\\\\]*\/\s*)(public|protected|protected)\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\$([a-zA-Z_][a-zA-Z0-9_]+);/' => '/**\n     * Variable \$$4\n     * @$1$2 $3 \$$4;',
        '/\/\*\*[\s*\*]*\@([.\s\*\@\w\d_\-\[\]\$\|\<\>\\\\]*\/\s*)(public|protected|protected) function ([\w\d_]+)\(/' => '/**\n     * Method $3\n     * @$1$2 function $3(',
        '/([\w\d_])\n     \* @return/' => '$1\n     *\n     * @return',
        '/(\n\s*)\bprivate\b/' => '$1protected',
        '/<\?php declare\(strict_types = 1\);\s*namespace ([a-zA-Z0-9_\\\\]+);/' => '<?php declare(strict_types = 1);\n\n/**\n *\n *\n * PHP version 7.4\n *\n * @category  PHP\n * @package   $1\n * @author    Gyula Madarasz <gyula.madarasz@gmail.com>\n * @copyright 2020 Gyula Madarasz\n * @license   Copyright (c) All rights reserved.\n * @link      this\n */\n\nnamespace $1;',
        '/;\s*class\s+([\w\d_]+)\b/' => ';\n\n/**\n * $1\n *\n * @category  PHP\n * @package   \n * @author    Gyula Madarasz <gyula.madarasz@gmail.com>\n * @copyright 2020 Gyula Madarasz\n * @license   Copyright (c) All rights reserved.\n * @link      this\n */\nclass $1 ',
        '/;\s*interface\s+([\w\d_]+)\b/' => ';\n\n/**\n * $1\n *\n * @category  PHP\n * @package   \n * @author    Gyula Madarasz <gyula.madarasz@gmail.com>\n * @copyright 2020 Gyula Madarasz\n * @license   Copyright (c) All rights reserved.\n * @link      this\n */\ninterface $1 ',
        '/\bnamespace ([a-zA-Z_][a-zA-Z_0-9\\\]*);(.*@package)\s+\*/s' => 'namespace $1;$2 $1\n *',
    ];
    
    
    if (false === ($phpcode = file_get_contents($phpfile))) {
        return 'File reading error.';
    }
    
    
    if (!preg_match('/^<\?php\s+declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $phpcode)) {
        $phpcode = preg_replace('/^<\?php\s*/', "<?php declare(strict_types = 1);\n\n", $phpcode);
    }
    
    //    $phpcode = preg_replace(
    //        '/;\s*(abstract\s+){0,1}(public\s+|protected\s+|protected\s+){0,1}(static\s+){0,1}function\s+([a-zA-Z_][a-zA-Z_0-9]*)\s*\(((([a-zA-Z_][a-zA-Z_0-9]*\s*\$[a-zA-Z_][a-zA-Z_0-9]*)(\s*,\s*){0,1})*)\)/s',
    //        ";\n\n\/**\n *\n @replaceparams $5\n */\n$1/"
    //    );
    
    foreach ($replaces as &$replace) {
        $replace = str_replace('\n', "\n", $replace);
    }
    
    while (true) {
        $replaced = preg_replace(array_keys($replaces), array_values($replaces), $phpcode);
        if (null === $replaced) {
            return 'Regex replace error.';
        }
        if ($replaced === $phpcode) {
            break;
        }
        echo '.';
        $phpcode = $replaced;
    };

    if (false === file_put_contents($phpfile, $replaced)) {
        return 'File write error.';
    }
    return '';
}

function output($output, $prevLen)
{
    $len = strlen($output);
    echo "\r$output";
    while ($prevLen > $len) {
        echo " ";
        $prevLen--;
    }
    //            usleep(100000);
    return $len;
}

function csfix($path, $ignores, $includes)
{
    $len = 0;

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $pathname = $file->getPathname();
        $included = false;
        foreach ($includes as $include) {
            if (preg_match($include, $pathname)) {
                $included = true;
                break;
            }
        }
        $ignored = false;
        foreach ($ignores as $ignore) {
            if (preg_match($ignore, $pathname)) {
                $ignored = true;
                break;
            }
        }
        if ($included && !$ignored) {
            $len = output("PHP CS Fixing: $pathname", $len);
            $error = fixit($pathname);
            if ($error) {
                echo "\nERROR: $error\n";
                return 1;
            }
        }
    }
    output("PHP CS Fixing: $path [OK]", $len);
    return 0;
}

$ignores = ['/\bconfig\b/', '/\.phtml$/'];
$includes = ['/\.php$/'];
$path = $argv[1] ?? '';
if (!$path) {
    echo "Add folder in argument\n";
    exit(1);
}
if (!is_dir($path)) {
    echo "Given folder not found: $path\n";
    exit(1);
}
$ret = csfix($path, $ignores, $includes);
echo "\n";
exit($ret);
