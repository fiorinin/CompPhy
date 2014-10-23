<?php
    $nwk = '';
    $treeToSwap = null;
    
    if($side == "left" && $projet->getMaing() != 0) 
        $treeToSwap = Arbre::getA ($db, $projet->getMaing());
    elseif($side == "right" && $projet->getMaind() != 0) 
        $treeToSwap = Arbre::getA ($db, $projet->getMaind());
    
    if($treeToSwap) {
        $nwk = $treeToSwap->getNewick();

        $infile = $res . 'infile_swap.nwk';
        $outfile = $res . 'outfile_swap.nwk';

        file_put_contents($infile,$nwk);
        //echo "$res ";
        //echo "perl ".BINPATH."compphy/swapLeaves.pl $infile $rleaf $lleaf >$outfile";exit;
        exec("cd ".$res."\nperl ".BINPATH."swapLeaves.pl $infile $rleaf $lleaf >$outfile");
        if(file_get_contents($outfile) != '') {            
            // On segmente le fichier sorti en arbres
            $swappednwk = file_get_contents($outfile);
            $treeToSwap->setNewick($swappednwk);
            $treeToSwap->update();
            $treeToSwap->create($res);
        }
        else {
            $errors .= "There was an error in leaf swapping.";
        }
        $restmp = $res."rest.tmp";
        $fakenwk = $res."fakenwk.tmp";
        exec("rm $infile $outfile $restmp $fakenwk");
    }
?>
