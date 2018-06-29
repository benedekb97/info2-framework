<?php

namespace App\Internal;


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

    /**
     * @param View $view
     * @param View|null $referenced_by
     * @return null|string|string[]
     */
    public static function parse(View $view, View $referenced_by = null)
    {
        // Initialise contents of temporary file
        $contents = "";

        // Get path for view 'fasz' file
        $path = $view->getPath();

        // If there are variables passed in the view, initialise them in the temporary file
        if($view->getVariables() != null){
            $_SESSION['temp_passed_variables'] = $view->getVariables();

            // Go through all the variables and set new local variable equal to it
            $contents = "<?php foreach(\$_SESSION['temp_passed_variables'] as \$key => \$value){\$\$key = \$value;} ?>";
        }

        // Same, but is called if the original view is passed on to the parser again
        if($referenced_by != null && $referenced_by->getVariables() != null){
            $_SESSION['temp_passed_variables'] = $referenced_by->getVariables();

            $contents = "<?php foreach(\$_SESSION['temp_passed_variables'] as \$key => \$value){\$\$key = \$value;} ?>";
        }

        // Add the contents of the view to the temp file contents
        $contents .= file_get_contents($path);

        // Check if the view has been referenced by another (If it is a layout extended by a view)
        if ($referenced_by != null) {

            // Path of the 'child' view
            $child_path = $referenced_by->getPath();

            // Contents of the 'child' view
            $child_contents = file_get_contents($child_path);

            // Array of the sections of the extended view (to be filled)
            $sections = [];

            // Fills the array of sections
            while (preg_match(self::LAYOUT_SECTION_MIDDLE_REGEX, $child_contents, $results)) {

                // The contents of the section
                $section_content = $results[1];

                // Find the name of the section
                preg_match(self::LAYOUT_SECTION_REGEX, $child_contents, $results);
                $section_name = $results[1];

                // add to section array
                $sections[$section_name] = $section_content;

                // Remove the finished section from the child file, so regex doesn't find it again
                $child_contents = preg_filter(self::LAYOUT_SECTION_MIDDLE_REGEX, "// SECTION DONE", $child_contents, 1);
            }

            // Go through the parent view and replace the @yield tag with the section from the child view
            while (preg_match(self::LAYOUT_YIELD_REGEX, $contents, $results)) {
                $section_name = $results[1];

                // If the child view has fulfilled the yield replace @yield with that, if not delete
                if (isset($sections[$section_name])) {
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, $sections[$section_name], $contents, 1);
                } else {
                    $contents = preg_filter(self::LAYOUT_YIELD_REGEX, "", $contents, 1);
                }

            }

            // replace {{ }} with echo tags
            while (preg_match(self::ECHO_REGEX, $contents)) {
                $contents = preg_filter(self::ECHO_REGEX, self::ECHO_REGEX_REPLACE, $contents, 1);
            }

            // replace @foreach ... @endforeach with php equivalent
            while (preg_match(self::PHP_FOREACH_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_FOREACH_REGEX, self::PHP_FOREACH_REGEX_REPLACE, $contents, 1);
            }

            // replace @if ... @else ... $endif with php equivalent
            while (preg_match(self::PHP_IF_ELSE_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_IF_ELSE_REGEX, self::PHP_IF_ELSE_REGEX_REPLACE, $contents, 1);
            }

            // replace @if ... @endif with php equivalent
            while (preg_match(self::PHP_IF_REGEX, $contents)) {
                $contents = preg_filter(self::PHP_IF_REGEX, self::PHP_IF_REGEX_REPLACE, $contents, 1);
            }

            // return the contents of the temporary file to the Router
            return $contents;
        }

        // Check if the current view extends another layout
        if (preg_match(self::EXTENDS_LAYOUT_REGEX, $contents, $results)) {
            $parent_view = new View($results[1]);

            // Call parse function again, with $referenced_by being the current view (the view that extends the layout)
            return self::parse($parent_view, $view);
        }

        // SAME AS IN "if($referenced_by != null)" BLOCK
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