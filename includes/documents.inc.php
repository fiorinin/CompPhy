<div class="row">
    <?php
    $id = 0;
    if (isset($_GET['id']))
        $id = intval($_GET['id']);

    $projet = Projet::getP($db, $id);
    $result_dir = $res = $projet->getRepertoire();

    if ($projet->getPublic() != 1 && (!isset($membre) || !$projet->canAccess($membre->getId())))
        Navigate::redirectMessage("login", "You do not have access to this project. Maybe your account has been disconnected.", 2);

    echo "<h4 class='subheader'>Project: " . $projet->getTitre() . "</h4><hr>";
    include (HEREPATH . 'includes/project_menu.php');
    
    if (isset($membre) && $projet->canAccess($membre->getId())) {


        $Upload = new Uploadf();

        if (!empty($_POST['MAX_FILE_SIZE'])) {
            $Upload->DirUpload = EXECPATH . $result_dir . "/uploads";
            $doc = new Document(array("db" => $db));
            $filename = $doc->createFileName();
            $Upload->Filename = $filename;
            $Upload->Permission = 0644;
            $Upload->Extension = '.gif;.jpg;.jpeg;.bmp;.png;.pdf;.doc;.docx;.xls;.xlsx;.ppt;.pptx;.tar.gz;.zip;.tar;.rar;.tre;.phy;.nwk;.txt;.aln;.dendro;.embl;.fasta;.pages;.keynote;.numbers;.rtf;.nw';
            $Upload->MimeType = 'image/gif;image/pjpeg;image/jpeg;image/png;image/bmp;image/x-png;application/pdf;application/download;application/x-gzip;application/zip;application/x-rar-compressed;application/msword;application/vnd.openxmlformats-officedocument.wordprocessingml.document;application/vnd.ms-excel;application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;application/vnd.ms-powerpoint;application/vnd.openxmlformats-officedocument.presentationml.presentation;application/treeview;application/octet-stream;text/plain;application/txt;browser/internal;text/anytext;widetext/plain;widetext/paragraph;chemical/x-embl-dl-nucleotide;chemical/x-pdb;chemical/seq-aa-fasta;chemical/seq-na-fasta;application/x-iwork-keynote-sffkeynote;application/x-iwork-pages-sffpages;application/x-iwork-numbers-sffnumbers;application/rtf;application/x-rtf;text/rtf;text/richtext;application/msword;application/doc;application/x-soffice';
            if ($Upload->Execute() === false) {
                // Récupère les erreurs sur le premier champ de formulaire
                $erreurs = $Upload->GetError(1);
                echo "<table class='warning'><tr><td class='warning'>";

                // Parcours du tableau, ajustement des traitements
                foreach ($erreurs as $code_erreur => $lib_erreur) {
                    echo $lib_erreur . "" . $Upload->_type;
                }
                echo "</td></tr></table>";
            } else {
                $fichier = $Upload->GetSummary(1);
                $titre = $fichier['nom_originel'];
                $adresse = $fichier['nom'];
                $sender = $membre->getId();
                $proj_id = $projet->getId();
                if ($fichier['nom'] != "") {
                    $doc->setTitre($titre);
                    $doc->setAdresse($adresse);
                    $doc->setSender($sender);
                    $doc->setProj_id($proj_id);
                    $doc->add();
                    echo "<table class='valid'><tr><td class='valid'>File uploaded.</td></tr></table>";
                }
            }
        }

        $Upload->InitForm();
        $Upload->MaxFilesize = 10485760;
        ?>
        <h5 class="subheader">Upload a document to this project <small>(max. 10Mo)</small></h5>
        <form method="post" action="?p=documents&id=<?= $projet->getId(); ?>" enctype="multipart/form-data">
            <?php
            print $Upload->Field[0];
            print $Upload->Field[1];
            ?>
            <button type="submit" class="small button">Send the document</button>
        </form>
        <script>
            $(document).ready(function() {
                $(".deld,.editd").click(function() {
                    $("#deldconf").attr('data-project',$(this).attr('doc-id'));
                    $("#editconf").attr('data-project',$(this).attr('doc-id'));
                });
                $("#docediter ,#docremover").click(function() {
                    var id = <?= $projet->getId(); ?>;
                    var idd = $(this).parent().parent().attr("data-project");
                    var r;
                    if($(this).attr("id") === "docediter")
                        r = $("#editconf").find("input").val();
                    else r = 1;
                    $.ajax({
                        type: "get",
                        url: "ajax/ajax_docHandler.php?id="+id+"&idd="+idd+"&r="+r,
                        dataType : "xml",
                        complete:function(){
                            if(r === '1')
                                $("#"+idd).parent().parent().hide();
                            else {
                                $("#d_"+idd).text(r);
                            }
                            $('#deldconf').foundation('reveal', 'close');
                        }
                    });
                });
            });
        </script>
        <?
        }
        if ($projet->getPublic() == 1 || (isset($membre) && $projet->canAccess($membre->getId()))) {
        ?>
        <h5 class="subheader">Uploaded documents</h5>
        <?
        $doclist = Document::getList($db, $projet->getId());
        if (count($doclist) == 0) {
            echo "There is no uploaded document for this projet yet.";
        } else {
            ?>
            <table width="100%">
                <thead>
                    <tr>
                        <th colspan="2">User</th>
                        <th>Filename</th>
                        <th>Date</th>
                        <th>Actions</th>
                    <tr>
                </thead>
                <?
                foreach ($doclist as $key => $doc) {
                    $author = Utilisateur::getM($db, $doc->getSender());
                    ?>
                    <tr>
                        <td align="center" width="60">
            <? if ($author->getAvatar() != '') { ?><img src="avatars/<?= $author->getAvatar(); ?>" alt="Profile pic"><? } ?>
                        </td>
                        <td align="center">
            <? echo $author->getPrenom() . ' ' . $author->getNom(); ?>
                        </td>
                        <td align="center">
                            <a href="?p=getresult&id=<?= $projet->getId(); ?>&d=1&f=<?= $doc->getId(); ?>" id="d_<?= $doc->getId(); ?>"><? echo $doc->getTitre(); ?></a>
                        </td>
                        <td align="center">
            <? echo date('d-m-Y H:i', strtotime($doc->getTimestamp())); ?>
                        </td>
                        <td align="center">
            <? if (isset($membre) && ($projet->isLead($membre->getId()) || $doc->isSender($membre->getId()))) { ?>
                                <a href="?p=getresult&id=<?= $projet->getId(); ?>&d=1&f=<?= $doc->getId(); ?>" class="small button">Download</a>
                                <button class="small button editd" doc-id="<?= $doc->getId(); ?>" data-reveal-id="editconf">Rename</button>
                                <button class="alert small button deld" doc-id="<?= $doc->getId(); ?>" data-reveal-id="deldconf">Remove</button>
            <? } ?>
                        </td>
                    </tr>
                    <? }
                ?>
            </table>

        </div>
        <div id="editconf" class="reveal-modal small text-center" data-project="">
            <div class="small-12">
                <form>
                    <fieldset>
                        <label>Rename the document</label>
                        <input type="text" />
                    </fieldset>
                </form>
                <button class="small button" id="docediter">Rename</button>
            </div>
        </div>

        <div id="deldconf" class="reveal-modal small text-center" data-project="">
                <div class="small-8 columns">
                    Are you sure you want to delete this document ?
                </div>
                <div class="small-4 columns">
                    <button class="alert small button" id="docremover">Delete</button>
                </div>
        </div>
        <?
    }
}
?>
</div>
