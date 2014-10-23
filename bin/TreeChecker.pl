#!/usr/bin/perl
#################################################
# INPUT:
# One or several files containing newick forms of ROOTED trees 
# Trees can contain polytomies or multiple occurences of a same taxa 
# (multi-labeled trees).
# Trees can possibly have branch lengths and support values, 
# BUT if one branch length is present -> ALL branches must have a length
#     if one branch support is present -> ALL clades must have a support
#
# OUTPUT:
# Same trees in Newick format restricted to taxa appearing in all input trees.
#
# USAGE: ./restrict2commons.pl inputFile_1 .... inputFile_k > outputFile
#
# SPECIFS INTERNES : 

# When support values are present and several branches are merged to one (due to taxa removal), 
#   the support value of the resulting branch is the max of the merged branches. 
#   The lengths of the merged branches are added to form the length of the resulting branch.
#
# Reads in rooted trees in Newick format 
# If no branch length (resp support values) add fake ones temporarily
# Trees are then encoded as node pointing on one another thanks to a hash table

# V.Berry as of 16th august 2011.
###################################################
# Var globales : tabeau des arbres, puis pour chacun 
# les fils/pere de noeuds, le numnoeud d'un tax, 
# the length of the edge above a node
# and the support value of the branch above a node (ex: bp or aLRT)
my (@arb, @fils, @pere , @tax , @nomtax , @length, @support, %nbocc, @modifrootL, @modifrootS);
##################################
sub verbose {  }# for (@_) {print;} } #  
#########################
sub nextToken {
    $chaine = $_[0];
    if ($chaine =~ /^\w/) {     # [0-9\.eE\+\-]+
    if ($chaine =~ /^(\w[^:()\,]*)\:*([0-9\.eE\+\-]*)(.*)/) { # taxon
        verbose "tax-$1- with lgr --$2--\n";
        return ($1,$2,$3);}
    else {die "-$chaine-\n regexp pas ok\n"}
    }
    elsif ($chaine =~ /^\,(.*)/) { # "," -> passe au frere
    #verbose "-,-"; 
    return (",",$1);
    }
    elsif ($chaine =~ /^\((.*)/) { # "(" -> new child
    #verbose "-(-"; 
    return ("(",$1);
    }
    elsif ($chaine =~ /^\)([0-9\.eE\-\+]*):*([0-9\.eE\-\+]*)(.*)/) { # ending clade
    #verbose "-)-";
    return (")",$1,$2,$3);}
    else {die "UNEXPECTED TOKEN: $chaine\n";}
}
########################
sub Pointerise {
#   @arb, @fils , @pere , @tax;
    my %taxThisTree = ();
    $numTree = $_[0]; # num d'arbre
    $chaine = $arb[$numTree];
    $modifrootL[$numTree] = 0;
    $modifrootS[$numTree] = 0;
    if($chaine =~ /\)$/) {
        $chaine = $chaine . "100:100"; # ajoute un support et une lgr fictive au dessus racine
        $modifrootL[$numTree] = 1;
        $modifrootS[$numTree] = 1;
    } elsif ($chaine =~ /\):[^\)+]$/) {
        $chaine =~ s/\):([^\)]+)$/)100:$1/; # ajoute un support et une lgr fictive au dessus racine
        $modifrootS[$numTree] = 1;
    } elsif ($chaine =~ /\)[^\):]+$/){
        $chaine .= ":100";
        $modifrootL[$numTree] = 1;
    }
    verbose "j'analyse le $numTree ieme arbre : $chaine\n";
    $courant = 0; $nextnd = 1; # nd 0 = racine
    $pere[$courant]=-1; # encodes the root
    while ($chaine) {
        @l = &nextToken($chaine);
        #print "--\n";
        #for (@l) {print; print"\n";}
        #print "--\n";
        $chaine = pop @l;
        #print "chaine : ".$chaine."\n";
        if ($l[0] =~ /^\w/) { # taxon
            #print "taxon : ".$l[0]."\n\n";
            $tax[$numTree]{$l[0]}.="$courant "; # taxon -> num de son noeud         
            $nomtax[$numTree][$courant] = $l[0] ; # numnoeud -> nom taxon
            $length[$numTree][$courant] = $l[1]; # lgth of the branch above this taxa
            $taxThisTree{$l[0]}=1;  # Soucis si Mul-trees: taxa presents plusieurs fois faussent les comptes $nbocc{$l[0]}++; # nb occ de ce tax ds les arbres lus
        }
        elsif ($l[0] eq "(") {
            $fils[$numTree]{$courant} = $nextnd;
            $pere[$numTree]{$nextnd} = $courant;
            $courant = $nextnd; $nextnd++;
        }
        elsif ($l[0] eq ",") { # neo frere
            $courant = $pere[$numTree]{$courant};
            $fils[$numTree]{$courant} .= " $nextnd";
            $pere[$numTree]{$nextnd} = $courant;
            $courant = $nextnd; $nextnd++;
        }
        else {  # ")" : revient au pere
            $courant = $pere[$numTree]{$courant}; # go back to the father of this node
            $support[$numTree][$courant] = $l[1]; # lgth of the branch from the father to this node
            $length[$numTree][$courant] = $l[2]; # lgth of the branch above this node
            # ATTENTION : soucis quand c'est la racine - je patche provisoirement en adaptant ToNewick pour la racine !!!
        }
#   verbose "reste -$chaine-\n";
    }
    # Add taxa of this tree to list of seen taxa (each taxa here is added once: ok for MUL-trees).
    foreach (keys %taxThisTree) {$nbocc{$_}++;}
}
################################
sub ToNewick {
    my $ch;
    my $n = $_[0]; my $tree= $_[1]; # $n = noeud auquel on commence
    if (exists $fils[$tree]{$n}) {
    #    if (defined $tax[$tree]{$n}) {
        my @sons = split / /, $fils[$tree]{$n};
        #verbose =:"ses fils sont $fils[$tree]{$n}\n";
            $ch = "(";
        my $lastson = pop @sons;
        my $s;
        foreach $s (@sons) { $ch .= ToNewick ($s,$tree).",";}
        $ch.= ToNewick($lastson,$tree);
        if ($pere[$n] == -1) {
            if($modifrootL[$tree] && $modifrootS[$tree]) {
                return $ch.")";
            } elsif($modifrootS[$tree] && !$modifrootL[$tree]) {
                return $ch."):".$length[$tree][$n];
            } elsif($modifrootL[$tree] && !$modifrootS[$tree]) {
                return $ch.")".$support[$tree][$n];
            } else {
                return $ch.")".$support[$tree][$n].":".$length[$tree][$n];
            }
        } # root node -> no length nor support above
        else {
            if($length[$tree][$n] ne "") {
                return $ch.")".$support[$tree][$n].":".$length[$tree][$n];
            } else {
                return $ch.")".$support[$tree][$n];
            }
        }
    }
    else {#verbose "tax $nomtax[$tree][$n] ";
        if($length[$tree][$n] ne "") {
            return $nomtax[$tree][$n].":".$length[$tree][$n];
        } else {
            return $nomtax[$tree][$n];
        }
    }
}
################################
sub PtrToScreen {
    my $ch;
    my $n = $_[0]; my $tree= $_[1]; # $n = noeud auquel on commence
    if (exists $fils[$tree]{$n}) {
    #    if (defined $tax[$tree]{$n}) {
        verbose "node $n has child node(s) = $fils[$tree]{$n}\n";
        my @sons = split / /, $fils[$tree]{$n};
        #print =:"ses fils sont $fils[$tree]{$n}\n";
    #   $lastson = $sons[$#sons]; pop @sons;
        my $lastson = pop @sons;
        verbose "last child is $lastson\n";
        foreach $s (@sons) { PtrToScreen ($s,$tree);}
        PtrToScreen($lastson,$tree);
    }
    else {
      verbose "$n is taxon $nomtax[$tree][$n]\n";}
}
##################################################
sub checkValue {
    my $toCheck = $_[0];
    $toCheck =~ s/^[\+\-]//; # delete + or -
    if($toCheck =~ s/^\d*\.?\d+//) { # then we have 0-9 .? 0-9
        if($toCheck =~ /^$/) { # end of string = ok
            return "ok";
        } elsif ($toCheck =~ s/^[eE]//) { # exponent value
            if($toCheck =~ s/^[\+\-]*\d+//) { 
                return "ok";
            } else { # exponent then other strange stuff
                return "no|wrong or missing value after 'e'";
            }
        } else { # strange stuff after decimals
            return "no|wrong value";
        }
    } else { # not even a digit
        return "no|not a number";
    }
}
##################################################
sub CheckAllSupport {
    my $tree = $_[0];
    my $support = 0;
    while ($tree =~ /\)([^\)\,\:]+)[\)\,\:]/g) { # check support values format
        my $c = $1;
        my $res = checkValue $c;
        if($res =~ s/no\|//) {
            return "$c, $res";
        }
    }

    if ($tree =~ /\)[0-9\.eE\+\-]+[\)\,:]/) {
        $support = 1;
    }

    if(($support) && ($tree =~ /\)[:\)\,]/)) { # at least one support missing
        return "error";
    }
    return "ok";
}
##################################################
sub CheckAllLgth {
    my $tree = $_[0];
    my $length = 0;
    $tree =~ s/\)[0-9\.eE\+\-]+([\)\,:])/)$1/;

    # while($tree =~ s/\)([0-9\.eE\+\-]+)[\)\,:]//) {
    while ($tree =~ m/:([^\)\,]+)[\)\,]/g) { # check edge values format
        my $c = $1;
        my $res = checkValue $c;
        if($res =~ s/no\|//) {
            return "$c, $res";
        }
    }

    if ($tree =~ /:[0-9\.eE\+\-]+[\)\,]/) {
        $length = 1;
    }

    if(($length) && ($tree =~ /[\)\(\,][^:]*[\)\,]/)) { # at least one length missing
        return "error";
    }
    return "ok";
}
##################################################
sub unifyTaxa {
    my %seen = ();
    my @r = ();
    my $nb = $_[0];
    for my $k (0 .. scalar @{$nomtax[$nb]}) {
        $val = $nomtax[$nb][$k];
        if($val ne "") {
            if ($seen{$val}) {
                $seen{$val}++;
                $nomtax[$nb][$k] .= "_$seen{$val}";
            } else {
                $seen{$val} = 1;
            }
        }
    }
}
##################################################
sub formatTaxa {
    my %seen = ();
    my @r = ();
    my $nb = $_[0];
    for my $k (0 .. scalar @{$nomtax[$nb]}) {
        $val = $nomtax[$nb][$k];
        if($val ne "") {
            $val =~ s/\.+/_/g;
            $nomtax[$nb][$k] = $val;
        }
    }
}
########################################################################################
########################################################################################
###############                                         ################################
###############              MAIN                       ################################
###############                                         ################################
########################################################################################
########################################################################################
# params = names of files containing trees
die "\n\tUsage: $0 inputFile_1 .... inputFile_k > outputFile\n\n" if ($#ARGV<0);

#$#nomtax = 4999; # array initialized with 5000 cells; # needed ???
#for ($i=0;$i<4999;$i++) {$tab = $nomtax[$i]; $#$tab = 4999;}

my $fic;
foreach $fic (@ARGV) {
    open F,$fic or die "Cannot open $fic\n";
    my $arbre;
    while (<F>) {$arbre .= $_ ;} 
    close F;
    $arbre =~ s/(\w+)\s+(\w+)/$1_$2/g;

    # Remove spaces
    $arbre =~ s/\s+//g;
    $arbre =~ s/\n//g; chop $arbre; # enleve le ; final sinon arb fantome

    my @AllTrees = split /;/, $arbre;
    verbose "This file contains ", 1+$#AllTrees," trees to process\n";
    $errors = "";
    $info = "";
    for ($a=0;$a<=$#AllTrees;$a++) {
        
        # Check unexpected characters
        $arbre_origin = $AllTrees[$a];
        $AllTrees[$a] =~ s/[^-+\w\.;:\,\(\)]//g; # \w prend l'underscore


        if($arbre_origin ne $AllTrees[$a]) {
            $info .= "Tree #".($a+1).": unexpected (%,#,etc.) characters or spaces have been removed from tree.\n";
        }
        
        # Check if correct numbers of opening and closing brackets
        my $tmp = $AllTrees[$a];
        $openNb = ($tmp =~ tr/(//); $closeNb = ($tmp =~ tr/)//);
        $errors.= "Tree #".($a+1).": unmatching numbers of brackets in Newick format of tree.\n" unless ($openNb == $closeNb);
        
        # Check bootstraps
        $test = "ok";
        if ($AllTrees[$a]=~ /\)\d/) { $test = CheckAllSupport $AllTrees[$a]; }
        if($test ne "ok") {
            if($test eq "error") {
                $errors.= "Tree #".($a+1).": the tree must contain all or no branch support.\n";
            }
            else {
                $errors.= "Tree #".($a+1).": the tree has bad branch support format: $test.\n";
            }
        }

        # Check branch lengths
        $test = "ok";
        if ($AllTrees[$a]=~ /\:/) { $test = CheckAllLgth $AllTrees[$a]; }
        if($test ne "ok") {
            if($test eq "error") {
                $errors.= "Tree #".($a+1).": the tree must contain all or no branch length value.\n";
            }
            else {
                $errors.= "Tree #".($a+1).": the tree has bad branch length format: $test.\n";
            }
        }
        
        push @arb, $AllTrees[$a];
        
        if($errors ne "") {
            #print "||";
            print $errors;
            print "Please fix these errors before proceeding.\n";
            exit;
        }
        # Pointerize
        &Pointerise($a);
        
        # Taxa unifying
        $origin = ToNewick(0,$a);
        unifyTaxa($a);
        $final = ToNewick(0,$a);
        if($origin ne $final) {
            $info .= "Tree #".($a+1).": some taxa have been renamed to keep them unique.\n";
        }
        $origin = ToNewick(0,$a);
        formatTaxa($a);
        $final = ToNewick(0,$a);
        if($origin ne $final) {
            $info .= "Tree #".($a+1).": '.' in taxa names have been replaced by '_'..\n";
        }
    }
    print "||";
    print $info;
}

# Transforme chaque arbre en dico de noeuds
#$numarb = 0;
#for $a (@arb) {
#         &Pointerise($numarb);
#        for $c (sort keys %{ $tax[$numarb] }) {verbose "$c => $tax[$numarb]{$c}\n";}
#         verbose "taxons de l'arbre $numarb: ";
#         $mestax = $nomtax[$numarb];
#         foreach (@$mestax) {verbose "-$_-" if (!($_ eq "")) ;}
#         verbose "\n";
#         $numarb++;
#    }
#}

# Imprime les arbres au format Newick
print "||";
for ($a=0;$a<=$#arb;$a++) {
    #PtrToScreen(0,$a),";\n";
    print ToNewick(0,$a),";\n";
}
# ENLEVE LES TAXONS NON PRESENTS DANS TOUS LES ARBRES
#verbose "taxons : ";
#$nbarbres = 1+$#arb;
#foreach $t (sort keys %nbocc)  {  #verbose "$t-> $nbocc{$t} fois\n";
#    if ($nbocc{$t}<$nbarbres) { # taxon to be removed
#       for ($a=0;$a<=$#arb;$a++) {
#         if (exists $tax[$a]{$t}) { # this taxon appears in this tree
#           rmvTaxFromTree ($t,$a);
#         }
#       }
#    }
#}

#verbose "\n Printing resulting trees :\n";
# Imprime les arbres resultant au format Newick a l'ecran
#open OUT,">u.out" or die "impossible de creer le fich de sortie\n";
#for ($a=0;$a<=$#arb;$a++) {
#    #verbose "imprime arbre numero $a\n";
#    $ch = ToNewick(0,$a);
#    verbose "\n\n\t$ch\n\n";
#    $ch =~ s/\)[0-9.eE\-+]+:[0-9.eE\-+]+$/)/; # support+length above root is for sure a fake: rmv it.#
#   # rmvs fake lengths and/or supports (!!! merged branches have added their fake values, hence no longer equal to 100 in this case
#    if ($FakeLgr[$a] == 1) {$ch =~s/:\d+//g;} # rmv fake lgrs if tree was without branch lengths
#   if ($FakeSupport[$a] == 1) {$ch =~s/\)\d+/)/g; verbose "rmv fake supports\n";} # rmv fake support if tree was without
#    print $ch,";\n";
#}

# Temporary, just to check how values add to one another correctly (thanks Perl!)
# $v1 = "1.23456e-2";
# $v2="3.5";
# $v3 = $v1+$v2;
# print "$v1+$v2=$v3\n";
