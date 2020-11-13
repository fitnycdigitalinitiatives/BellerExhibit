<?php

function public_nav_main_bootstrap()
{
    $partial = array('common/menu-partial.phtml', 'default');
    $nav = public_nav_main();  // this looks like $this->navigation()->menu() from Zend
    $nav->setPartial($partial);
    return $nav->render();
}
function public_nav_items_bootstrap()
{
    $partial = array('common/menu-items-partial.phtml', 'default');
    if (!isset($navArray)) {
        $navArray = array(
            array(
                'label' =>__('Browse All'),
                'uri' => url('items/browse'),
            ));
        if (total_records('Tag')) {
            $navArray[] = array(
                    'label' => __('Browse by Tag'),
                    'uri' => url('items/tags')
                );
        }
        $navArray[] = array(
                'label' => __('Advanced Search'),
                'uri' => url('items/search')
            );
    }
    $nav = nav($navArray, 'public_navigation_items');
    $nav->setPartial($partial);
    return $nav->render();
}
function browse_sort_links_bootstrap($links, $wrapperTags = array())
{
    $sortParam = Omeka_Db_Table::SORT_PARAM;
    $sortDirParam = Omeka_Db_Table::SORT_DIR_PARAM;
    $req = Zend_Controller_Front::getInstance()->getRequest();
    $currentSort = trim($req->getParam($sortParam));
    $currentDir = trim($req->getParam($sortDirParam));
    $defaults = array(
        'link_tag' => 'li',
        'list_tag' => 'ul',
        'link_attr' => array( 'role' => 'presentation' ),
        'list_attr' => array( 'id' => 'sort-links-list', 'class' => 'dropdown-menu' )
    );
    $sortlistWrappers = array_merge($defaults, $wrapperTags);
    $linkAttrArray = array();
    foreach ($sortlistWrappers['link_attr'] as $key => $attribute) {
        $linkAttrArray[$key] = $key . '="' . html_escape($attribute) . '"';
    }
    $linkAttr = join(' ', $linkAttrArray);
    $listAttrArray = array();
    foreach ($sortlistWrappers['list_attr'] as $key => $attribute) {
        $listAttrArray[$key] = $key . '="' . html_escape($attribute) . '"';
    }
    $listAttr = join(' ', $listAttrArray);
    $sortlist = '';
    if (!empty($sortlistWrappers['list_tag'])) {
        $sortlist .= "<{$sortlistWrappers['list_tag']} $listAttr>";
    }
    foreach ($links as $label => $column) {
        if ($column) {
            $urlParams = $_GET;
            $urlParams[$sortParam] = $column;
            $class = '';
            if ($currentSort && $currentSort == $column) {
                if ($currentDir && $currentDir == 'd') {
                    $class = 'class="active sorting desc"';
                    $urlParams[$sortDirParam] = 'a';
                } else {
                    $class = 'class="active sorting asc"';
                    $urlParams[$sortDirParam] = 'd';
                }
            }
            $url = html_escape(url(array(), null, $urlParams));
            if ($sortlistWrappers['link_tag'] !== '') {
                $sortlist .= "<{$sortlistWrappers['link_tag']} $class $linkAttr><a href=\"$url\">$label</a></{$sortlistWrappers['link_tag']}>";
            } else {
                $sortlist .= "<a href=\"$url\" $class $linkAttr>$label</a>";
            }
        } else {
            $sortlist .= "<{$sortlistWrappers['link_tag']}>$label</{$sortlistWrappers['link_tag']}>";
        }
    }
    if (!empty($sortlistWrappers['list_tag'])) {
        $sortlist .= "</{$sortlistWrappers['list_tag']}>";
    }
    return $sortlist;
}

function item_search_filters_bootstrap(array $params = null)
{
    if ($params === null) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $requestArray = $request->getParams();
    } else {
        $requestArray = $params;
    }

    $db = get_db();
    $displayArray = array();
    foreach ($requestArray as $key => $value) {
        if ($value != null) {
            $filter = ucfirst($key);
            $displayValue = null;
            switch ($key) {
                    case 'type':
                        $filter = 'Item Type';
                        $itemType = $db->getTable('ItemType')->find($value);
                        if ($itemType) {
                            $displayValue = $itemType->name;
                        }
                        break;

                    case 'collection':
                        $collection = $db->getTable('Collection')->find($value);
                        if ($collection) {
                            $displayValue = strip_formatting(
                                metadata(
                                    $collection,
                                    array('Dublin Core', 'Title'),
                                    array('no_escape' => true)
                                )
                            );
                        }
                        break;

                    case 'user':
                        $user = $db->getTable('User')->find($value);
                        if ($user) {
                            $displayValue = $user->name;
                        }
                        break;

                    case 'public':
                    case 'featured':
                        $displayValue = ($value == 1 ? __('Yes') : $displayValue = __('No'));
                        break;

                    case 'search':
                    case 'tags':
                    case 'range':
                        $displayValue = $value;
                        break;
                }
            if ($displayValue) {
                $displayArray[$filter] = $displayValue;
            }
        }
    }

    $displayArray = apply_filters('item_search_filters', $displayArray, array('request_array' => $requestArray));

    // Advanced needs a separate array from $displayValue because it's
    // possible for "Specific Fields" to have multiple values due to
    // the ability to add fields.
    if (array_key_exists('advanced', $requestArray)) {
        $advancedArray = array();
        foreach ($requestArray['advanced'] as $i => $row) {
            if (!$row['element_id'] || !$row['type']) {
                continue;
            }
            $elementID = $row['element_id'];
            $elementDb = $db->getTable('Element')->find($elementID);
            $element = __($elementDb->name);
            $type = __($row['type']);
            $query = $row['terms'];
            if ((($element == 'Color Data') or ($element == 'Primary Color') or ($element == 'Facet Color')) and (preg_match('/^#[a-f0-9]{6}$/i', $query))) {
                $color_name = color_name($query);
                $advancedValue = '<div id="swatch" data-toggle="tooltip" title="Color name: '. $color_name . '"><div style="background-color:' . html_escape($query) . ';"></div></div>';
                $advancedArray[$i] = $advancedValue;
            } else {
                if (isset($row['terms'])) {
                    $advancedValue = '"' . html_escape($row['terms']) . '"';
                }
                $advancedArray[$i] = '<span class="advanced">' . $advancedValue . '</span> ';
            }
        }
    }

    $html = '';
    if (!empty($displayArray) || !empty($advancedArray)) {
        foreach ($displayArray as $name => $query) {
            $class = html_escape(strtolower(str_replace(' ', '-', $name)));
            $html .= '<span class="' . $class . '">' . html_escape($query) . '</span>';
        }
        if (!empty($advancedArray)) {
            foreach ($advancedArray as $j => $advanced) {
                $html .= $advanced;
            }
        }
    }
    return $html;
}

function related_items($current_item)
{
    if (($collection = get_collection_for_item($current_item)) && ($subject_1 = metadata($current_item, array('Dublin Core', 'Subject'), array('index' => 0, 'no_escape' => true)))) {
        $related_items_1 = get_records('Item', array('collection' => metadata($collection, 'id'), 'tags' => $subject_1, 'sort_field' => 'random'), 5);
    } else {
        $related_items_1 = array();
    }
    if (($collection = get_collection_for_item($current_item)) && ($subject_2 = metadata($current_item, array('Dublin Core', 'Subject'), array('index' => 1, 'no_escape' => true)))) {
        $related_items_2 = get_records('Item', array('collection' => metadata($collection, 'id'), 'tags' => $subject_2, 'sort_field' => 'random'), 5);
    } else {
        $related_items_2 = array();
    }
    if ($subject_1 = metadata($current_item, array('Dublin Core', 'Subject'), array('index' => 0, 'no_escape' => true))) {
        $related_items_3 = get_records('Item', array('tags' => $subject_1, 'sort_field' => 'random'), 5);
    } else {
        $related_items_3 = array();
    }
    if ($collection = get_collection_for_item($current_item)) {
        $related_items_4 = get_records('Item', array('collection' => metadata($collection, 'id'), 'sort_field' => 'random'), 5);
    } else {
        $related_items_4 = array();
    }
    if ($medium = metadata($current_item, array('Dublin Core', 'Medium'), array('index' => 0, 'no_escape' => true))) {
        $related_items_5 = get_records('Item', array('tags' => $medium, 'sort_field' => 'random'), 5);
    } else {
        $related_items_5 = array();
    }
    if (($related_items_1) || ($related_items_2) || ($related_items_3) || ($related_items_4) || ($related_items_5)) {
        $related_items = array_merge(@$related_items_1, @$related_items_2, @$related_items_3, @$related_items_4, @$related_items_5);
        $unique_related_items = array_map("unserialize", array_unique(array_map("serialize", $related_items)));
        $sliced_related_items = array_slice($unique_related_items, 0, 7);
        if ($sliced_related_items) {
            $html = '<div class="col-md-4 related-items"><div class="panel panel-default"><div class="panel-heading"><h4>Related Items</h4></div><div class="list-group">';
            foreach ($sliced_related_items as $related_item) {
                if ((metadata($related_item, 'id')) != (metadata($current_item, 'id'))) {
                    $html .= link_to_item('<div class="row"><div class="col-xs-4">' . mdid_square_thumbnail_tag($related_item, 'img-responsive') . '</div><div class="col-xs-8"><h3 class="list-group-item-heading">' . metadata($related_item, array('Dublin Core', 'Title')) . '</h3></div></div>', array('class'=>'list-group-item'), 'show', $related_item);
                    release_object($related_item);
                }
            }
            $html .= '</div></div></div>';
            return $html;
        }
    }
}

function palette($current_item)
{
    if (metadata('item', array('Item Type Metadata', 'Color Data'))) {
        $color_data = metadata($current_item, array('Item Type Metadata', 'Color Data'));
        $color_data = json_decode(html_entity_decode($color_data), true);
        $palette = $color_data["palette"];
        $html = '<ul class="list-inline">';
        foreach ($palette as $section) {
            $color = $section["color"];
            $closest = $section['closest'];
            $name = $section['name'];
            $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Item Type Metadata', 'Color Data');
            $advanced = array();
            $advanced[] = array('element_id' => $element->id, 'terms' => htmlspecialchars_decode($closest, ENT_QUOTES), 'type' => 'contains');
            $paramArray = array('search' => '', 'advanced' => $advanced);
            $params = http_build_query($paramArray);
            $url = url('/items/browse?') . $params;
            $html .= '<li id="swatch" data-toggle="tooltip" title="Closest color: '. $name . '"><a href="' . $url .'">';
            $html .= '<span class="sr-only">' . $name . '</span><div style="background-color:' . $color . ';">';
            $html .= '</div></a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
}

function css4_palette()
{
    $palette = '{"#7cfc00": {"name": "lawngreen"}, "#808080": {"name": "grey"}, "#00008b": {"name": "darkblue"}, "#98fb98": {"name": "palegreen"}, "#fffff0": {"name": "ivory"}, "#9400d3": {"name": "darkviolet"}, "#b8860b": {"name": "darkgoldenrod"}, "#fffaf0": {"name": "floralwhite"}, "#ffffff": {"name": "white"}, "#fff5ee": {"name": "seashell"}, "#ff8c00": {"name": "darkorange"}, "#ffefd5": {"name": "papayawhip"}, "#5f9ea0": {"name": "cadetblue"}, "#2f4f4f": {"name": "darkslategrey"}, "#afeeee": {"name": "paleturquoise"}, "#add8e6": {"name": "lightblue"}, "#fffafa": {"name": "snow"}, "#1e90ff": {"name": "dodgerblue"}, "#000000": {"name": "black"}, "#8fbc8f": {"name": "darkseagreen"}, "#7fffd4": {"name": "aquamarine"}, "#ffe4e1": {"name": "mistyrose"}, "#800000": {"name": "maroon"}, "#6495ed": {"name": "cornflowerblue"}, "#4169e1": {"name": "royalblue"}, "#40e0d0": {"name": "turquoise"}, "#8b008b": {"name": "darkmagenta"}, "#00ced1": {"name": "darkturquoise"}, "#00fa9a": {"name": "mediumspringgreen"}, "#ee82ee": {"name": "violet"}, "#8b0000": {"name": "darkred"}, "#adff2f": {"name": "greenyellow"}, "#b0e0e6": {"name": "powderblue"}, "#7b68ee": {"name": "mediumslateblue"}, "#ff00ff": {"name": "magenta"}, "#4682b4": {"name": "steelblue"}, "#ff6347": {"name": "tomato"}, "#00ff7f": {"name": "springgreen"}, "#ff7f50": {"name": "coral"}, "#ffd700": {"name": "gold"}, "#f5fffa": {"name": "mintcream"}, "#008080": {"name": "teal"}, "#f0fff0": {"name": "honeydew"}, "#d2b48c": {"name": "tan"}, "#cd5c5c": {"name": "indianred"}, "#ffa07a": {"name": "lightsalmon"}, "#bdb76b": {"name": "darkkhaki"}, "#778899": {"name": "lightslategrey"}, "#008000": {"name": "green"}, "#faebd7": {"name": "antiquewhite"}, "#0000ff": {"name": "blue"}, "#c0c0c0": {"name": "silver"}, "#228b22": {"name": "forestgreen"}, "#7fff00": {"name": "chartreuse"}, "#663399": {"name": "rebeccapurple"}, "#d3d3d3": {"name": "lightgrey"}, "#fafad2": {"name": "lightgoldenrodyellow"}, "#fff8dc": {"name": "cornsilk"}, "#f4a460": {"name": "sandybrown"}, "#6b8e23": {"name": "olivedrab"}, "#9932cc": {"name": "darkorchid"}, "#fdf5e6": {"name": "oldlace"}, "#3cb371": {"name": "mediumseagreen"}, "#d8bfd8": {"name": "thistle"}, "#808000": {"name": "olive"}, "#a9a9a9": {"name": "darkgrey"}, "#f5f5dc": {"name": "beige"}, "#f5deb3": {"name": "wheat"}, "#ffa500": {"name": "orange"}, "#00ff00": {"name": "lime"}, "#90ee90": {"name": "lightgreen"}, "#a0522d": {"name": "sienna"}, "#8b4513": {"name": "saddlebrown"}, "#00ffff": {"name": "cyan"}, "#9acd32": {"name": "yellowgreen"}, "#66cdaa": {"name": "mediumaquamarine"}, "#b22222": {"name": "firebrick"}, "#dda0dd": {"name": "plum"}, "#ffebcd": {"name": "blanchedalmond"}, "#008b8b": {"name": "darkcyan"}, "#da70d6": {"name": "orchid"}, "#fffacd": {"name": "lemonchiffon"}, "#f5f5f5": {"name": "whitesmoke"}, "#ffc0cb": {"name": "pink"}, "#f0e68c": {"name": "khaki"}, "#ffffe0": {"name": "lightyellow"}, "#f0ffff": {"name": "azure"}, "#f0f8ff": {"name": "aliceblue"}, "#daa520": {"name": "goldenrod"}, "#c71585": {"name": "mediumvioletred"}, "#b0c4de": {"name": "lightsteelblue"}, "#fa8072": {"name": "salmon"}, "#708090": {"name": "slategrey"}, "#ff1493": {"name": "deeppink"}, "#6a5acd": {"name": "slateblue"}, "#2e8b57": {"name": "seagreen"}, "#e9967a": {"name": "darksalmon"}, "#ba55d3": {"name": "mediumorchid"}, "#ff4500": {"name": "orangered"}, "#a52a2a": {"name": "brown"}, "#696969": {"name": "dimgrey"}, "#191970": {"name": "midnightblue"}, "#dc143c": {"name": "crimson"}, "#ffff00": {"name": "yellow"}, "#556b2f": {"name": "darkolivegreen"}, "#eee8aa": {"name": "palegoldenrod"}, "#cd853f": {"name": "peru"}, "#faf0e6": {"name": "linen"}, "#ffdab9": {"name": "peachpuff"}, "#d2691e": {"name": "chocolate"}, "#f8f8ff": {"name": "ghostwhite"}, "#ffe4b5": {"name": "moccasin"}, "#ff69b4": {"name": "hotpink"}, "#e0ffff": {"name": "lightcyan"}, "#87cefa": {"name": "lightskyblue"}, "#ffdead": {"name": "navajowhite"}, "#483d8b": {"name": "darkslateblue"}, "#32cd32": {"name": "limegreen"}, "#e6e6fa": {"name": "lavender"}, "#800080": {"name": "purple"}, "#9370db": {"name": "mediumpurple"}, "#006400": {"name": "darkgreen"}, "#0000cd": {"name": "mediumblue"}, "#00bfff": {"name": "deepskyblue"}, "#ffe4c4": {"name": "bisque"}, "#48d1cc": {"name": "mediumturquoise"}, "#4b0082": {"name": "indigo"}, "#f08080": {"name": "lightcoral"}, "#ff0000": {"name": "red"}, "#bc8f8f": {"name": "rosybrown"}, "#db7093": {"name": "palevioletred"}, "#ffb6c1": {"name": "lightpink"}, "#20b2aa": {"name": "lightseagreen"}, "#87ceeb": {"name": "skyblue"}, "#fff0f5": {"name": "lavenderblush"}, "#8a2be2": {"name": "blueviolet"}, "#000080": {"name": "navy"}, "#dcdcdc": {"name": "gainsboro"}, "#deb887": {"name": "burlywood"}}';
    return $palette;
}

function basic_palette()
{
    $palette = '{
            "#ff0000": {"name": "red"},
            "#ffa500": {"name": "orange"},
            "#ffff00": {"name": "yellow"},
            "#7fff00": {"name": "chartreuse"},
            "#008000": {"name": "green"},
            "#1e90ff": {"name": "dodger blue"},
            "#0000ff": {"name": "blue"},
            "#8a2be2": {"name": "blue violet"},
            "#ff1493": {"name": "deep pink"}
          }';
    // Have removed "#00ff7f": {"name": "spring green"}, "#00ffff": {"name": "cyan"}, "#ff00ff": {"name": "magenta"} until there are results with this color.
    return $palette;
}
function color_name($color)
{
    $palette = css4_palette();
    $source = json_decode(html_entity_decode($palette), true);
    $value = $source[$color];
    $name = $value["name"];
    return $name;
}
function css4_color_board($palette = null)
{
    if (empty($palette)) {
        $palette = css4_palette();
    }
    $palette = json_decode(html_entity_decode($palette), true);
    $html = '<ul>';
    foreach ($palette as $hexcode => $color) {
        $color_name = $color['name'];
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Item Type Metadata', 'Facet Color');
        $advanced = array();
        $advanced[] = array('element_id' => $element->id, 'terms' => htmlspecialchars_decode($hexcode, ENT_QUOTES), 'type' => 'contains');
        $paramArray = array('search' => '', 'advanced' => $advanced);
        $params = http_build_query($paramArray);
        $url = url('/items/browse?') . $params;
        $html .= '<li id="swatch" data-toggle="tooltip" title="'. $color_name . '"><a href="' . $url .'">';
        $html .= '<span class="sr-only">' . $color_name . '</span><div style="background-color:' . $hexcode . ';">';
        $html .= '</div></a></li>';
    }
    $html .= '</ul>';
    return $html;
}

function basic_color_board($palette = null)
{
    if (empty($palette)) {
        $palette = basic_palette();
    }
    $palette = json_decode(html_entity_decode($palette), true);
    $html = '<ul>';
    foreach ($palette as $hexcode => $color) {
        $color_name = $color['name'];
        $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Item Type Metadata', 'Primary Color');
        $advanced = array();
        $advanced[] = array('element_id' => $element->id, 'terms' => htmlspecialchars_decode($hexcode, ENT_QUOTES), 'type' => 'contains');
        $paramArray = array('search' => '', 'advanced' => $advanced);
        $params = http_build_query($paramArray);
        $url = url('/items/browse?') . $params;
        $html .= '<li id="bar" data-toggle="tooltip" title="'. $color_name . '"><a href="' . $url .'">';
        $html .= '<span class="sr-only">' . $color_name . '</span><div style="background-color:' . $hexcode . ';">';
        $html .= '</div></a></li>';
    }
    $html .= '</ul>';
    return $html;
}
function mdid_image_tag($item, $class)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $html = '<img src="https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/" class="' . $class . '" alt="' . metadata($item, array('Dublin Core', 'Title')) . '">';
        return $html;
    }
}
function mdid_medium_image_tag($item, $class)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $html = '<img src="https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/800x800" class="' . $class . '" alt="' . metadata($item, array('Dublin Core', 'Title')) . '">';
        return $html;
    }
}
function mdid_medium_image_url($item)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $url = 'https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/800x800/';
        return $url;
    }
}
function mdid_rss_image_tag($item)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $url = record_url($item, null, true);
        $html = '<a href="' . $url . '"><img src="https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/600x600/" class="rss" alt="' . metadata($item, array('Dublin Core', 'Title')) . '"></a>';
        return $html;
    }
}
function mdid_thumbnail_tag($item, $class)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $html = '<div class="thumbnail-container"><img src="https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/400x400/" class="' . $class . '" alt="' . metadata($item, array('Dublin Core', 'Title')) . '"></div>';
        return $html;
    } else {
        $html = '<div class="thumbnail-container"><img src="' . img("fallback-image.png") . '" class="' . $class . '" alt="' . metadata($item, array('Dublin Core', 'Title')) . '"></div>';
        return $html;
    }
}
function mdid_thumbnail_url($item)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $url = 'https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/400x400/';
        return $url;
    }
}
function mdid_square_thumbnail_tag($item, $class)
{
    if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
        $html = '<div class="thumbnail-container"><img src="https://fitdil.fitnyc.edu/media/thumb/' . $record_id . '/' . $record_name . '/?square" class="' . $class . '" alt="' . metadata($item, array('Dublin Core', 'Title')) . '"></div>';
        return $html;
    }
}
function public_domain_download($item)
{
    $rights = metadata($item, array('Dublin Core', 'Rights'), array('all' => true));
    if (in_array("Public Domain", $rights)) {
        if (($record_name = metadata($item, array('Item Type Metadata', 'Record Name'))) && ($record_id = metadata($item, array('Item Type Metadata', 'Record ID')))) {
            $url = 'https://fitdil.fitnyc.edu/media/get/' . $record_id . '/' . $record_name . '/?forcedl';
            $html = '<div class="hidden-xs" id="pd-download"><a href="' . $url . '" data-toggle="tooltip" data-placement="bottom" title="Please see Rights statement below for usage guidelines."><i class="fa fa-download" aria-hidden="true"></i> Download</a></div>';
            return $html;
        }
    }
}
function get_exhibit_item($exhibit)
{
    $page = $exhibit->getFirstTopPage();
    if ($page) {
        $attachments = $page->getAllAttachments();
        if ($attachments) {
            $item = $attachments[0]->getItem();
            return $item;
        }
    }
}
function tag_search($text, $elementName)
{
    // check if creator field has role which we don't want to include in the tag search
    if ((($elementName == "Creator") or ($elementName == "Contributor")) and strpos($text, '[') !== false) {
        $explode = explode("[", $text);
        $tag = trim($explode[0]);
        $role = $explode[1];
        $role = trim($role, "]");
        $text = $tag . " (" . $role . ")";
    } else {
        $tag = $text;
    }

    $paramArray = array('tags' => htmlspecialchars_decode($tag, ENT_QUOTES));
    $params = http_build_query($paramArray);
    $url = url('/items/browse?') . $params;
    $html = '<a href="';
    $html .= $url;
    $html .= '">';
    $html .= $text;
    $html .= '</a>';
    return $html;
}
// Given an metadata element and term, returns a search of all items that match that term.
function heading_links($setName, $elementName, $text)
{
    $element = get_db()->getTable('Element')->findByElementSetNameAndElementName($setName, $elementName);
    $id = $element->id;
    $advanced[] = array('element_id' => $id, 'terms' => htmlspecialchars_decode($text, ENT_QUOTES), 'type' => 'is exactly');
    $paramArray = array('search' => '', 'advanced' => $advanced);
    $params = http_build_query($paramArray);
    $url = url('/items/browse?') . $params;
    $html = '<a href="';
    $html .= $url;
    $html .= '">';
    $html .= $text;
    $html .= '</a>';
    return $html;
}
// Creates social media tags for an image, following Twitter and Facebook standards.
function social_tags($bodyclass)
{
    $html = '';
    if ($bodyclass == "items show") {
        $item = get_current_record('item');
        $title = metadata($item, array('Dublin Core', 'Title'));
        $o_width = metadata($item, array('Item Type Metadata', 'Width'));
        $o_height = metadata($item, array('Item Type Metadata', 'Height'));
        if ($o_width >= $o_height) {
            $height = floor((400 * $o_height) / $o_width);
            $width = 400;
        } else {
            $width = floor((400 * $o_width) / $o_height);
            $height = 400;
        }
        $url = record_url($item, null, true);
        $image = mdid_thumbnail_url($item);
        $description = metadata($item, array('Dublin Core', 'Description'));
        $html .= '<meta name="description" content="' . $description . '" />';
        $html .= '<!-- Open Graph data -->';
        $html .= '<meta property="og:title" content="' . $title . '" />';
        $html .= '<meta property="og:type" content="article" />';
        $html .= '<meta property="og:url" content="' . $url . '" />';
        $html .= '<meta property="og:image" content="' . $image . '" />';
        $html .= '<meta property="og:image:width" content="' . $width . '" />';
        $html .= '<meta property="og:image:height" content="' . $height . '" />';
        $html .= '<meta property="og:description" content="' . $description . '" />';

        $html .= '<!-- Twitter Card data -->';
        $html .= '<meta name="twitter:card" content="summary_large_image">';
        $html .= '<meta name="twitter:title" content="' . $title . '" />';
        $html .= '<meta name="twitter:site" content="@FITLibrary">';
        $html .= '<meta name="twitter:description" content="' . $description . '" />';
        $html .= '<meta name="twitter:image" content="' . $image . '" />';
    } else {
        if ($site_description = option('description')) {
            $html .= '<meta name="description" content="' . $site_description . '" />';
        }
    }
    return $html;
}

// Creates custom RSS2 feed.

class Output_ItemRss2_Custom
{
    public function render_custom(array $records)
    {
        $entries = array();
        foreach ($records as $record) {
            $entries[] = $this->itemToRSS_custom($record);
            release_object($record);
        }
        $headers = $this->buildRSSHeaders_custom();
        $headers['entries'] = $entries;
        $feed = Zend_Feed::importArray($headers, 'rss');
        return $feed->saveXML();
    }
    public function buildRSSHeaders_custom()
    {
        $headers = array();
        // How do we determine what title to give the RSS feed?
        $headers['title'] = option('site_title');
        $headers['link'] = xml_escape(get_view()->serverUrl(isset($_SERVER['REQUEST_URI'])));
        $headers['lastUpdate'] = time();
        $headers['charset'] = "UTF-8";
        // Feed could have a description, where would it be stored ?
        // $headers['description'] = ""
        $headers['author'] = option('site_title');
        $headers['email'] = option('administrator_email');
        $headers['copyright'] = option('copyright');
        //How do we determine how long a feed can be cached?
        //$headers['ttl'] =
        return $headers;
    }
    public function buildDescription_custom($item)
    {
        $description = mdid_rss_image_tag($item);
        $description .= '<span id="rss-citation">This image was provided by the FIT Library\'s Special Collections and College Archives. View more at <a href="https://sparcdigital.fitnyc.edu">sparcdigital.fitnyc.edu</a>.</span>';
        return $description;
    }
    public function itemToRSS_custom($item)
    {
        $entry = array();
        set_current_record('item', $item, true);
        // Title is a CDATA section, so no need for extra escaping.
        $date = metadata($item, array('Dublin Core', 'Date'), array('no_escape' => true));
        $creator = metadata($item, array('Dublin Core', 'Creator'), array('no_escape' => true));
        $entry['title'] = metadata($item, array('Dublin Core', 'Title'), array('no_escape' => true)) . ($date ? ' (' . $date . ')' : '') . ($creator ? ' / ' . $creator : '');
        $entry['description'] = $this->buildDescription_custom($item);
        $entry['link'] = xml_escape(record_url($item, null, true));
        $entry['lastUpdate'] = strtotime($item->added);
        return $entry;
    }
}
