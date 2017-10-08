<?php
if (($latestuser = $mc1->get_value('latestuser')) === false) {
    $latestuser = mysqli_fetch_assoc(sql_query('SELECT id FROM users WHERE status = "confirmed" ORDER BY id DESC LIMIT 1'));
    $mc1->cache_value('latestuser', $latestuser, $site_config['expires']['latestuser']);
}
$HTMLOUT .= "
        <a id='latestuser-hash'></a>
        <fieldset id='latestuser' class='header'>
            <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_lmember']}</legend>
            <div class='bordered padleft10 padright10'>
                <div class='alt_bordered transparent text-center'>
                    <span>{$lang['index_wmember']} " . format_username($latestuser['id']) . "!</span>
                </div>
            </div>
        </fieldset>";
