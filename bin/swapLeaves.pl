#!/usr/bin/perl
####################################################
# INPUT : - the newick tree
#         - 2 leaves
#
# OUTPUT : - the new newick tree with swapped leaves
#
# USAGE : ./swapLeaves.pl fileContainingNewickTree leaf1 leaf2 >newfile.nwk
####################################################
# Var globales : tabeau des arbres, puis pour chacun 
# les fils/pere de noeuds, le numnoeud d'un tax, 
# the length of the edge above a node
# and the support value of the branch above a node (ex: bp or aLRT)
my (@arb, @fils, @pere , @tax , @nomtax , @length, @support, %nbocc, %nbAncestors);
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
}
##################################
sub max {
	$v1 = $_[0];
	$v2 = $_[1];
	return $v2 <=> $v1 ;
}
##################################
sub getAncestors {
    $leaf = $_[0];
    $tree = $_[1];
    local @ancestors = ();
    local %relPere = ();
    while($leaf) {
        verbose "feuille $leaf analysée, suivant : $pere[$tree]{$leaf}\n";
        push(@ancestors, $leaf);
        $relPere{$pere[$tree]{$leaf}} = $leaf;
        $leaf = $pere[$tree]{$leaf};
    }
    push(@ancestors, 0);
    return (\@ancestors, %relPere);
}
##################################
sub countAncestors {
    my ($an1, $an2) = ($_[0], $_[1]);
    foreach(@$an1) {
        $nbAncestors{$_}++;
        verbose "Ancestor added for leaf 1\n";
    }
    foreach(@$an2) {
        $nbAncestors{$_}++;
        verbose "Ancestor added for leaf 2\n";
    }
}
##################################
sub commonParent {
    $anL = $_[0];
    foreach(@$anL) {
        verbose "j'analyse $_...$nbAncestors{$_}\n";
        if($nbAncestors{$_} == 2) {
            verbose "je choisis $_ ! \n";
            return $_;
        }
    }
}
##################################
sub swap {
    local ($parentNode, $tree, $f1, $f2) = ($_[0], $_[1], $_[2], $_[3]);
    local $filsnodes = $fils[$tree]{$parentNode};
    verbose "$f1 $f2";
    if ($filsnodes =~ /(.*)$f1(.*)$f2(.*)/) { $filsnodes =~ s/(.*)$f1(.*)$f2(.*)/$1$f2$2$f1$3/; }
    elsif ($filsnodes =~ /(.*)$f2(.*)$f1(.*)/) { $filsnodes =~ s/(.*)$f2(.*)$f1(.*)/$1$f1$2$f2$3/; }
    $fils[$tree]{$parentNode} = $filsnodes;
}
################################
sub LgrBidon { # add fake length of 100 (! assumed for removal) to taxa and clades to ease their parsing
# pay attention to the fact that support values may however be present as in 
# (aaa,bbb)support,(cccc,ddd)support);
#
    verbose "Adding fake lengths\n";
    my $c = $_[0];
    $c =~ s/\,/:100,/g;
    $c =~ s/\)([\d\.eE\+\-]*)/:100)$1:100/g;
    $c =~ s/\:100\:100/:100/g;
    $c =~ s/\:100$//; # remove final lgr as root does not have one
    return $c;
}
################################
sub SuppBidon { # add fake supports of 100 (! assumed for removal) to clades to ease their parsing
                # (the tree for sure contains branch lengths (true or fake)
    verbose "Adding fake supports\n";
    my $c = $_[0]; $c =~ s/\):/)100:/g; return $c;
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
		if ($pere[$n] == -1) {return $ch.")"} # root node -> no length nor support above
		else { return $ch.")".$support[$tree][$n].":".$length[$tree][$n]; }
    }
    else {#verbose "tax $nomtax[$tree][$n] ";
	  return $nomtax[$tree][$n].":".$length[$tree][$n];}
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
	#	$lastson = $sons[$#sons]; pop @sons;
		my $lastson = pop @sons;
		verbose "last child is $lastson\n";
		foreach $s (@sons) { PtrToScreen ($s,$tree);}
		PtrToScreen($lastson,$tree);
    }
    else {
	  verbose "$n is taxon $nomtax[$tree][$n]\n";}
}
#################################
############# MAIN ##############
#################################
die "\n\tUsage: ./swapLeaves.pl inputFile leaf1 leaf2 >outputFile\n\n" if ($#ARGV<0);

my $fic; my $inTree=0; my $PRG = "/data/http/www/binaries/compphy/";
$fic = shift @ARGV;
$leaf1 = shift @ARGV;
$leaf2 = shift @ARGV;

`${PRG}addFakeLengthAndSupport.pl LS $fic > rest.tmp`;

open F, "<rest.tmp";

my $arbre;
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
my $numarb = 0;

&Pointerise($numarb);

$mestax = $nomtax[$numarb];
my $leaf1nb;
my $leaf2nb;
foreach (@$mestax) {
    if ($_ =~ /$leaf1/i) {
        $leaf1nb = int $tax[$numarb]{$_};
        verbose "feuille 1 numéro $leaf1nb\n";
    }
    elsif ($_ =~ /$leaf2/i) {
        $leaf2nb = int $tax[$numarb]{$_};
        verbose "feuille 2 numéro $leaf2nb\n ";
    }
}
verbose "$leaf1nb || $leaf2nb\n";
verbose "----------------------------\n\n";
  
my ($ancestors1, %relFils1) = &getAncestors($leaf1nb, $numarb);
my ($ancestors2, %relFils2) = &getAncestors($leaf2nb, $numarb);
&countAncestors($ancestors1, $ancestors2);

verbose "@$ancestors1\n\n";
verbose "@$ancestors2\n\n";

#use Data::Dumper;
#print Dumper($nba);
if (scalar @$ancestors1 <= @$ancestors2) {
    verbose "je prends la feuille 1\n";
    $commonParent = &commonParent($ancestors1);
}
else {
    verbose "je prends la feuille 2\n";
    $commonParent = &commonParent($ancestors2);
}

verbose "Le noeud commun : $commonParent\n\n";
verbose "Ses fils sont : $fils[$numarb]{$commonParent}\n\n";

$filsToSwap1 = $relFils1{$commonParent};
$filsToSwap2 = $relFils2{$commonParent};
foreach(keys %relFils1) { verbose "$_ => $relFils1{$_}\n"; }
verbose "Fils 1 a swap : $filsToSwap1 || Fils 2 : $filsToSwap2\n\n";

&swap($commonParent, $numarb, $filsToSwap1, $filsToSwap2);

verbose "Ses fils sont maintenant : $fils[$numarb]{$commonParent}\n\n";

$ch = ToNewick(0,$numarb);
$ch =~ s/\)[0-9.eE\-+]+:[0-9.eE\-+]+$/)/;

open Fout,">fakenwk.tmp";		
    print Fout $ch, ";";
close Fout;

my $newch = `${PRG}rmvFakeLengthAndSupport.pl LS fakenwk.tmp`;
print $newch;
