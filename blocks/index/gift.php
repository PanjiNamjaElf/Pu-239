<?php
$Christmasday = mktime(0, 0, 0, 12, 25, date('Y'));
$today = mktime(date('G'), date('i'), date('s'), date('m'), date('d'), date('Y'));
if (($CURUSER['opt1'] & user_options::GOTGIFT) && $today != $Christmasday) {
    $HTMLOUT .= "
    <a id='gift-hash'></a>
    <fieldset id='gift' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_christmas_gift']}</legend>
        <div class='bordered padleft10 padright10'>
            <div class='alt_bordered transparent text-center'>
                <a href='{$site_config['baseurl']}/gift.php?open=1'>
                    <img src='{$site_config['pic_base_url']}gift.png' class='tooltipper image_48' alt='{$lang['index_christmas_gift']}' title='{$lang['index_christmas_gift']}' />
                </a>
            </div>
        </div>
    </fieldset>";
}
