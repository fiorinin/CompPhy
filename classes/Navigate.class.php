<?php

require_once 'Projet.class.php';

abstract class Navigate {
    public static function pagination($isMember) {
        if (isset($_GET['p'])) {
                switch($_GET['p']) {
                    case 'projects' :
                        if ($isMember) return 'includes/projectslist.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'project' : 
                        return 'includes/project.inc.php';
                        break;
                    case 'settingsp' : 
                        if ($isMember) return 'includes/settings.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'forump' : 
                        //UPD if ($isMember) return 'includes/forump.inc.php';
                        //UPD else return 'includes/login.inc.php';
                        return 'includes/forump.inc.php';
                        break;
                    case 'new' : 
                        if ($isMember) return 'includes/newproject.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'disconnect' : 
                        if ($isMember) return 'includes/disconnect.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'userguide' : 
                        return 'includes/usersguide.inc.php';
                        break;
                    case 'terms' : 
                        return 'includes/terms.inc.php';
                        break;
                    case 'links' : 
                        return 'includes/links.inc.php';
                        break;
                    case 'settings' : 
                        if ($isMember) return 'includes/accountsettings.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'documents' : 
                        //UPD if ($isMember) return 'includes/documents.inc.php';
                        //UPD else return 'includes/login.inc.php';
                        return 'includes/documents.inc.php';
                        break;
                    case 'member' : 
                        if ($isMember) return 'includes/member.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'error' : 
                        return 'includes/errorpage.inc.php';
                        break;
                    case 'getresult' :
                        return 'includes/get_result.inc.php';
                        break;
                    case 'exehandler' :
                        if ($isMember) return 'exe/exe_handler.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'faq' :
                        return 'includes/faq.inc.php';
                        break;
                    case 'contact' :
                        return 'includes/contactus.inc.php';
                        break;
                    case 'login' : 
                        if ($isMember) return 'includes/projectslist.inc.php';
                        else return 'includes/login.inc.php';
                        break;
                    case 'register' : 
                        if ($isMember) return 'includes/projectslist.inc.php';
                        else return 'includes/inscription.inc.php';
                        break;
                    case 'forgot' : 
                        if ($isMember) return 'includes/projectslist.inc.php';
                        else return 'includes/forgotpasswd.inc.php';
                        break;
                    Default :
                        /*if ($isMember) return 'includes/projectslist.inc.php';
                        else */return 'includes/home.inc.php';
                }
            }
            else {
                //if (!$isMember)
                    return 'includes/home.inc.php';
                /*else
                    return 'includes/projectslist.inc.php';*/
            }
    }
    
    public static function redirect($page, $id = '', $action='') {
        //echo ROOT.'?p='.$page.($res != '' ? '&res='.$res : '').($action != '' ? '&a='.$action : '');
        header('Location: '.ROOT.'compphy/?p='.$page.($id != '' ? '&id='.$id : '').($action != '' ? '&a='.$action : ''));
    }
    
    public static function redirectMessage($page, $message, $level, $id = '', $action='') {
        if($message != '' && $level != '') {
            Navigate::addMessage($message, $level);
            header('Location: '.ROOT.'compphy/?p='.$page.($id != '' ? '&id='.$id : '').($action != '' ? '&a='.$action : ''));
        } else
            redirect($page, $id, $action);
    }
    
    public static function addMessage($message, $level) {
        if($message != '' && $level != '') {
            $_SESSION['level'] = $level;
            $_SESSION['message'] = preg_replace("/\r\n|\r|\n/",'<br>',$message);
        }
    }
    
    public static function needMenu() {
        if(!Navigate::displayDesign())
            return false;
        if (isset($_GET['p'])) {
            switch($_GET['p']) {
                case 'new' : 
                    return false;
                    break;
                case 'disconnect' : 
                    return false;
                    break;
                Default :
                    return true;
            }
        }
        else {
            return true;
        }
    }
    
    public static function displayDesign() {
        if (isset($_GET['p'])) {
            switch($_GET['p']) {
                case "getresult" :
                    return false;
                    break;
                case "exehandler" :
                    return false;
                    break;
                case "disconnect" :
                    return false;
                    break;
                case "new" :
                    //if(isset($_GET['id']) || isset($_GET['folder']))
                    //    return false;
                    return true; //else
                    break;
                case "forump":
                    if(isset($_POST['message']) && $_POST['message'] != '')
                        return false;
                    else return true;
                    break;
                Default :
                    return true;
            }
        }
        else {
            return true;
        }
    }
    
    public static function projectTemplate() {
        if (isset($_GET['p'])) {
            switch($_GET['p']) {
                case "settingsp" :
                    return true;
                    break;
                case "forump" :
                    return true;
                    break;
                case "project" :
                    return true;
                    break;
                case "userguide" :
                    return true;
                    break;
                case "forum" :
                    return true;
                    break;
                case "documents" :
                    return true;
                    break;
                Default :
                    if(isset($_SESSION['membre']))
                        return true;
                    else return false;
            }
        }
        else {
            if(isset($_SESSION['membre']))
                return true;
            else return false;
        }
    }
    
    public static function fullWidth() {
        if (isset($_GET['p'])) {
            switch($_GET['p']) {
                case "project" :
                    return true;
                    break;
                Default : return false;
            }
        } else
            return false;
    }
    
}

?>
