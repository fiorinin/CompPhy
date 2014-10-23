<?php

global $UploadError;

class Uploadf {
    
    // constantes methode de verification des entetes 
    const CST_UPL_HEADER_BROWSER  = 0; // Navigateur
    const CST_UPL_HEADER_MIMETYPE = 1; // librairie mime_type
    const CST_UPL_HEADER_FILEINFO = 2; // librairie fileinfo (PECL)
    
    // constantes methode d'ecriture des fichiers
    const CST_UPL_WRITE_ERASE  = 0;
    const CST_UPL_WRITE_COPY   = 1;
    const CST_UPL_WRITE_IGNORE = 2;
    
    // constantes types d'erreurs 1 : appairage avec les erreurs retournees par PHP
    const CST_UPL_ERR_NONE                  = UPLOAD_ERR_OK;            // Aucune erreur, le telechargement est valide
    const CST_UPL_ERR_EXCEED_INI_FILESIZE   = UPLOAD_ERR_INI_SIZE;      // la taille du fichier excede la directive max_file_size (php.ini)
    const CST_UPL_ERR_EXCEED_FORM_FILESIZE  = UPLOAD_ERR_FORM_SIZE;     // la taille du fichier excede la directive max_file_size (formulaire)
    const CST_UPL_ERR_CORRUPT_FILE          = UPLOAD_ERR_PARTIAL;       // le fichier n'a pas ete charge completement
    const CST_UPL_ERR_EMPTY_FILE            = UPLOAD_ERR_NO_FILE;       // champ du formulaire vide
    const CST_UPL_ERR_NO_TMP_DIR            = UPLOAD_ERR_NO_TMP_DIR;    // Un dossier temporaire est manquant. Introduit en PHP 4.3.10 et PHP 5.0.3.
    const CST_UPL_ERR_CANT_WRITE            = UPLOAD_ERR_CANT_WRITE;    // echec de l'ecriture du fichier sur le disque. Introduit en PHP 5.1.0.
    const CST_UPL_ERR_EXTENSION             = UPLOAD_ERR_EXTENSION;     // L'envoi de fichier est arrete par l'extension. Introduit en PHP 5.2.0.
    
    // constantes types d'erreurs 2 : erreurs supplementaires detectees par la classe
    const CST_UPL_ERR_UNSAFE_FILE           = 20; // fichier potentiellement dangereux
    const CST_UPL_ERR_WRONG_MIMETYPE        = 21; // le fichier n'est pas conforme a la liste des entetes autorises
    const CST_UPL_ERR_WRONG_EXTENSION       = 22; // le fichier n'est pas conforme a la liste des extensions autorisees
    const CST_UPL_ERR_IMG_EXCEED_MAX_WIDTH  = 23; // largeur max de l'image excede celle autorisee
    const CST_UPL_ERR_IMG_EXCEED_MAX_HEIGHT = 24; // hauteur max de l'image excede celle autorisee
    const CST_UPL_ERR_IMG_EXCEED_MIN_WIDTH  = 25; // largeur min de l'image excede celle autorisee
    const CST_UPL_ERR_IMG_EXCEED_MIN_HEIGHT = 26; // hauteur min de l'image excede celle autorisee
    
    const CST_UPL_EXT_FILEINFO  = 'fileinfo';
    const CST_UPL_EXT_MIMEMAGIC = 'mime_magic';
    const CST_UPL_PHP_VERSION   = '5.0.4';
    
    
    /**
     * Etant donne qu'entre les differents navigateurs les informations sur les entetes de fichiers peuvent differer, 
     * il est dorenavant possible de laisser PHP s'occuper du type MIME. L'ajout de cette fonctionnalite necessite 
     * l'activation de la librairie mime_magic ou fileinfo.
     * 
     * Positionne a self::CST_UPL_HEADER_BROWSER, la verification des entetes de fichiers se fera comme auparavant, cad via les informations retournees par le navigateur.
     * Positionne a self::CST_UPL_HEADER_MIMETYPE, la verification est base sur les fonctions Mimetype de php (extension mime_magic)
     * Positionne a self::CST_UPL_HEADER_FILEINFO, la verification est base sur la classe fileinfo() (librairie PECL)
     * 
     * @var integer
     */
    public $phpDetectMimeType = self::CST_UPL_HEADER_BROWSER;
    
    
    /**
     * Initialisee dynamiquement dans la fonction loadPECLInfoLib() suivant le parametrage
     * de la propriete $phpDetectMimeType.
     *
     * @var string $path . $filename
     */
    public $magicfile = '';
    
    
    /**
     * Par defaut la classe genere des champs de formulaire a la norme x-html.
     * 
     * @var boolean
     */
    public $xhtml = true;
    
    
    /**
     * Taille maximale exprimee en kilo-octets pour l'upload d'un fichier.
     * Valeur par defaut : celle configuree dans le php.ini (cf. constructeur).
     * 
     * @var integer
     */
    public $MaxFilesize = null;
    
    
    /**
     * Largeur maximum d'une image exprimee en pixel.
     * 
     * @var int
     */
    public $ImgMaxWidth = null;
    
    
    /**
     * Hauteur maximum d'une image exprimee en pixel.
     * 
     * @var int
     */
    public $ImgMaxHeight = null;
    
    
    /**
     * Largeur minimum d'une image exprimee en pixel.
     * 
     * @var int
     */
    public $ImgMinWidth = null;
    
    
    /**
     * Hauteur minimum d'une image exprimee en pixel.
     * 
     * @var int
     */
    public $ImgMinHeight = null;
    
    
    /**
     * Repertoire de destination dans lequel vont etre charges les fichiers.
     * Accepte les chemins relatifs et absolus.
     * 
     * @var string
     */
    public $DirUpload = '';
    
    
    /**
     * Nombre de champs de type file que la classe devra gerer.
     *
     * @var integer 
     */
    public $Fields = 1;
    
    
    /**
     * Parametres a ajouter aux champ de type file (ex: balise style, evenements JS...)
     * 
     * @var string
     */
    public $FieldOptions = '';
    
    
    /**
     * Definit si les champs sont obligatoires ou non.
     * 
     * @var boolean
     */
    public $Required = false;
    
    
    /**
     * Politique de securite max : ignore tous les fichiers executables / interpretable.
     * Deprecie. Garde pour compatibilite descendante.
     * 
     * @var boolean
     */
    public $SecurityMax = false;
    
    
    /**
     * Permet de preciser un nom pour le fichier a uploader.
     * Peut etre utilise conjointement avec les proprietes $Suffixe / $Prefixe
     * 
     * @var string
     */
    public $Filename = '';
    
    
    /**
     * Prefixe pour le nom du fichier sur le serveur.
     * 
     * @var string
     */
    public $Prefixe = '';
    
    
    /**
     * Suffixe pour le nom du fichier sur le serveur.
     * 
     * @var string
     */
    public $Suffixe = '';
    
    
    /**
     * Methode a employer pour l'ecriture des fichiers si un fichier de meme nom est present dans le repertoire :
     * self::CST_UPL_WRITE_ERASE  : le fichier du serveur est ecrase par le nouveau fichier.
     * self::CST_UPL_WRITE_COPY   : le nouveau fichier est uploade mais precede de la mention 'copie_de_'.
     * self::CST_UPL_WRITE_IGNORE : le nouveau fichier est ignore.
     * 
     * @var integer
     */
    public $WriteMode = self::CST_UPL_WRITE_ERASE;
    
    
    /**
     * Chaine de caracteres representant les entetes de fichiers autorises (mime-type).
     * Les entetes doivent etre separees par des points virgules.
     * <code>$Upload->MimeType = 'image/gif;image/pjpeg';</code>
     * 
     * @var string
     */
    public $MimeType = '';
    
    
    /**
     * Positionne a [true], une erreur de configuration du composant sera envoye en sortie ecran et bloquera le script
     * en cours d'execution.
     * 
     * @var boolean
     */
    public $TrackError = true;
    
    
    /** 
     * Reaffection des droits utilisateur apres ecriture du document sur le serveur.
     * 
     * @var string
     */
    public $Permission = 0666;
    
    
    /**
     * Liste des extensions autorisees separees par un point virgule.
     * <code>$Upload->Extension = ".dat;.csv;.txt";</code>
     * 
     * @var string
     */
    public $Extension = '';
    
    
    /**
     * En remplacement de la variable globale $UploadError.
     *
     * @var boolean.
     */
    private $uplSuccess = true;
    
    
    /**
     * Tableau des erreurs rencontres durant l'upload.
     *
     * @var array
     */
    private $ArrOfError = array();
    
    
    /**
     * proprietes temporaires utilisees lors du parcours de la variable globale $_FILES
     */
    private $_field = 0;                // position du champ dans le formulaire a partir de 1 (0 etant reserve au champ max_file_size)
    private $_size  = 0;                // poids du fichier
    public $_type  = '';                // type mime renvoye par le navigateur
    private $_name  = '';               // nom du fichier
    private $_temp  = '';               // emplacement temporaire
    private $_ext   = '';               // extension du fichier
    private $_error = UPLOAD_ERR_OK;    // Erreur upload retournee par PHP
    
    
    /**
     * Tableaux des messages d'erreurs sur l'echec d'une upload.
     *
     * @see setError()
     * @var array
     */
    private $errorMsg = array(
        self::CST_UPL_ERR_EXCEED_INI_FILESIZE   => 'Le document [%FILENAME%] excède la directive [upload_max_filesize] du fichier de configuration [php.ini].',
        self::CST_UPL_ERR_EXCEED_FORM_FILESIZE  => 'Le document [%FILENAME%] excède la directive MAX_FILE_SIZE spécifiée dans le formulaire.',
        self::CST_UPL_ERR_CORRUPT_FILE          => 'Document [%FILENAME%] corrompu.',
        self::CST_UPL_ERR_EMPTY_FILE            => 'Le champ [parcourir] du formulaire d\'upload n\'a pas été renseigné.',
        self::CST_UPL_ERR_NO_TMP_DIR            => 'Un dossier temporaire est manquant.',
        self::CST_UPL_ERR_CANT_WRITE            => 'Échec de l\'écriture du fichier [%FILENAME%] sur le disque.',
        self::CST_UPL_ERR_EXTENSION             => 'L\'envoi du fichier [%FILENAME%] est arrêté par l\'extension.',
        self::CST_UPL_ERR_UNSAFE_FILE           => 'Document [%FILENAME%] potentiellement dangereux.',
        self::CST_UPL_ERR_WRONG_MIMETYPE        => 'Le document [%FILENAME%] n\'est pas conforme à la liste des entêtes autorisées.',
        self::CST_UPL_ERR_WRONG_EXTENSION       => 'Le document [%FILENAME%] n\'est pas conforme à la liste des extensions autorisées.',
        self::CST_UPL_ERR_IMG_EXCEED_MAX_WIDTH  => 'La largeur de l\'image [%FILENAME%] excède celle autorisée.',
        self::CST_UPL_ERR_IMG_EXCEED_MAX_HEIGHT => 'La hauteur de l\'image [%FILENAME%] excède celle autorisée.',
        self::CST_UPL_ERR_IMG_EXCEED_MIN_WIDTH  => 'La largeur de l\'image [%FILENAME%] est inférieure à celle autorisée.',
        self::CST_UPL_ERR_IMG_EXCEED_MIN_HEIGHT => 'La hauteur de l\'image [%FILENAME%] est inférieure à celle autorisée.'
    );
    
    
    
    /**
     * Constructeur.
     */
    public function __construct() {
        $this->MaxFilesize = ereg_replace('M', '', @ini_get('upload_max_filesize')) * 1024;
    }
    
    
    
    /**
     * Lance l'initialisation de la classe pour la generation du formulaire
     * 
     * @access public
     */
    public function InitForm() {
        $this->SetMaxFilesize();
        $this->CreateFields();
    }
    
    
    
    /**
     * Retourne le tableau des erreurs survenues durant l'upload
     *
     * <code>
     * if (!$Upload->Execute()) {
     *     print_r($Upload-> GetError);
     * }
     * </code>
     *
     * @access public
     * @param integer $num_field numero du champ 'file' sur lequel on souhaite recuperer l'erreur
     * @return array
     */
    public function GetError($num_field='') {
        return (Empty($num_field)) ? $this->ArrOfError : $this->ArrOfError[$num_field];
    }
    
    
    
    /**
     * Retourne le tableau contenant les informations sur les fichiers uploades
     *
     * <code>
     * if (!$Upload->Execute()) {
     *     print_r($Upload->GetSummary());
     * }
     * </code>
     *
     * @access public
     * @param integer $num_field    numero du champ 'file' sur lequel on souhaite recuperer les informations
     * @return array                tableau des infos fichiers
     */
    public function GetSummary($num_field = null) {
        
        if (!isSet($num_field)) {
            $result = (isSet($this->Infos)) ? $this->Infos : false;
        }
        else {
            $result = (isSet($this->Infos[$num_field])) ? $this->Infos[$num_field] : false;
        }
        
        return $result;
    }
    
    
    
    /**
     * Lance les differents traitements necessaires a l'upload
     * 
     * @return boolean
     */
    public function Execute(){
        @set_time_limit(0);
        
        $this->CheckConfig();
        $this->CheckUpload();
        
        return $this->uplSuccess;
    }
    
    
    
    /**
     * Permet de modifier le message d'erreur en cas d'echec d'une upload.
     * Le libelle peut contenir le mot cle %FILENAME%.
     * 
     * @var int    $code_erreur
     * @var string $libelle
     * @see AddError()
     * @return boolean
     */
    public function setErrorMsg($code_erreur, $libelle) {
        
        if (!isSet($this->errorMsg[$code_erreur])) {
            $this->Error('le parametre $code_erreur passe � la Methode [setErrorMsg] est erron�.');
            return false;
        }
        
        $this->errorMsg[$code_erreur] = $libelle;
        
        return true;
    }
    
    
    
    /**
     * Methode de definition des proprietes sur les dimensions des images.
     * La verification sur le bon format est gere dans la Methode CheckImgPossibility().
     *
     * @param integer $maxWidth
     * @param integer $minWidth
     * @param integer $maxHeight
     * @param integer $minHeight
     */
    public function SetImgDim($maxWidth = null, $minWidth = null, $maxHeight = null, $minHeight = null) {
        $this->ImgMaxHeight = $maxHeight;
        $this->ImgMaxWidth  = $maxWidth;
        $this->ImgMinHeight = $minHeight;
        $this->ImgMinWidth  = $minWidth;
    }
    
    
    
    /**
     * Methode lancant les verifications sur les fichiers.
     * Initialisation de la propriete $uplSuccess a false si erreur, lance la 
     * Methode d'ecriture toutes les verifications sont ok.
     * @access private
     */
    private function CheckUpload() {
        
        if (!isSet($_FILES['userfile']['tmp_name'])) {
            $this->Error('Le tableau contenant les informations des fichiers t�l�charges est vide.' . PHP_EOL .
                         'Si vous avez renseign� un champ de fichier, il est probable que la taille de ce dernier exc�de les capacit�s de chargement du serveur.');
        }
        
        $nbFiles = count($_FILES['userfile']['tmp_name']);
        
        // Parcours des fichiers a uploader
        for ($i=0; $i < $nbFiles; $i++)  {
            
            // Recup des particularita du fichier dans les proprietes temporaires
            $this->_field++;
            $this->_size  = $_FILES['userfile']['size'][$i];
            $this->_type  = $_FILES['userfile']['type'][$i];
            $this->_name  = $_FILES['userfile']['name'][$i];
            $this->_temp  = $_FILES['userfile']['tmp_name'][$i];
            $this->_ext   = strtolower(substr($this->_name, strrpos($this->_name, '.')));
            $this->_error = $_FILES['userfile']['error'][$i];
            
            // On execute les verifications demandees
            if ($this->_error == UPLOAD_ERR_OK && is_uploaded_file($_FILES['userfile']['tmp_name'][$i])) {
                
                // verification du type mime via la librairie "mime_magic" : on surcharge la propriete _type avec le type renvoye par la fonction mime_content_type
                if ($this->phpDetectMimeType === self::CST_UPL_HEADER_MIMETYPE) {
                    $this->_type = mime_content_type($_FILES['userfile']['tmp_name'][$i]);
                }
                
                // verification du type mime via la librairie "file_info" : on surcharge la propriete _type avec le type renvoye par la fonction fileinfo()
                else if ($this->phpDetectMimeType === self::CST_UPL_HEADER_FILEINFO) {
                    
                    $fInfo = new finfo(FILEINFO_MIME, $this->magicfile);
                    
                    // La classe retourne une chaine de type "mime; charset". Seul la partie mime nous interesse.
                    $mime = explode(';', $fInfo->file($_FILES['userfile']['tmp_name'][$i]));
                    
                    $this->_type = trim($mime[0]);
                    
                    unset($fInfo, $mime);
                }
                
                // verification des erreurs suplementaires detectees par la classe
                if (!$this->CheckSecurity() || !$this->CheckMimeType() || !$this->CheckExtension() || !$this->CheckImg()) {
                    continue;
                }                
            }
            else {
                // Erreur retournee par PHP
                $this->AddError($this->_error);
                continue;
            }
            
            // Le fichier a passe toutes les verifications, on procede a l'ecriture
            $this->WriteFile($this->_name, $this->_type, $this->_temp, $this->_ext, $this->_field);
        }
    }
    
    
    
    /**
     * Ecrit le fichier sur le serveur.
     *
     * @access private
     * @param string $name        nom du fichier sans son extension
     * @param string $type        entete du fichier
     * @param string $temp        chemin du fichier temporaire
     * @param string $temp        extension du fichier precedee d'un point
     * @param string $num_fied    position du champ dans le formulaire a compter de 1
     * @return bool               true/false => succes/erreur
     */
    private function WriteFile($name, $type, $temp, $ext, $num_field) {
        
        $new_filename = null;
        
        if (is_uploaded_file($temp)) {
            
            // Nettoyage du nom original du fichier
            $new_filename = (Empty($this->Filename)) ? $this->CleanFileName(substr($name, 0, strrpos($name, '.'))) : $this->Filename;
            
            // Ajout Prefixes / suffixes + extension :
            $new_filename = $this->Prefixe . $new_filename . $this->Suffixe . $ext;
            
            switch ($this->WriteMode) {
                
                case self::CST_UPL_WRITE_ERASE :
                    $uploaded = @move_uploaded_file($temp, $this->DirUpload . $new_filename);
                break;
                    
                case self::CST_UPL_WRITE_COPY :
                    
                    if ($this->AlreadyExist($new_filename)) {
                        $new_filename = 'copie_de_' . $new_filename;
                    }
                    
                    $uploaded = @move_uploaded_file($temp, $this->DirUpload . $new_filename);
                    
                 break;
                
                case self::CST_UPL_WRITE_IGNORE : 
                
                    if ($this->AlreadyExist($new_filename)) $uploaded = true;
                    else                                    $uploaded = @move_uploaded_file($temp, $this->DirUpload . $new_filename);
                    
                break;
            }
            
            // Informations pouvant etre utiles au developpeur (si le fichier a pu etre copie)
            if ($uploaded) {
                
                $filesize = filesize($this->DirUpload . $new_filename);
                
                $this->Infos[$num_field]['nom']          = $new_filename;
                $this->Infos[$num_field]['nom_originel'] = $name;
                $this->Infos[$num_field]['chemin']       = $this->DirUpload . $new_filename;
                $this->Infos[$num_field]['poids']        = number_format($filesize/1024, 3, '.', '');
                $this->Infos[$num_field]['octets']       = $filesize;
                $this->Infos[$num_field]['mime-type']    = $type;
                $this->Infos[$num_field]['extension']    = $ext;
            }
            else {
                $this->Error('move_uploaded_file() a g�n�r� une erreur. Verifiez les droits d\'ecriture du repertoire temporaire d\'upload [' . @ini_get('upload_tmp_dir') . '] et celui du repertoire de destination [' . $this->DirUpload . '].');
                return false;
            }
            
            // Mise en place des droits
            if (function_exists('chmod')) {
                @chmod($this->DirUpload . $new_filename, $this->Permission);
            }
            
            return true;
            
        } // End is_uploaded_file
        
        return false;
    }
    
    
    
    /**
     * Verifie si le fichier passe en parametre existe deja dans le repertoire DirUpload
     * 
     * @access private
     * @return bool
     */
    private function AlreadyExist($file) {
        return (file_exists($this->DirUpload . $file));
    }
    
    
    
    /**
     * Verifie la hauteur/largeur d'une image
     * 
     * @access private
     * @return bool
     */
    private function CheckImg() {
        
        $dim = @getimagesize($this->_temp);
        $res = true;
        
        // On travaille sur un fichier image
        if ($dim != false) {
            
            if (!Empty($this->ImgMaxWidth) && $dim[0] > $this->ImgMaxWidth)  {
                $this->AddError(self::CST_UPL_ERR_IMG_EXCEED_MAX_WIDTH);
                $res = false;
            }
            
            if (!Empty($this->ImgMaxHeight) && $dim[1] > $this->ImgMaxHeight) {
                $this->AddError(self::CST_UPL_ERR_IMG_EXCEED_MAX_HEIGHT);
                $res = false;
            }
            
            if (!Empty($this->ImgMinWidth)  && $dim[0] < $this->ImgMinWidth) {
                $this->AddError(self::CST_UPL_ERR_IMG_EXCEED_MIN_WIDTH);
                $res = false;
            }
            
            if (!Empty($this->ImgMinHeight) && $dim[1] < $this->ImgMinHeight) {
                $this->AddError(self::CST_UPL_ERR_IMG_EXCEED_MIN_HEIGHT);
                $res = false;
            }
        }
                
        return $res;
    }
    
    
    
    /**
     * Verifie l'extension des fichiers suivant celles precisees dans $Extension
     * @access private
     * @return bool
     */
    private function CheckExtension() {
        
        $ArrOfExtension = explode(';', strtolower($this->Extension));
        
        if (!Empty($this->Extension) && !in_array($this->_ext, $ArrOfExtension)) {
            $this->AddError(self::CST_UPL_ERR_WRONG_EXTENSION);
            return false;
        }
        
        return true;
    }
    
    
    
    /**
     * Verifie l'entete des fichiers suivant ceux precises dans $MimeType
     * @access private
     * @return bool
     */
    private function CheckMimeType() {
        
        $ArrOfMimeType = explode(';', $this->MimeType);
        
        if (!Empty($this->MimeType) && !in_array($this->_type, $ArrOfMimeType)) {
            $this->AddError(self::CST_UPL_ERR_WRONG_MIMETYPE);
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Ajoute une erreur pour le fichier en cours de lecture dans le tableau des erreur.
     * Voir http://www.php.net/manual/fr/features.file-upload.errors.php
     * 
     * @access private
     */
    private function AddError($code_erreur) {
        
        // Deprecie. Garde pour compatibilite.
        global $UploadError;
        
        $positionnerEnErreur = true;
        
        switch ($code_erreur) {
            
            case self::CST_UPL_ERR_NONE :
               $positionnerEnErreur = false;
            break;
            
            case '' :
                $msg = 'Exception lev�e mais non d�cel�e pour le document %FILENAME%.';
            break;
            
            case self::CST_UPL_ERR_EMPTY_FILE :
                $msg = $this->errorMsg[$code_erreur];
                $positionnerEnErreur = $this->Required;
            break;
            
            default :
                $msg = $this->errorMsg[$code_erreur];
                $positionnerEnErreur = true;
            break;
            
        }
        
        if ($positionnerEnErreur) {
            
            $msg              = str_replace('%FILENAME%', $this->_name, $msg);
            $UploadError      = true;
            $this->uplSuccess = false;
            
            $this->ArrOfError[$this->_field][$code_erreur] = $msg;
        }
    }
    
    
    
    /**
     * Verifie les criteres de la politique de securite
     * OV : 26/10/07 => Deprecie.
     * 
     * @access private
     * @return bool
     */
    private function CheckSecurity() {
        
        // Bloque tous les fichiers executables, et tous les fichiers php pouvant etre interprete mais dont l'entete ne peut les identifier comme etant dangereux
        if ($this->SecurityMax === true && ereg ('application/octet-stream', $this->_type) || preg_match("/.php$|.inc$|.php3$/i", $this->_ext)) {
            $this->AddError(self::CST_UPL_ERR_UNSAFE_FILE);
            return false;
        }
        
        return true;
    }
    
    
    
    /**
     * Verifie et formate le chemin de destination :
     *     - definit comme rep par defaut celui de la classe
     *     - teste l'existance du repertoire et son acces en ecriture
     * @access private
     */
    private function CheckDirUpload() {
        
        // Si aucun repertoire n'a ete precise, on prend celui de la classe
        if (Empty($this->DirUpload)) $this->DirUpload = dirname(__FILE__);
        
        $this->DirUpload = $this->FormatDir($this->DirUpload);
        
        // Le repertoire existe?
        if (!is_dir($this->DirUpload)) $this->Error('Le repertoire de destination sp�cifi�e par la propriete DirUpload n\'existe pas.');
        
        // Anciennement, le test sur le droit en ecriture etait gere via la fonction is_writeable() ici.
        // Malheureusement, pour des raisons inconnus, ce test pouvait genere une erreur alors que le repertoire de destination etait correctement configure (Windows Server 2003).
        // Le test est finalement delocalise lors de l'ecriture du fichier via la fonction move_uploaded_file().
    }
    
    
    
    /**
     * Formate le repertoire passe en parametre
     * - convertit un chemin relatif en chemin absolu
     * - ajoute si besoin le dernier slash (ou antislash suivant le systeme)
     * 
     * @access private
     */
    private function FormatDir($Dir) {
        
        // Convertit les chemins relatifs en chemins absolus
        if (function_exists('realpath')) {
            if (realpath($Dir)) $Dir = realpath($Dir);
        }
        
        // Position du dernier slash/antislash
        if ($Dir[strlen($Dir)-1] != DIRECTORY_SEPARATOR) $Dir .= DIRECTORY_SEPARATOR;
        
        return $Dir;
    }
    
    
    
    /**
     * Formate la chaine passee en parametre en nom de fichier standard (pas de caracteres speciaux ni d'espaces)
     * @access private
     * @param  string $str   chaine a formater
     * @return string        chaine formatee
     */
    private function CleanFileName($str) {
        
        $return = '';
        
        for ($i=0; $i <= strlen($str)-1; $i++) {
            if (eregi('[a-z]',$str{$i}))              $return .= $str{$i};
            elseif (eregi('[0-9]', $str{$i}))         $return .= $str{$i};
            elseif (ereg('[������������]', $str{$i})) $return .= 'a';
            elseif (ereg('[��]', $str{$i}))           $return .= 'a';
            elseif (ereg('[��]', $str{$i}))           $return .= 'c';
            elseif (ereg('[��������E]', $str{$i}))    $return .= 'e';
            elseif (ereg('[��������]', $str{$i}))     $return .= 'i';
            elseif (ereg('[����������]', $str{$i}))   $return .= 'o';
            elseif (ereg('[��������]', $str{$i}))     $return .= 'u';
            elseif (ereg('[��ݟ]', $str{$i}))         $return .= 'y';
            elseif (ereg('[ ]', $str{$i}))            $return .= '_';
            elseif (ereg('[.]', $str{$i}))            $return .= '_';
            else                                      $return .= $str{$i};
        }
        
        return str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '', $return);
    }
    
    
    
    /**
     * Conversion du poids maximum d'un fichier exprimee en Ko en octets
     * @access private
     */
    private function SetMaxFilesize() {
        (is_numeric($this->MaxFilesize)) ? $this->MaxFilesize = $this->MaxFilesize * 1024 : $this->Error('la propriete MaxFilesize doit etre une valeur num�rique');
    }
    
    
    
    /**
     * Cree les champs de type fichier suivant la propriete Fields dans un tableau $Field. Ajoute le contenu de FieldOptions aux champs.
     * @access private
     */
    private function CreateFields() {
        
        if (!is_int($this->Fields)) {
            $this->Error('la propriete Fields doit etre un entier');
        }
        
        for ($i=0; $i <= $this->Fields; $i++) {
            if ($i == 0)  $this->Field[] = ($this->xhtml) ? '<input type="hidden" name="MAX_FILE_SIZE" value="'. $this->MaxFilesize .'" />' : '<input type="hidden" name="MAX_FILE_SIZE" value="'. $this->MaxFilesize .'">';
            else          $this->Field[] = ($this->xhtml) ? '<input type="file" name="userfile[]" '. $this->FieldOptions .'/>'              : '<input type="file" name="userfile[]" '. $this->FieldOptions .'>';
        }
    }
    
    
    
    /**
     * Verifie la configuration de la classe.
     * @access private
     */
    private function CheckConfig() {
        
        if (!version_compare(phpversion(), self::CST_UPL_PHP_VERSION)) {
            $this->Error('Version PHP minimale requise : ' . self::CST_UPL_PHP_VERSION . '.');
        }
        
        if (ini_get('file_uploads') != 1) {
            $this->Error('la configuration du serveur ne vous autorise pas � faire du transfert de fichier. Verifiez la propriete [file_uploads] du fichier [php.ini].');
        }
        
        if (!is_string($this->Extension)) $this->Error('la propriete Extension est mal configur�e.');
        if (!is_string($this->MimeType))  $this->Error('la propriete MimeType est mal configur�e.');
        if (!is_string($this->Filename))  $this->Error('la propriete Filename est mal configur�e.');
        if (!is_bool($this->Required))    $this->Error('la propriete Required est mal configur�e.');
        if (!is_bool($this->SecurityMax)) $this->Error('la propriete SecurityMax est mal configur�e.');
        
        if ($this->WriteMode != self::CST_UPL_WRITE_COPY && $this->WriteMode != self::CST_UPL_WRITE_ERASE && $this->WriteMode != self::CST_UPL_WRITE_IGNORE) {
            $this->Error('la propriete WriteMode est mal configur�e.');
        }
                
        $this->CheckImgPossibility();
        $this->CheckDirUpload();
        
        // verification de la propriete $phpDetectMimeType.
        if (!is_int($this->phpDetectMimeType) || ($this->phpDetectMimeType != self::CST_UPL_HEADER_BROWSER && $this->phpDetectMimeType != self::CST_UPL_HEADER_FILEINFO && $this->phpDetectMimeType != self::CST_UPL_HEADER_MIMETYPE)) {
            $this->Error('la propriete phpDetectMimeType est mal configur�e.');       
        }
        else if ($this->phpDetectMimeType === self::CST_UPL_HEADER_MIMETYPE) {
            $this->loadMimeTypeLib();
        }
        else if ($this->phpDetectMimeType === self::CST_UPL_HEADER_FILEINFO) {
            $this->loadPECLInfoLib();
        }
    }
    
    
    
    /**
     * Verifie les proprietes ImgMaxWidth/ImgMaxHeight
     * @access private
     */
    private function CheckImgPossibility() {
        if (!Empty($this->ImgMaxWidth)  && !is_numeric($this->ImgMaxWidth))  $this->Error('la propriete ImgMaxWidth est mal configur�e.');
        if (!Empty($this->ImgMaxHeight) && !is_numeric($this->ImgMaxHeight)) $this->Error('la propriete ImgMaxHeight est mal configur�e.');
        if (!Empty($this->ImgMinWidth)  && !is_numeric($this->ImgMinWidth))  $this->Error('la propriete ImgMinWidth est mal configur�e.');
        if (!Empty($this->ImgMinHeight) && !is_numeric($this->ImgMinHeight)) $this->Error('la propriete ImgMinHeight est mal configur�e.');
    }
    
    
    
    /** 
     * Essaie de charger la librairie MimeType.
     * 
     * @access  private
     * @return  bool
     */
    private function loadMimeTypeLib() {
        
        if(!extension_loaded(self::CST_UPL_EXT_MIMEMAGIC)) @dl(self::CST_UPL_EXT_MIMEMAGIC . PHP_SHLIB_SUFFIX);
        
        if(!extension_loaded(self::CST_UPL_EXT_MIMEMAGIC)) {
            trigger_error('Impossible de charger la librairie ' . self::CST_UPL_EXT_MIMEMAGIC . '(http://fr3.php.net/manual/fr/ref.mime-magic.php). La verification des entetes de fichiers se fera par le biais des informations retournees par la navigateur.', E_USER_WARNING);
            $this->phpDetectMimeType = self::CST_UPL_HEADER_BROWSER;
            return false;
        }
        
        return true;
    }
    
    
    
    /** 
     * Essaie de charger la librairie PECL.
     * Note : impossible d'activer a la volee cette extension.
     * 
     * @access  private
     * @return  bool
     */
    private function loadPECLInfoLib() {
        
        if(!extension_loaded(self::CST_UPL_EXT_FILEINFO)) {
            trigger_error('Impossible de charger la librairie ' . self::CST_UPL_EXT_FILEINFO . ' (http://fr3.php.net/manual/fr/ref.fileinfo). La verification des entetes de fichiers se fera par le biais des informations retournees par la navigateur.', E_USER_WARNING);
            $this->phpDetectMimeType = self::CST_UPL_HEADER_BROWSER;
            return false;
        }
        
        $this->magicfile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mime_magic' . DIRECTORY_SEPARATOR . 'magic';
        
        if (!is_file($this->magicfile)) {
            trigger_error('Impossible de charger le fichier "magic" n�c�ssaire � la librairie FileInfo. La verification des entetes de fichiers se fera par le biais des informations retournees par la navigateur.', E_USER_WARNING);
            $this->phpDetectMimeType = self::CST_UPL_HEADER_BROWSER;
            return false;
        }
        
        return true;
    }
    
    
    
    /**
     * Affiche les erreurs de configuration et stoppe tout traitement 
     * 
     * @var string $error_msg
     */
    private function Error($error_msg) {
        
        if ($this->TrackError) {
            trigger_error('Erreur [' . get_class($this) . '] : ' . $error_msg, E_USER_ERROR);
            exit;
        }
    }
    
} // End Class
?>
