<?php
    $nwk = '';
    
    foreach($trees as $key => $value) {
        $nwk .= $value->getNewick();
    }
    $nwkfile = $res . 'reroot.nwk';
    $outfile = $res . 'reroot.out';
    $newfile = $res . 'newrerooted.nwk';
    $scriptout = $res . 'reroot_stdout';
    $err_nwk = $res . 'err_nwk';
    
    file_put_contents($nwkfile,$nwk);
    file_put_contents($outfile,$_POST['outgroups']);
    
    // Getting errors before rooting
    exec("cd ".$res."\nperl ".BINPATH."detectMissingLgth.pl ". $nwkfile ." >err_nwk");
    if(!preg_match("#OK#",file_get_contents($err_nwk))) {
        $errors .= file_get_contents($err_nwk);
    }
    else {
        //echo "perl ".BINPATH."compphy/addFakeLengthAndSupport.pl ". $nwkfile ." >tempfake";
        //echo "bppreroot input.list.file=tempfake outgroups.file=reroot.out output.trees.file=tempres print.option=true tryAgain.option=true >reroot_stdout";
        //echo "perl ".BINPATH."compphy/rmvFakeLengthAndSupport.pl tempres >". $newfile."";
        exec("cd ".$res."\nperl ".BINPATH."addFakeLengthAndSupport.pl S ". $nwkfile ." >tempfake");
        exec("cd ".$res."\nbppreroot input.list.file=tempfake outgroups.file=reroot.out output.trees.file=tempres print.option=true tryAgain.option=true >reroot_stdout");
        exec("cd ".$res."\nperl ".BINPATH."rmvFakeLengthAndSupport.pl S tempres >". $newfile);

        // Getting errors after reroot
        $err = file_get_contents($scriptout);
        if(preg_match("#.*Could\snot\sexecute([\s\n\(\)\-\_a-zA-Z0-9\"\'\:\,]+)#", $err, $m)) {
            $errors .= "Could not execute".$m[1].")";
        }
        else {
            // On segmente le fichier sorti en arbres
            $back = file_get_contents($newfile);
            $back_split = substr(trim($back),0,-1);
            $backtab = explode(';',$back_split);

            // On crée les arbres correspondants
            foreach($backtab as $key => $value) {
                if(isset($_POST['replace']) && $_POST['replace'] == "1") {
                    $arbre = Arbre::getA($db, $trees[$key]->getId());
                    $arbre->setNewick($value.';');
                    $arbre->update();
                    $arbre->create($res);
                }
                else {
                    $donnees = array('db' => $db,
                                     'nom' => $trees[$key]->getNom().'_rerooted',
                                     'typet' => $trees[$key]->getType(),
                                     'newick' => $value.';',
                                     'script' => $trees[$key]->getScript(),
                                     'annotation' => $trees[$key]->getAnnotation(),
                                     'image' => '',
                                     'miniature' => '',
                                     'actif' => 1,
                                     'proj_id' => $projet->getId());
                    $arbre = new Arbre($donnees);
                    $arbre->add();
                    $arbre->create($res, true);
                }
            }
        }
    }
    $tempres = $res.'tempres';
    $tempfake = $res.'tempfake';
    $fake = $res.'fake.tmp';

    if (file_exists ($nwkfile)  ) unlink ($nwkfile);
    if (file_exists ($outfile)  ) unlink ($outfile);
    if (file_exists ($newfile)  ) unlink ($newfile);
    if (file_exists ($scriptout)) unlink ($scriptout);
    if (file_exists ($tempres)  ) unlink ($tempres);
    if (file_exists ($tempfake) ) unlink ($tempfake);
    if (file_exists ($fake)     ) unlink ($fake);
    if (file_exists ($err_nwk)  ) unlink ($err_nwk);
//    exec("rm $nwkfile $outfile $newfile $scriptout $tempres $tempfake $fake $err_nwk");
?>
