<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('needseed'));
$possible_actions = [
    'leechers',
    'seeders',
];
$needed = (isset($_GET['needed']) ? htmlsafechars($_GET['needed']) : 'seeders');
if (!in_array($needed, $possible_actions)) {
    stderr('Error', 'A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.');
}
//$needed = isset($_GET["needed"]) ? htmlsafechars($_GET["needed"]) : '';
$categorie = genrelist();
foreach ($categorie as $key => $value) {
    $change[$value['id']] = [
        'id'    => $value['id'],
        'name'  => $value['name'],
        'image' => $value['image'],
    ];
}
if ($needed == 'leechers') {
    $HTMLOUT .= begin_main_frame();
    $HTMLOUT .= begin_frame("{$lang['needseed_sin']}&#160;&#160;-&#160;&#160;[<a href='?needed=seeders' class='altlink'>{$lang['needseed_tns']}</a>]");
    $Dur = TIME_NOW - 86400 * 7; //== 7 days
    if (XBT_TRACKER === true) {
        $res = sql_query('SELECT x.fid, x.uid, u.username, u.uploaded, u.downloaded, t.name, t.seeders, t.leechers, t.category ' . 'FROM xbt_files_users AS x ' . 'LEFT JOIN users AS u ON u.id=x.uid ' . "LEFT JOIN torrents AS t ON t.id=x.fid WHERE x.left = '0' AND active='1'" . "AND u.downloaded > '1024' AND u.added < $Dur ORDER BY u.uploaded / u.downloaded ASC LIMIT 20") or sqlerr(__FILE__, __LINE__);
    } else {
        $res = sql_query('SELECT p.id, p.userid, p.torrent, u.username, u.uploaded, u.downloaded, t.name, t.seeders, t.leechers, t.category ' . 'FROM peers AS p ' . 'LEFT JOIN users AS u ON u.id=p.userid ' . "LEFT JOIN torrents AS t ON t.id=p.torrent WHERE p.seeder = 'yes' " . "AND u.downloaded > '1024' AND u.added < $Dur ORDER BY u.uploaded / u.downloaded ASC LIMIT 20") or sqlerr(__FILE__, __LINE__);
    }
    if (mysqli_num_rows($res) > 0) {
        $HTMLOUT .= "<table class='table table-bordered table-striped'>
    <tr><td class='colhead'>{$lang['needseed_user']}</td><td class='colhead'>{$lang['needseed_tor']}</td><td class='colhead'>{$lang['needseed_cat']}</td><td class='colhead'>{$lang['needseed_peer']}</td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $What_ID = (XBT_TRACKER === true ? $arr['fid'] : $arr['torrent']);
            $What_User_ID = (XBT_TRACKER === true ? $arr['uid'] : $arr['userid']);
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            $cat = "<img src=\"./images/caticons/" . get_categorie_icons() . "/{$needseed['cat_pic']}\" alt=\"{$needseed['cat_name']}\" title=\"{$needseed['cat_name']}\" />";
            $torrname = htmlsafechars(CutName($arr['name'], 80));
            $peers = (int)$arr['seeders'] . ' seeder' . ((int)$arr['seeders'] > 1 ? 's' : '') . ', ' . (int)$arr['leechers'] . ' leecher' . ((int)$arr['leechers'] > 1 ? 's' : '');
            $HTMLOUT .= "<tr><td><a href='{$site_config['baseurl']}/userdetails.php?id=" . (int)$What_User_ID . "'>" . htmlsafechars($arr['username']) . '</a>&#160;(' . member_ratio($arr['uploaded'], $arr['downloaded']) . ")</td><td><a href='{$site_config['baseurl']}/details.php?id=" . (int)$What_ID . "' title='{$torrname}'>{$torrname}</a></td><td>{$cat}</td><td>{$peers}</td></tr>\n";
        }
        $HTMLOUT .= "</table>\n";
    } else {
        $HTMLOUT .= "{$lang['needseed_noleech']}\n";
    }
    $HTMLOUT .= end_frame();
    $HTMLOUT .= end_main_frame();
    echo stdhead("{$lang['needseed_lin']}") . $HTMLOUT . stdfoot();
} else {
    $HTMLOUT .= begin_main_frame();
    $HTMLOUT .= begin_frame("[<a href='?needed=leechers' class='altlink'>{$lang['needseed_sin']}</a>]&#160;&#160;-&#160;&#160;{$lang['needseed_tns']}");
    $res = sql_query('SELECT id, name, seeders, leechers, added, category FROM torrents WHERE leechers >= 0 AND seeders = 0 ORDER BY leechers DESC LIMIT 20') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $HTMLOUT .= "<table class='table table-bordered table-striped'>
        <tr><td class='colhead'>{$lang['needseed_cat']}</td><td class='colhead'>{$lang['needseed_tor']}</td><td class='colhead'>{$lang['needseed_seed']}</td><td class='colhead'>{$lang['needseed_leech']}</td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            $cat = "<img src=\"./images/caticons/" . get_categorie_icons() . "/{$needseed['cat_pic']}\" alt=\"{$needseed['cat_name']}\" title=\"{$needseed['cat_name']}\" />";
            $torrname = htmlsafechars(CutName($arr['name'], 80));
            $HTMLOUT .= "<tr><td>{$cat}</td><td><a href='{$site_config['baseurl']}/details.php?id=" . (int)$arr['id'] . "&amp;hit=1' title='{$torrname}'>{$torrname}</a></td><td><span>" . (int)$arr['seeders'] . "</span></td><td>" . (int)$arr['leechers'] . "</td></tr>\n";
        }
        $HTMLOUT .= "</table>\n";
    } else {
        $HTMLOUT .= "{$lang['needseed_noseed']}\n";
    }
    $HTMLOUT .= end_frame();
    $HTMLOUT .= end_main_frame();
    echo stdhead("{$lang['needseed_sin']}") . $HTMLOUT . stdfoot();
}
