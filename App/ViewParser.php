<?php

namespace App;


class ViewParser
{
    const ECHO_REGEX = "/\{\{\s*([^\s]?.*[^\s])\s*\}\}/i";
    const ECHO_REGEX_REPLACE = "<?= $1; ?>";

    const LAYOUT_SECTION_REGEX = "/\@section\([\' | \"](.*)[\' | \"]\)\;?/i";
    const LAYOUT_SECTION_MIDDLE_REGEX = "/\@section\([\' | \"].*[\' | \"]\)\;?(.*)\@endsection\;?/isU";
    const LAYOUT_SECTION_END_REGEX = "/\@endsection\;?/i";

    const PHP_IF_REGEX = "/\@if(.*)\n(.*)\@endif/isU";
    const PHP_IF_REGEX_REPLACE = "<?php if$1{ ?>$2<?php } ?>";

    const PHP_IF_ELSE_REGEX = "/\@if(.*)\n(.*)\@else(.*)\@endif/isU";
    const PHP_IF_ELSE_REGEX_REPLACE = "<?php if$1{ ?>$2<?php }else{ ?>$3<?php } ?>";

    const PHP_FOREACH_REGEX = "/\@foreach(.*)\n(.*)\@endforeach/isU";
    const PHP_FOREACH_REGEX_REPLACE = "<?php foreach$1{ ?>$2<?php } ?>\n";

    const LAYOUT_YIELD_REGEX = "/\@yield\([\' | \"](.*)[\' | \"]\)\;?/i";

    const EXTENDS_LAYOUT_REGEX = "/\@extends\([\' | \"](.*)[\' | \"]\)\;?/i";

    public static function parse(View $view, View $referenced_by = null)
    {
        $contents = "";

        $path = $view->getPath();
        if($view->getVariables() != null){
            $_SESSION['temp_passed_variables'] = $view->getVariables();

            $contents = "<?php foreach(\$_SESSION['temp_passed_variables'] as \$key => \$value){\$\$key = \$value;} ?>";
        }

        if($referenced_by != null && $referenced_by->getVariables() != null){
            $_SESSION['temp_passed_variables'] = $referenced_by->getVariables();

            $contents = "<?php foreach(\$_SESSION['temp_passed_variables'] as \$key => \$value){\$\$key = \$value;} ?>";
        }

        $contents .= file_get_contents($path);

        if ($referenced_by != null) {

            $child_path = $referenced_by->getPath();

            $child_contents = file_get_contents($child_path);

            $sections = [];

            while (preg_match(self::LAYOUT_SECTION_MIDDLE_REGEX, $child_contents, $results)) {

                $section_content = $results[1];

                preg_match(self::LAYOUT_SECTION_REGEX, $child_contents, $results);
                $section_name = $results[1];

                $sections[$section_name] = $section_content;

                $child_contents = preg_filter(self::LAYOUT_SECTION_MIDDLE_REGEX, "// SECTION DONE", $child_contents, 1);
            }

            while (preg_match(self::LAYOUT_YIELD_REGEX, $contents, $results)) {
                $section_name = $results[1];

                if (isset($sections[$section_name])) {
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, $sections[$section_name], $contents, 1);
                } else {
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, "", $contents, 1);
                }

            }

            while (preg_match(self::ECHO_REGEX, $contents)) {
                $contents = preg_filter(self::ECHO_REGEX, self::ECHO_REGEX_REPLACE, $contents, 1);
            }

            while (preg_match(self::PHP_FOREACH_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_FOREACH_REGEX, self::PHP_FOREACH_REGEX_REPLACE, $contents, 1);
            }

            while (preg_match(self::PHP_IF_ELSE_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_IF_ELSE_REGEX, self::PHP_IF_ELSE_REGEX_REPLACE, $contents, 1);
            }

            while (preg_match(self::PHP_IF_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_IF_REGEX, self::PHP_IF_REGEX_REPLACE, $contents, 1);
            }

            return $contents;
        }

        if (preg_match(self::EXTENDS_LAYOUT_REGEX, $contents, $results)) {

            $parent_view = new View($results[1]);

            return self::parse($parent_view, $view);
        }

        while (preg_match(self::ECHO_REGEX, $contents)) {
            $contents = preg_filter(self::ECHO_REGEX, self::ECHO_REGEX_REPLACE, $contents, 1);
        }

        while (preg_match(self::PHP_FOREACH_REGEX, $contents)) {
            $contents = preg_filter(self::PHP_FOREACH_REGEX, self::PHP_FOREACH_REGEX_REPLACE, $contents, 1);
        }

        while (preg_match(self::PHP_IF_ELSE_REGEX, $contents)) {
            $contents = preg_filter(self::PHP_IF_ELSE_REGEX, self::PHP_IF_ELSE_REGEX_REPLACE, $contents, 1);
        }

        while (preg_match(self::PHP_IF_REGEX, $contents)) {
            $contents = preg_filter(self::PHP_IF_REGEX, self::PHP_IF_REGEX_REPLACE, $contents, 1);
        }

        return $contents;
    }

}