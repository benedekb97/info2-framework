<?php

namespace App;


class ViewParser
{
    const ECHO_REGEX = "/\{\{\s*([^\s]?.*[^\s])\s*\}\}/i";
    const ECHO_REGEX_REPLACE = "<?= $1; ?>";

    const LAYOUT_SECTION_REGEX = "/\@section\([\' | \"](.*)[\' | \"]\)\;?/i";
    const LAYOUT_SECTION_MIDDLE_REGEX = "/\@section\([\' | \"].*[\' | \"]\)\;?(.*)\@endsection\;?/isU";
    const LAYOUT_SECTION_END_REGEX = "/\@endsection\;?/i";

    const LAYOUT_YIELD_REGEX = "/\@yield\([\' | \"](.*)[\' | \"]\)\;?/i";

    const EXTENDS_LAYOUT_REGEX = "/\@extends\([\' | \"](.*)[\' | \"]\)\;?/i";

    public static function parse(View $view, View $referenced_by = null)
    {
        $path = $view->getPath();

        $contents = file_get_contents($path);

        if($referenced_by!=null){


            $child_path = $referenced_by->getPath();

            $child_contents = file_get_contents($child_path);

            $sections = [];

            while(preg_match(self::LAYOUT_SECTION_MIDDLE_REGEX, $child_contents, $results)) {

                $section_content = $results[1];

                preg_match(self::LAYOUT_SECTION_REGEX, $child_contents, $results);
                $section_name = $results[1];

                $sections[$section_name] = $section_content;

                $child_contents = preg_filter(self::LAYOUT_SECTION_MIDDLE_REGEX, "// SECTION DONE", $child_contents, 1);
            }

            while(preg_match(self::LAYOUT_YIELD_REGEX, $contents, $results)){
                $section_name = $results[1];

                if(isset($sections[$section_name])){
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, $sections[$section_name], $contents, 1);
                }else{
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, "", $contents, 1);
                }

            }


            while(preg_match(self::ECHO_REGEX, $contents)) {
                $contents = preg_filter(self::ECHO_REGEX, self::ECHO_REGEX_REPLACE, $contents, 1);
            }

            return $contents;
        }

        if(preg_match(self::EXTENDS_LAYOUT_REGEX, $contents, $results)) {

            $parent_view = new View($results[1]);

            return self::parse($parent_view, $view);
        }

        while(preg_match(self::ECHO_REGEX, $contents)) {
            $contents = preg_filter(self::ECHO_REGEX, self::ECHO_REGEX_REPLACE, $contents, 1);
        }

        return $contents;
    }

}