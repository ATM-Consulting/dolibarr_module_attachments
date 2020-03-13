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
 * \file    class/actions_attachments.class.php
 * \ingroup attachments
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsAttachments
 */
class ActionsAttachments
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

    public $TTileKeyRank = array(
        'AttachmentsTitleProductOrService' => 0
        ,'AttachmentsTitlePropal' => 5
        , 'AttachmentsTitleCommande' => 10
        , 'AttachmentsTitleFacture' => 15
        , 'AttachmentsTitleContrat' => 20
        , 'AttachmentsTitlePropalFournisseur' => 25
        , 'AttachmentsTitleCommandeFournisseur' => 30
        , 'AttachmentsTitleFactureFournisseur' => 35
        , 'AttachmentsTitleFicheInter' => 40
        , 'AttachmentsSociete' => 50
        , 'AttachmentsTitleTask' =>60
        , 'AttachmentsTitleEcm' => 500
    );

	public $TTileKeyByElement = array(
        'product' => 'AttachmentsTitleProductOrService'
        ,'propal' => 'AttachmentsTitlePropal'
        , 'commande' => 'AttachmentsTitleCommande'
        , 'facture' => 'AttachmentsTitleFacture'
        , 'contrat' => 'AttachmentsTitleContrat'
        , 'supplier_proposal' => 'AttachmentsTitlePropalFournisseur'
        , 'order_supplier' => 'AttachmentsTitleCommandeFournisseur'
        , 'invoice_supplier' => 'AttachmentsTitleFactureFournisseur'
        , 'fichinter' => 'AttachmentsTitleFicheInter'
        , 'societe' => 'AttachmentsSociete'
        , 'ecm' => 'AttachmentsTitleEcm'
        , 'project_task' => 'AttachmentsTitleTask'
    );

	public $TFilePathByTitleKey = array();

	public $formconfirm;

	public $current_object;

	public $action;

	public $modelmailselected;


    /**
     * ActionsAttachments constructor.
     * @param DoliDB    $db     Database connector
     */
    public function __construct($db)
    {
		$this->db = $db;
    }

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user;

	    if (in_array($action, array('presend', 'send', 'attachments_send', 'confirm_attachments_send')) && method_exists($object, 'fetchObjectLinked'))
        {
            require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

            $hookmanager->initHooks(array('attachmentsform'));

            $this->current_object = $object;
            if (!empty($conf->global->ATTACHMENTS_INCLUDE_OBJECT_LINKED)) {
            	$this->current_object->fetchObjectLinked();
				$this->current_object->linkedObjects['societe'][$this->current_object->fk_soc] = $this->current_object->thirdparty;
			}

            if (empty($this->current_object->linkedObjects[$this->current_object->element])) $this->current_object->linkedObjects[$this->current_object->element] = array();
            array_unshift($this->current_object->linkedObjects[$this->current_object->element], $this->current_object);

            if (!empty($conf->global->ATTACHMENTS_INCLUDE_PRODUCT_LINES) && !empty($this->current_object->lines))
            {
                foreach ($this->current_object->lines as $line)
                {
                    if (!empty($line->fk_product) && !isset($this->current_object->linkedObjects['product'][$line->fk_product]))
                    {
                        $product = new Product($this->db);
                        if ($product->fetch($line->fk_product) > 0)
                        {
                            $this->current_object->linkedObjects['product'][$line->fk_product] = $product;
                        }
                    }
                }
            }

			if ($this->current_object->element == "project")
			{
				$this->current_object->getLinesArray($user);
				if (!empty($this->current_object->lines))
				{
					$subdir = '/'.dol_sanitizeFileName($this->current_object->ref);
					foreach ($this->current_object->lines as $line)
					{
						$linkObjRef = dol_sanitizeFileName($line->ref);
						$filedir = $conf->projet->dir_output . $subdir . '/' . $linkObjRef;

						$file_list=dol_dir_list($filedir, 'files', 0, '', '(\.meta|_preview.*.*\.png)$', 'date', SORT_DESC);

						if (!empty($file_list))
						{
							$key = $this->TTileKeyByElement['project_task'];
							foreach ($file_list as $file_info)
							{
								$fullname_md5 = md5($file_info['fullname']);
								$this->TFilePathByTitleKey[$key][$linkObjRef][$fullname_md5] = array(
                                    'name' => $file_info['name']
                                    ,'path' => $file_info['path']
                                    ,'fullname' => $file_info['fullname']
                                    ,'fullname_md5' => $fullname_md5
								);
							}
						}
					}
				}
			}
            // Gestion des objets standards
            foreach ($this->current_object->linkedObjects as $element => $TLinkedObject)
            {

                if (empty($conf->global->ATTACHMENTS_INCLUDE_OBJECT_LINKED) && $element !== 'product' && $element !== $this->current_object->element)
                {
                    // Si la recherche dans les objets liés n'est pas actif et qu'on est pas sur un élément "product" ou de l'objet courant, alors on passe
                    continue;
                }

                $sub_element_to_use = '';
                $subdir = '';
                if ($element === 'fichinter') $element_to_use = 'ficheinter';
                elseif ($element === 'order_supplier') { $element_to_use = 'fournisseur'; $subdir = '/commande'; }
                elseif ($element === 'invoice_supplier') { $element_to_use = 'fournisseur'; $sub_element_to_use = 'facture'; /* $subdir is defined in the next loop */ }
                else $element_to_use = $element;

                /** @var CommonObject $linkedObject */
                foreach ($TLinkedObject as $linkedObject)
                {
                    // Documents
                    $linkObjRef = dol_sanitizeFileName($linkedObject->ref);
                    if ($element == 'societe') $linkObjRefKey = $linkedObject->nom;
                    else $linkObjRefKey = $linkObjRef;

                    if ($element === 'invoice_supplier') $subdir = '/'.get_exdir($linkedObject->id, 2, 0, 0, $linkedObject, 'invoice_supplier');

                    // TODO $element doit être faussé en fonction du type de l'objet
                    if (!empty($sub_element_to_use)) $filedir = $conf->{$element_to_use}->{$sub_element_to_use}->dir_output . $subdir . '/' . $linkObjRef;
                    else $filedir = $conf->{$element_to_use}->dir_output . $subdir . '/' . $linkObjRef;

                    $file_list=dol_dir_list($filedir, 'files', 0, '', '(\.meta|_preview.*.*\.png)$', 'date', SORT_DESC);

                    if (!empty($file_list))
                    {
                        $key = $this->TTileKeyByElement[$element];
                        foreach ($file_list as $file_info)
                        {
                            $fullname_md5 = md5($file_info['fullname']);
                            $this->TFilePathByTitleKey[$key][$linkObjRefKey][$fullname_md5] = array(
                                'name' => $file_info['name']
                                ,'path' => $file_info['path']
                                ,'fullname' => $file_info['fullname']
                                ,'fullname_md5' => $fullname_md5
                            );
                        }
                    }
                }
            }

            if (!empty($conf->ecm->enabled) && $conf->global->ATTACHMENTS_ECM_SCANDIR > 0)
            {
                require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
                $ecmdir = new EcmDirectory($this->db);
                if ($ecmdir->fetch($conf->global->ATTACHMENTS_ECM_SCANDIR) > 0)
                {
                    $fullpathselecteddir = $conf->ecm->dir_output.'/'.$ecmdir->getRelativePath();
                    $key = $this->TTileKeyByElement['ecm'];

                    $this->nyandog($key, $fullpathselecteddir, $ecmdir->label);
                }
            }

            // Surcharge pour les modules externes
            $parameters['TFilePathByTitleKey'] = $this->TFilePathByTitleKey;
            $reshook = $hookmanager->executeHooks('attachMoreFiles', $parameters, $this->current_object, $action); // Note that $action and $object may have been modified by some hooks
            if (empty($reshook))
            {
                if (is_array($hookmanager->resArray) && count($hookmanager->resArray))
                {
                    // TODO voir si "array_merge_recursive" correspond au comportement attendu
                    $this->TFilePathByTitleKey = array_merge_recursive($this->TFilePathByTitleKey, $hookmanager->resArray);
                }
            }
            elseif ($reshook > 0) $this->TFilePathByTitleKey = $hookmanager->resArray;


            $param = array('TTileKeyRank' => $this->TTileKeyRank);
            $reshook = $hookmanager->executeHooks('attachSort', $param, $this->TFilePathByTitleKey, $action); // Note that $action and $object may have been modified by some hooks
            if (empty($reshook))
            {
                uksort($this->TFilePathByTitleKey, array($this, 'cmp'));
            }


            if ($action === 'attachments_send')
            {
                dol_include_once('attachments/lib/attachments.lib.php');
                $this->formconfirm = getFormConfirmAttachments($this, $this->TFilePathByTitleKey, GETPOST('trackid'));
                $action = 'presend';
                $_POST['addfile'] = ''; // Permet de bi-passer un setEventMessage de Dolibarr
            }
            // Gestion de l'envoi des données provenant du formconfirm
            elseif ($action === 'confirm_attachments_send')
            {
                // Hack permettant de conserver le text car la méthode "get_form()" cherche uniquement dans $_POST, 2 cas particuliers
//                $_POST['message'] = $_GET['message'];
//                $_POST['subject'] = $_GET['subject']; // Hack plus nécessaire suite aux changements de la fonction "getFormConfirmAttachments" et à l'utilisation d'un formulaire en POST

                $TAttachments = array();
                foreach ($_REQUEST as $k => $v)
                {
                    // On provient d'un formconfirm, et il n'est pas possible de faire passer des tableaux de valeur
                    if (preg_match('/^TAttachments\_[a-f0-9]{32}$/', $k) && !empty($v))
                    {
                        $TAttachments[$v] = $v;
                    }
                }


                include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
                $formmail = new FormMail($this->db);
                $formmail->trackid = GETPOST('trackid');

                // Je ne peux pas me baser sur le chemin complet car une fois joint au mail, les chemins pointe vers le dossier "/temp" du user
                $TSelectedFileName = array();
                if (!empty($_SESSION['listofnames-'.$formmail->trackid])) $TSelectedFileName = explode(';', $_SESSION['listofnames-'.$formmail->trackid]);

                // Set tmp user directory
                $vardir=$conf->user->dir_output."/".$user->id;
                $upload_dir_tmp = $vardir.'/temp';
                if (dol_mkdir($upload_dir_tmp) >= 0)
                {
                    foreach ($this->TFilePathByTitleKey as $titleKey => $TFilePathByRef)
                    {
                        foreach ($TFilePathByRef as $ref => $file_info)
                        {
                            foreach ($file_info as $info)
                            {
                                if (isset($TAttachments[$info['fullname_md5']]))
                                {
                                    $destfull = $upload_dir_tmp.'/'.$info['name'];
                                    dol_copy($info['fullname'], $destfull);

                                    // Update session
                                    $formmail->add_attached_files($destfull, $info['name'], mime_content_type($info['fullname']));
                                }
                                elseif (($k = array_search($info['name'], $TSelectedFileName)) !== false)
                                {
                                    // Fichier précédemment joint et maintenant il a été décoché
                                    $formmail->remove_attached_files($k);
                                    if (is_file($upload_dir_tmp.'/'.$info['name'])) unlink($upload_dir_tmp.'/'.$info['name']);
                                    unset($TSelectedFileName[$k]);
                                    $TSelectedFileName = explode(';', implode(';', $TSelectedFileName));
                                }
                            }
                        }
                    }
                }

                $action = 'presend';
                $_GET['addfile'] = ''; // Permet de bi-passer un setEventMessage de Dolibarr
            }

            if (!empty($this->TFilePathByTitleKey))
            {
                $modelmailselected = GETPOST('modelmailselected', 'alpha');
                if ($modelmailselected != -1)
                {
                    // Permet d'esquiver l'appel à "clear_attached_files()" dans la méthode "get_form()" @see
                    unset($_POST['modelmailselected']);
                    $this->modelmailselected = $modelmailselected;
                }
            }

            $this->action = $action;
        }

		return 0;
	}

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject $object      The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string       $action      Current action (if set). Generally create or edit or null
     * @param   HookManager  $hookmanager Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
	public function getFormMail($parameters, &$object, &$action, $hookmanager)
    {
        if (in_array($this->action, array('presend', 'send')))
        {
            if (!empty($this->TFilePathByTitleKey))
            {
                print '
                    <script type="text/javascript">
                        $(function() {
                            let attachments_button = $("<span class=\'fa fa-paperclip\' onclick=\'attachments_send()\'></span>");
                            $("#addfile").after(attachments_button);

                            attachments_send = function()
                            {
                                $("#action").val("attachments_send"); // Maj de "action" pour interception côté "doActions"
                                $("#addfile").click(); // Simulation du clique sur le bouton "Joindre ce fichier"
                            }

                            if (window.location.hash === "")
                            {
                                let attachments_top = document.getElementById("formmail").offsetTop; //Getting Y of target element
                                window.scrollTo(0, attachments_top);
                            }

                        });
                    </script>
                ';

                if (!empty($this->formconfirm)) print $this->formconfirm;

                if (!empty($this->modelmailselected))
                {
                    $object->param['models_id'] = $this->modelmailselected;
                }
            }
        }

        return 0;
    }

    /**
     * @param string $a First element
     * @param string $b Second element
     * @return int
     */
    private function cmp($a, $b)
    {
        global $langs;

        if (isset($this->TTileKeyRank[$a]) && isset($this->TTileKeyRank[$b])) return $this->TTileKeyRank[$a] - $this->TTileKeyRank[$b];
        elseif (isset($this->TTileKeyRank[$a])) return -1;
        elseif (isset($this->TTileKeyRank[$b])) return 1;
        else
        {
            return strcmp($langs->trans($a), $langs->trans($b));
        }
    }

    /**
     * @param string $key                   key
     * @param string $fullpathselecteddir   directory to scan (value must finish with '/')
     * @param string $dir                   current sub directory
     * @return null
     */
    private function nyandog($key, $fullpathselecteddir, $dir = '')
    {
        if (is_dir($fullpathselecteddir))
        {
            $files = @scandir($fullpathselecteddir);
            foreach ($files as $file)
            {
                if ($file == '.' || $file == '..') continue;

                $fullname = $fullpathselecteddir.$file;

                if (is_dir($fullname))
                {
                    $this->nyandog($key, $fullname, $file);
                }
                elseif (is_file($fullname))
                {
                    $fullname_md5 = md5($fullname);
                    $name = pathinfo($fullname, PATHINFO_BASENAME);

                    $this->TFilePathByTitleKey[$key][$dir][$fullname_md5] = array(
                        'name' => $name
                        ,'path' => $fullpathselecteddir
                        ,'fullname' => $fullname
                        ,'fullname_md5' => $fullname_md5
                    );
                }
            }
        }
    }
}
