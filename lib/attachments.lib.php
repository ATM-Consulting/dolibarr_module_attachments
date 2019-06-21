<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/attachments.lib.php
 *	\ingroup	attachments
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function attachmentsAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("attachments@attachments");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/attachments/admin/attachments_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/attachments/admin/attachments_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@attachments:/attachments/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@attachments:/attachments/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'attachments');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	Attachments	$object		Object company shown
 * @return 	array				Array of tabs
 */
function attachments_prepare_head(Attachments $object)
{
    global $langs, $conf;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/attachments/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("AttachmentsCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@attachments:/attachments/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@attachments:/attachments/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'attachments');
	
	return $head;
}

/**
 * @param ActionsAttachments  $actionattachments             Object
 * @param array         $TFilePathByTitleKey   Array of path
 * @return string
 */
function getFormConfirmAttachments($actionattachments, $TFilePathByTitleKey, $trackid=null)
{
    global $db, $langs, $user;

    $object = $actionattachments->current_object;

    $langs->load('attachments@attachments');
    $form = new Form($db);

    $formquestion = array();

    $moreparam = array();

    foreach ($_REQUEST as $k => $v)
    {
        if (in_array($k, array('action', 'token', 'id'))) continue;
        $moreparam[$k] = $v;
    }

    // Je ne peux pas me baser sur le chemin complet car une fois joint au mail, les chemins pointe vers le dossier "/temp" du user
    $TSelectedFileName = array();
    if (!empty($_SESSION['listofnames-'.$trackid])) $TSelectedFileName = array_flip(explode(';', $_SESSION['listofnames-'.$trackid]));

    foreach ($TFilePathByTitleKey as $titleKey => $TFilePathByRef)
    {
        $formquestion[] = array('type' => 'onecolumn', 'value' => '<b>'.$langs->trans($titleKey).'</b>');
        foreach ($TFilePathByRef as $ref => $file_info)
        {
            $class = $object->ref == $ref ? 'fieldrequired' : '';
            $formquestion[] = array('type' => 'onecolumn', 'value' => '<b class="'.$class.'">'.str_repeat('&nbsp;', 4).$ref.'</b>');
            foreach ($file_info as $info)
            {
                $value = isset($TSelectedFileName[$info['name']]) ? 1 : 0;
                $formquestion[] = array('type' => 'checkbox', 'label' => str_repeat('&nbsp;', 8).$info['name'], 'name' => 'TAttachments_'.$info['fullname_md5'], 'value' => $value, 'moreattr' => ' value="'.$info['fullname_md5'].'"', 'tdclass' => 'oddeven');
            }
        }
    }

    $formconfirm = $form->formconfirm(
        $_SERVER['PHP_SELF'] . '?id=' . $object->id.'&'.http_build_query($moreparam)
        , $langs->trans('ConfirmCancelAttachmentsTitle')
        , '<i>'.$langs->trans('ConfirmCancelAttachmentsBody').'</i>'
        , 'confirm_attachments_send'
        , $formquestion
        , 0
        , 1
        , 'auto'
        , 'auto'
    );


    return $formconfirm;
}