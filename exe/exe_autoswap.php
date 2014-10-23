<?php

$lefttree = null;
$righttree = null;

if ($projet->getMaing() != 0)
    $lefttree = Arbre::getA($db, $projet->getMaing());
if ($projet->getMaind() != 0)
    $righttree = Arbre::getA($db, $projet->getMaind());

if ($righttree == $lefttree) {
    $errors .= "You are trying to compare the same tree.";
} else {

    if ($righttree && $lefttree) {
        $infile1 = $res . 'infile1_swap.nwk';
        $infile2 = $res . 'infile2_swap.nwk';
        $outfile = $res . 'outfile_swap.nwk';

        file_put_contents($infile1, $lefttree->getNewick());
        file_put_contents($infile2, $righttree->getNewick());
        exec("cd " . $res . "\nperl " . BINPATH . "autoSwapLeaves.pl $infile1 $infile2 >$outfile");

        if (file_get_contents($outfile) != '') {
            // On segmente le fichier sorti en arbres
            $swappednwk = file_get_contents($outfile);
            $righttree->setNewick($swappednwk);
            $righttree->update();
            $righttree->create($res);
        } else {
            $errors = "There was an error in leaf auto swapping.";
        }
        $restmp = $res . "rest.tmp";
        $fakenwk = $res . "fakenwk.tmp";
        if (is_file($infile1))
            unlink($infile1);
        if (is_file($infile2))
            unlink($infile2);
        if (is_file($outfile))
            unlink($outfile);
        if (is_file($restmp))
            unlink($restmp);
        if (is_file($fakenwk))
            unlink($fakenwk);
    }
}
?>
