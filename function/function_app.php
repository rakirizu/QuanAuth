<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2018-01-14
 * Time: 13:44
 */
function app_idgetinfo($id)
{
    global $db;
    return $db->select_first_row('sq_apps', '*', array('ID' => $id), 'AND');
}

function app_idgetname($id)
{
    global $db;
    if (!$result = $db->select_first_row('sq_apps', 'appname', array('ID' => $id), 'AND')) {
        return false;
    }
    return $result['appname'];
}

function level_idgetname($id)
{
    global $db;
    if (!$result = $db->select_first_row('sq_level', 'lname', array('ID' => $id), 'AND')) {
        return false;
    } else {
        return $result['lname'];
    }

}