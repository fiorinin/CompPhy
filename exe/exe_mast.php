<?php

$nwk = '';

foreach ($trees as $key => $value) {
    $nwk .= $value->getNewick();
}

if (sizeof($trees) == 1) {
    $errors .= "You are trying to compare the same tree.";
} else {
    
    $inputfile = $res . 'mast_input.nwk';
    $tempfake = $res . 'tempfake';
    $fictmp = $res . 'fic.tmp';
    $restmp = $res . 'rest.tmp';
    $faketmp = $res . 'fake.tmp';
    $mastcmd = $res . 'mast.cmd';
    $mastpaup = $res . 'mastPaup.out';
    $mastTree = $res . 'mastTree.nwk';

    // Suppression longueurs de branches et bootstraps pour la moulinette restrict2common
    /* $nwk = trim(preg_replace('/\)\d*\:/', '):', $nwk));
      $nwk = trim(preg_replace('/\:\d*\.*\d*\)/', ')', $nwk));
      $nwk = trim(preg_replace('/\:\d*\.*\d*\,/', ',', $nwk)); */

    file_put_contents($inputfile, $nwk);
    //exec("cd ".$res."\nperl ".BINPATH."compphy/addFakeLengthAndSupport.pl ". $inputfile ." >tempfake");
    //echo "perl ".BINPATH."compphy/computeMAST-paup.pl $inputfile";exit;
    exec("cd " . $res . "\nperl " . BINPATH . "computeMAST-paup.pl $inputfile");

    if (file_get_contents($mastTree) != '') {
        $treenwk = file_get_contents($mastTree);
        $tree = new Arbre(array("newick" => $treenwk));
        $masttaxalist = $tree->getOneTaxaList();
        $nbboucle = 0;
        foreach ($trees as $key => $value) {
            $addscript = "\n";
            $addann = "\n";
            $dup = $value;
            $taxalist = $value->getOneTaxaList();
            foreach ($taxalist as $taxon => $nb) {
                if (!isset($masttaxalist[$taxon]) || $masttaxalist[$taxon] != 1) {
                    if (!preg_match("/query\_annotation \-q \{result \#\# mast\} \-hi \{\-o \{sd1 lfg sfg\} \-c grey\}/", $addscript))
                        $addscript .= "query_annotation -q {result ## mast} -hi {-o {sd1 lfg sfg} -c grey}\n";
                    $addann .= $taxon . " result {mast}\n";
                }
            }
            $oldScript = $dup->getScript();
            if ($oldScript == "" || !preg_match("/^t/i", $oldScript))
                $oldScript = "t -interleaf 20";
            $dup->setNom($dup->getNom() . "_M");
            $dup->setScript($oldScript . $addscript);
            $oldann = $dup->getAnnotation();
            $oldann = preg_replace("/\w+ result \{mast\}\n/", "", $oldann);
            $dup->setAnnotation($oldann . $addann);
            $dup->setImage('');
            $dup->setMiniature('');
            $dup->add();
            $dup->create($res, true);
            if ($nbboucle == 0)
                $projet->setMaing($dup->getId());
            elseif ($nbboucle == 1)
                $projet->setMaind($dup->getId());
            $projet->update();
            $nbboucle++;
        }
    }
    else {
        $errors .= "There was an error in mast computing: maybe there are too few taxa in common between submitted trees.";
    }

    exec("rm $inputfile $tempfake $fictmp $faketmp $mastcmd $mastpaup $mastTree $restmp");
}
?>
