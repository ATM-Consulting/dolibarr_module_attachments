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
 * @param Form      $form       Form object
 * @param Attachments  $object     Attachments object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmAttachments($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'valid' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmValidateAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmValidateAttachmentsTitle'), $body, 'confirm_validate', '', 0, 1);
    }
    elseif ($action === 'accept' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmAcceptAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmAcceptAttachmentsTitle'), $body, 'confirm_accept', '', 0, 1);
    }
    elseif ($action === 'refuse' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmRefuseAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmRefuseAttachmentsTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'reopen' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmReopenAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmReopenAttachmentsTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmDeleteAttachmentsBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeleteAttachmentsTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmCloneAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneAttachmentsTitle'), $body, 'confirm_clone', '', 0, 1);
    }
    elseif ($action === 'cancel' && !empty($user->rights->attachments->write))
    {
        $body = $langs->trans('ConfirmCancelAttachmentsBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCancelAttachmentsTitle'), $body, 'confirm_cancel', '', 0, 1);
    }

    return $formconfirm;
}