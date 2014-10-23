#!/usr/bin/perl

####################################################
# INPUT : - 2 newick trees (first will not change)
#
# OUTPUT : - the new newick tree with swapped leaves (the second one)
#
# USAGE : ./autoSwapLeaves.pl fileContainingNewickTree1 fileContainingNewickTree2 >newfile.nwk
####################################################
# Var globales : tabeau des arbres, puis pour chacun
# les fils/pere de noeuds, le numnoeud d'un tax,
# the length of the edge above a node
# and the support value of the branch above a node (ex: bp or aLRT)
my (@arb, @fils, @pere , @tax , @nomtax , @length, @support, %nbocc, %nbAncestors, @fromBinarise, @parentsHash);
##################################
sub verbose { }#for (@_) {print;} }
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
    return $nextnd;
}
################################
sub Binarise {
	$startnode = shift;
	$numToBinarise = shift;
	$lastnd = shift;
	#verbose "\nJe binarise a partir de $startnode\n";
	push @ancestors, $startnode;
	
	local @filsToCheck = ();
	@filsCeNoeud = ();
	if ($fils[$numToBinarise]{$startnode}) {
		@filsCeNoeud = split / / , $fils[$numToBinarise]{$startnode};
		if($#filsCeNoeud > 1) {
			$lastnd++;
			# verbose "\n    =>je split $startnode : ";
			$extnode = shift @filsCeNoeud;
			# verbose "$extnode est mis en externe\n\n";
			$newnode = join (" ", @filsCeNoeud);
			# Mise a jour des fils
			$fils[$numToBinarise]{$startnode} = "$extnode $lastnd";
			$fils[$numToBinarise]{$lastnd} = $newnode;
			# Mise a jour des peres
			$pere[$numToBinarise]{$lastnd} = $startnode;
			foreach (@filsCeNoeud) {
				$pere[$numToBinarise]{$_} = $lastnd;
			}
			if (!$fromBinarise[$numToBinarise]) { $fromBinarise[$numToBinarise] = "$lastnd"; }
			else { $fromBinarise[$numToBinarise] .= " $lastnd"; }
			push @filsToCheck, $extnode; push @filsToCheck, $lastnd;
		}
		# Préparation des fils à checker
		if($#filsToCheck != 1 && $fils[$numToBinarise]{$startnode} =~ /(\d+)\s(\d+)/i ) {
			verbose "\n    =>Pas de split, je passe aux fils.\n";
			push @filsToCheck, $1; push @filsToCheck, $2;
		}
		foreach $f (@filsToCheck) {
			foreach(@ancestors) {
				$parentsHash{$f}{$_}++;
			}
			verbose "Je vais lancer sur $f";
			&Binarise($f, $numToBinarise, $lastnd);
		}
	}
	else { 
		foreach(@ancestors) {
			$parentsHash{$f}{$_}++ if($f != $_) ;
		}
	}
	pop @ancestors;
}
################################
sub Linearise {
	my $tree = $arb[shift];
	$tree =~ s/(\)[\d\.eE\-\+]+)|(\:[\d\.eE\-\+]+)|([\(\):,;.]+)/#/g;
	@pureTree = split /#+/, $tree;
}
################################
sub swapAuto {
	my $treenb = shift;
	my $node = shift;
	
	if($fils[$treenb]{$node}) {
		my @filstmp = split / /, $fils[$treenb]{$node};
		foreach (@filstmp) {
			&swapAuto($treenb, $_);
		}
		#verbose "Execution du swap sur $node\n";
		# On swap en post-ordre
		$nbCroisSsSwap = 0;
		$nbFdRencontres = 0;
		foreach (@pureTree) {
			$nbnode = int $tax[$treenb]{$_};
			if($nbnode) {
				if($parentsHash{$nbnode}{$filstmp[0]} || $nbnode == $filstmp[0]) { $nbCroisSsSwap += $nbFdRencontres; }
				elsif ($parentsHash{$nbnode}{$filstmp[1]} || $nbnode == $filstmp[1]) { $nbFdRencontres++; }
			}
		}
		#verbose "\n";
		$nbCroisSiSwap = 0;
		$nbFgRencontres = 0;
		foreach (@pureTree) {
			$nbnode = int $tax[$treenb]{$_};
			if($nbnode) {
				if($parentsHash{$nbnode}{$filstmp[1]} || $nbnode == $filstmp[1]) { $nbCroisSiSwap += $nbFgRencontres; }
				elsif ($parentsHash{$nbnode}{$filstmp[0]} || $nbnode == $filstmp[0]) { $nbFgRencontres++; }
			}
		}
		if ($nbCroisSiSwap < $nbCroisSsSwap) { 
			# verbose "Je swap les fils de $node\n\n";
			&swap($node,$treenb); 
		}
	}
}
################################
sub swap {
    local ($parentNode, $tree) = ($_[0], $_[1]);
    local $filsnodes = $fils[$tree]{$parentNode};
    $filsnodes =~ s/(\d+)\s(\d+)/$2 $1/;
    $fils[$tree]{$parentNode} = $filsnodes;
}
################################
sub Unbinarise {
	local $numtree = shift;
	local @res = split / / , $fromBinarise[$numtree];
	@res = reverse @res;
	foreach(@res) {
		# verbose "Debinarisons $_ ... \n";
		my $parent = $pere[$numtree]{$_};
		# verbose "Le nouveau pere est donc $parent ";
		my $content = $fils[$numtree]{$_};
		# verbose "et les enfants suivants s'ajoutent : $content \n";
        # print " $fils[$numtree]{$parent}\n";
		$fils[$numtree]{$parent} =~ s/($_)/$content/;
        # print " $fils[$numtree]{$parent}\n";
		my @tmpchilds = split / /, $content;
		foreach (@tmpchilds) {
			$pere[$numtree]{$_} = $parent;
		}
		delete $pere[$numtree]{$_};
		delete $fils[$numtree]{$_};
	}
}
################################
sub max {
	$v1 = $_[0];
	$v2 = $_[1];
	return $v2 <=> $v1 ;
}
##################################
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
#################################
############# MAIN ##############
#################################
die "\n\tUsage: ./autoSwapLeaves.pl inputFile1 inputFile2 >outputFile\n\n" if ($#ARGV<0);

my ($fic, $fic2);
$fic1 = shift @ARGV;
$fic2 = shift @ARGV;

my $dirvar = "/data/http/www/binaries/compphy/";
#my $dirvar = "./";

`${dirvar}addFakeLengthAndSupport.pl LS $fic2 > rest.tmp`;

for ($i = 1; $i < 3; $i++) {
    if ($i == 1) {open F, "<$fic1";}
    else {open F, "<rest.tmp";}

    my $arbre = "";
    while (<F>) {$arbre .= $_ ;}
    close F;

    $arbre =~ s/\n//g; chop $arbre; # enleve le ; final
    $arbre =~ s/\s+//g;    # vire espaces de la chaine si y en a

    # Check if correct numbers of opening and closing brackets
    my $tmp = $arbre;
    $openNb = ($tmp =~ tr/(//); $closeNb = ($tmp =~ tr/)//);
    die "Error: unmatching numbers of brackets in Newick format of tree\n" unless ($openNb == $closeNb);

    # ajoute les arb lus dans ce fichier aux arbres a traiter
    push @arb, $arbre;
}

&Linearise(0);
verbose @pureTree;
verbose "\n\n";
$lastnd = &Pointerise(1);
verbose "\n\n";

# foreach (keys @fils[1]) {
# 	verbose "$_ =>";
# 	@tmpfils = split / /, $fils[1]{$_};
# 	foreach $f (@tmpfils) {
# 		verbose " $f ($nomtax[1][$f])";
# 	}
# 	verbose "\n";
# }

verbose "\n\nBinarise...\n\n";
verbose "-> $lastnd\n";
&Binarise(0, 1, $lastnd);
verbose "\n\n";

# foreach (keys @fils[1]) {
# 	verbose "$_ =>";
# 	@tmpfils = split / /, $fils[1]{$_};
#     foreach $f (@tmpfils) {
# 		verbose " $f ($nomtax[1][$f])";
# 	}
# 	verbose "\n";
# }

verbose "\n\nSwap auto...\n\n";

&swapAuto(1, 0);
verbose "\n\n";

# foreach (keys @fils[1]) {
# 	verbose "$_ =>";
# 	@tmpfils = split / /, $fils[1]{$_};
# 	foreach $f (@tmpfils) {
# 		verbose " $f ($nomtax[1][$f])";
# 	}
# 	verbose "\n";
# }

# for my $k1 ( keys %parentsHash ) {
# 	print "$k1 a pour ancetres :\n\t";
 
# 	for my $k2 ( keys %{$parentsHash{ $k1 }} ) {
#     	print " $k2 ";
# 	}
# 	print "\n";
# }

verbose "\n\nDebinarisation...\n\n";

&Unbinarise(1);


# foreach (keys @fils[1]) {
# 	verbose "$_ =>";
# 	@tmpfils = split / /, $fils[1]{$_};
# 	foreach $f (@tmpfils) {
# 		verbose " $f ($nomtax[1][$f])";
# 	}
# 	verbose "\n";
# }

$ch = ToNewick(0,1);
$ch =~ s/\)[0-9.eE\-+]+:[0-9.eE\-+]+$/)/;

open Fout,">fakenwk.tmp";		
    print Fout $ch, ";";
close OUT;

my $newch = `${dirvar}rmvFakeLengthAndSupport.pl LS fakenwk.tmp`;
print $newch;
