<?php
/**
 * Provides the framework with shortcode functionality
 * similar to how wordpress works. I used heavily from
 * the wordpress codebase and made adjustments where
 * necessary for use in the moonshine framework.
 *
 * @todo Move all functions into a single object
 * @todo document each function fully
 *
 * @version 1.0
 */


$igz_shortcode_tags = array();

function igz_addShortcode($tag,$func) {
    global $igz_shortcode_tags;
    if (is_callable($func)) { $igz_shortcode_tags[$tag] = $func; }
}

function igz_doShortcode($content) {
    global $igz_shortcode_tags;

    if (empty($igz_shortcode_tags) || !is_array($igz_shortcode_tags))
        return $content;
    // echo "here";
    $pattern = get_shortcode_regex();
    return preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag', $content);

}

function igz_getShortcodeHeader($content) {
    global $igz_shortcode_tags;

    if (empty($igz_shortcode_tags) || !is_array($igz_shortcode_tags))
        return $content;

    $pattern = get_shortcode_regex();
    if (preg_match('/'.$pattern.'/s', $content,$m)) {
        // echo 'here';
        // echo print_a($m);
        $tag = $m[2];
        $attr = shortcode_parse_atts( $m[3] );
        $the_func = "header_" . $igz_shortcode_tags[$tag];
        return call_user_func( $the_func, $attr, NULL,  $tag );
    }
}

function get_shortcode_regex() {
    global $igz_shortcode_tags;
    $tagnames = array_keys($igz_shortcode_tags);
    $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

    // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcodes()
    return '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
}

function do_shortcode_tag( $m ) {
    global $igz_shortcode_tags;

    // allow [[foo]] syntax for escaping a tag
    if ( $m[1] == '[' && $m[6] == ']' ) {
        return substr($m[0], 1, -1);
    }

    $tag = $m[2];
    $attr = shortcode_parse_atts( $m[3] );

    $the_func = $igz_shortcode_tags[$tag];

    if ( isset( $m[5] ) ) {
        // enclosing tag - extra parameter
        return $m[1] . call_user_func( $the_func, $attr, $m[5], $tag ) . $m[6];
    } else {
        // self-closing tag
        return $m[1] . call_user_func( $the_func, $attr, NULL,  $tag ) . $m[6];
    }
}

function shortcode_parse_atts($text) {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
        foreach ($match as $m) {
            if (!empty($m[1]))
                $atts[strtolower($m[1])] = stripcslashes($m[2]);
            elseif (!empty($m[3]))
                $atts[strtolower($m[3])] = stripcslashes($m[4]);
            elseif (!empty($m[5]))
                $atts[strtolower($m[5])] = stripcslashes($m[6]);
            elseif (isset($m[7]) and strlen($m[7]))
                $atts[] = stripcslashes($m[7]);
            elseif (isset($m[8]))
                $atts[] = stripcslashes($m[8]);
        }
    } else {
        $atts = ltrim($text);
    }

    return $atts;
}
