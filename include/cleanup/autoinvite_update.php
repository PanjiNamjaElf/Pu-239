<?php
function autoinvite_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $ratiocheck = 1.0;
    $joined = (TIME_NOW - 86400 * 90);
    $res = sql_query("SELECT id, uploaded, invites, downloaded, modcomment FROM users WHERE invites='1' AND class = " . UC_USER . " AND uploaded / downloaded <= $ratiocheck AND enabled='yes' AND added < $joined") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Auto Invites';
        $msg = "Congratulations, your user group met a set out criteria therefore you have been awarded 2 invites  :)\n Please use them carefully. Cheers " . $site_config['site_name'] . " staff.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $modcomment = $arr['modcomment'];
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . ' - Awarded 2 bonus invites by System (UL=' . mksize($arr['uploaded']) . ', DL=' . mksize($arr['downloaded']) . ', R=' . $ratio . ") .\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', 2, ' . $modcom . ')'; //== 2 in the user_buffer is award amount :)
            $update['invites'] = ($arr['invites'] + 2); //== 2 in the user_buffer is award amount :)
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'invites' => $update['invites'],
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('user_stats_' . $arr['id']);
            $mc1->update_row(false, [
                'modcomment' => $modcomment,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'invites' => $update['invites'],
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->delete_value('inbox_new_' . $arr['id']);
            $mc1->delete_value('inbox_new_sb_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, invites, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE invites = invites+values(invites), modcomment=values(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: Awarded 2 bonus invites to ' . $count . ' member(s) ');
        }
        unset($users_buffer, $msgs_buffer, $update, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Auto Invites Cleanup: Completed using $queries queries");
    }
}
