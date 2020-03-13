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
 * @param ActionsAttachments    $actionattachments      Object
 * @param array                 $TFilePathByTitleKey    Array of path
 * @param string                $trackid                Key to avoid conflicts
 * @return string
 */
function getFormConfirmAttachments($actionattachments, $TFilePathByTitleKey, $trackid = null)
{
    global $db, $langs;

    if (empty($TFilePathByTitleKey)) return '';

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

    $html = '
        <div>
            <i class="fa fa-search"></i>
            <input type="text" id="attachments-item-filter" class="search-filter" data-target="" value="" placeholder="Rechercher" <span="">
            <span id="attachments-filter-count-wrap" >'.$langs->trans('Result').': <span id="attachments-filter-count" ></span></span>    
        </div>';

    $html.= '<dl id="attachments-accordion">';
    foreach ($TFilePathByTitleKey as $titleKey => $TFilePathByRef)
    {
        $html.= '
            <dt class="title">
                <b>'.$langs->trans($titleKey).'</b>
                <span title="'.$langs->trans('AttachmentsSelectedOnTotalAvailable').'" class="attachments-element-selected badge badge-secondary" data-element-selected=""></span>
                <span title="'.$langs->trans('AttachmentsFiltered').'" class="attachments-element-count badge" data-element-count=""></span>
            </dt>';

        $html.= '<dd class="panel">';
        foreach ($TFilePathByRef as $ref => $file_info)
        {
            $class = $object->ref === $ref ? 'currentobject' : '';
            $html.= '
                <p class="subtitle">
                    <b class="'.$class.'">'.str_repeat('&nbsp;', 4).$ref.'</b>
                </p>';
            foreach ($file_info as $info)
            {
                $id = 'TAttachments_'.$info['fullname_md5'];
                $checked = isset($TSelectedFileName[$info['name']]) ? 'checked' : '';
                $html.= '
                    <div class="searchable search-match oddeven">
                        '.str_repeat('&nbsp;', 8).'<input id="'.$id.'" name="'.$id.'" type="checkbox" '.$checked.' value="'.$info['fullname_md5'].'" class="pull-right" />
                        <label for="'.$id.'">'.$info['name'].'</label>
                    </div>';
                $formquestion[] = array('name' => 'TAttachments_'.$info['fullname_md5']);
            }
        }
        $html.= '</dd>';
    }
    $html.= '</dl>';

    $formquestion['text'] = $html.'
        <style type="text/css">
            #attachments-accordion .attachments-element-count, #attachments-accordion .attachments-element-selected {
                margin-left: 8px;
            }
            #attachments-accordion .attachments-element-count::after {
                content: attr(data-element-count);
            }
            #attachments-accordion .attachments-element-selected::after {
                content: attr(data-element-selected);
            }
            
            #attachments-accordion .subtitle {
                margin: 4px 0 8px 0;
            }
            
            #attachments-accordion .searchable {
                margin: 0 0 8px 0;
            }
            
            #attachments-accordion .searchable label {
                padding-right: 8px;
            }
            
            #attachments-accordion .currentobject::before {
                content: "*"
            }
            #attachments-accordion .currentobject {
                color: blue;
            }
        </style>
        
        <script type="text/javascript">
            $(function() {
                $("#attachments-accordion").accordion({
                    header: "dt"
                    , heightStyle: "content"
                    , collapsible: true
                });
                
                $( document ).on("keyup", "#attachments-item-filter", function () {
                    let filter = $(this).val(), count = 0;
                    $("#attachments-accordion .searchable").each(function () {
                        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                            $(this).removeClass("search-match").hide();
                        } else {
                            $(this).addClass("search-match").show();
                            count++;
                        }
                    });
                    
                    $("#attachments-filter-count").text(count);
                    
                    updateBadgeCount();
                });
                
                updateBadgeCount = function () {
                    $("#attachments-accordion .attachments-element-count").each(function(i, item) {
                        let dtId = $(item).parent().attr("id");
                        let nb = $("dd[aria-labelledby="+dtId+"]").find("div.searchable.search-match").length;
                        item.dataset.elementCount = nb;
                        
                        if (nb > 0) $(this).addClass("badge-warning").removeClass("badge-secondary");
                        else $(this).addClass("badge-secondary").removeClass("badge-warning");
                    });
                };
                
                updateBadgeCount();
                
                updateBadgeSelected = function () {
                    $("#attachments-accordion .attachments-element-selected").each(function(i, item) {
                        let dtId = $(item).parent().attr("id");
                        let nb = $("dd[aria-labelledby="+dtId+"]").find("input[type=checkbox]").length;
                        let nb_selected = $("dd[aria-labelledby="+dtId+"]").find("input[type=checkbox]:checked").length;
                        item.dataset.elementSelected = nb_selected + " / " + nb;
                    });
                };
                
                updateBadgeSelected();
                
                $("#attachments-accordion input[type=checkbox]").change(updateBadgeSelected);
            });
        </script>
    ';

    $formconfirm = $form->formconfirm(
        $_SERVER['PHP_SELF'] . '?id=' . $object->id.'&'.http_build_query($moreparam)
        , $langs->trans('ConfirmCancelAttachmentsTitle')
        , '<i>'.$langs->trans('ConfirmCancelAttachmentsBody').'</i>'
        , 'confirm_attachments_send'
        , $formquestion
        , 0
        , 1
        , 'auto'
        , '800'
    );

    $formconfirm.= '
        <script type="text/javascript">
            function customDialogByPhTouch(uri) {
                var p = uri.split("?");
                var action = p[0];
                var params = p[1].split("&");
                var paramsGet = "";
                var form = $(document.createElement("form")).attr({
                    method: "POST"
                    , id: "attachments-dialog"
                    , enctype: "multipart/form-data"
                });
                $("body").append(form);

                for (var i in params) {
                    var tmp= params[i].split("=");
                    var key = tmp[0], value = tmp[1];

                    if (key === "message") {
                        let textarea = $(document.createElement("textarea"));
                        textarea.css({display: "none"});
                        textarea.attr("name", "message");

                        if (typeof CKEDITOR === "object" && typeof CKEDITOR.instances.message === "object") {
                            textarea.val(CKEDITOR.instances.message.getData());
                        } else {
                            textarea.val(value);
                        }

                        textarea.appendTo(form);
                    } else if (key === "subject" || key === "deliveryreceipt") {
                        $(document.createElement("input")).attr("type", "hidden").attr("name", key).attr("value", $("#"+key).val()).appendTo(form);
                    } else {
                        if (paramsGet.length > 0) paramsGet+= "&";
                        paramsGet+= key + "=" + value;
                    }
                }
                form.attr("action", action + "?" + paramsGet);

                setTimeout(function() { form.submit(); }, 100);
                return false;
            }
        </script>
    ';

    $formconfirm = str_replace('location.href = urljump;', 'customDialogByPhTouch(urljump);', $formconfirm);

    return $formconfirm;
}