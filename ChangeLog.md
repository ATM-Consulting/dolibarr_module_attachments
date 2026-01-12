# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]



## Release 1.5
- FIX : Attached files societe for id - *12/01/2025* -1.5.9
- FIX : Compat V23 - *02/12/2025* -1.5.8
- FIX : DA027218 - Fixing hooks for Agefodd compatibility - *24/10/2025* - 1.5.7
- FIX DA026978 : Attachments of third party not available even when conf `ATTACHMENTS_INCLUDE_OBJECT_LINKED` was enabled - *29/08/2025* - 1.5.6
- FIX : Warning Constant INC_FROM_DOLIBARR already defined - *18/08/2025* - 1.5.5
- FIX : Compat v22 - *02/07/2025* - 1.5.4
- FIX : $action == "presend" écrasé par d'autres module lors de l'envoi en getpost - *02/04/2025* - 1.5.3
- FIX : Displays the button to attach a file in all cases, with a warning message if no file is available - *07/02/2025* - 1.5.2
- FIX : Compat v21 - *17/12/2024* - 1.5.1
        Suppression retropcompat <=V15

- FIX : Compat v20 - Changed Dolibarr compatibility range to 16 min - 20 max - *23/07/2024* - 1.5.0

## Release 1.4

- FIX : PHP 8.2 Compatibility and object compatibility - *12/01/2023* - 1.4.2
- FIX : MIN PHP/DOLIBARR  - *30/11/2023* - 1.4.1  
- NEW : compatV19 - *24/11/2023* 1.4.0  

## Release 1.3

- FIX : Compat mass action  *20/06/2023* 1.3.4
- FIX : Compat V17  *20/01/2023* 1.3.3
- FIX : Module icon  *25/07/2022* 1.3.2
- FIX : Permet de ne pas avoir l'attachement automatique de la PJ sur les modèles n'en possédant pas *25/05/2022* 1.3.1
- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" *11/05/2022* 1.3.0

## Release 1.2

- FIX: Compatibility V16 - Family - *03/06/2022* - 1.2.2
- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" *11/05/2022* 1.2.0

## Release 1.1

- NEW : add attachments to agefodd formmails *09/03/2022* - 1.1.0

## Release 1.0
- FIX : error message in v14 due to formconfirm change *25/11/2021* - 1.0.7
- FIX : warning (foreach over non-arrays) *20/10/2021* - 1.0.6
- FIX : v14 compatibility (only change is in module descriptor) *29/06/2021* - 1.0.5
- FIX : FIX clear attached files in mail using template *08/06/2021* - 1.0.5
- FIX : attachment didnt handle shipping *07/05/2021* - 1.0.4
- FIX : attachment should manage PRODUCT_USE_OLD_PATH_FOR_PHOTO *23/04/2021* - 1.0.3
- Remove unused Box
