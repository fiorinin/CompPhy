#!/usr/bin/perl

#################################################
# INPUT:
# One or several files containing newick forms of ROOTED trees 
# Trees can contain polytomies or multiple occurences of a same taxa 
# (multi-labeled trees).
# Trees MUST  have branch LENGTH values at all taxa and clades, ALSO AT ROOT, 
# Trees MUST  have SUPPORT values clades, ALSO AT ROOT,
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
# Trees are then encoded as node pointing on one another thanks to a hash table

# V.Berry as of 16th august 2011.
###################################################
# Var globales : tabeau des arbres, puis pour chacun 
# les fils/pere de noeuds, le numnoeud d'un tax, 
# the length of the edge above a node
# and the support value of the branch above a node (ex: bp or aLRT)
my (@arb, @fils, @pere , @tax , @nomtax , @length, @support, %nbocc);
##################################
sub verbose  { }#  for (@_) {print;} } #  
#########################
sub nextToken {
    $chaine = $_[0];
    if ($chaine =~ /^\w/) {     # [0-9\.eE\+\-]+
	if ($chaine =~ /^(\w[^:]*)\:([0-9\.eE\+\-]+)(.*)/) { # taxon
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
    elsif ($chaine =~ /^\)([0-9\.eE\-\+]+):([0-9\.eE\-\+]+)(.*)/) { # ending clade
	#verbose "-)-";
	return (")",$1,$2,$3);}
    else { die "UNEXPECTED TOKEN: $chaine\n";}
}
########################
sub Pointerise {
#   @arb, @fils , @pere , @tax;
	my %taxThisTree = ();
    $numTree = $_[0]; # num d'arbre

$chaine = $arb[$numTree] ;

    verbose "j'analyse le $numTree ieme arbre : $chaine\n";
    $courant = 0; $nextnd = 1; # nd 0 = racine
    $pere[$courant]=-1; # encodes the root
    while ($chaine) {
		@l = &nextToken($chaine);
		$chaine = pop @l;
		if ($l[0] =~ /^\w/) { # taxon
			$tax[$numTree]{$l[0]}.="$courant "; # taxon -> num de son noeud			
			$nomtax[$numTree][$courant] = $l[0]; # numnoeud -> nom taxon
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
		else { # ")" : revient au pere
			$courant = $pere[$numTree]{$courant}; # go back to the father of this node
			$support[$numTree][$courant] = $l[1]; # lgth of the branch from the father to this node
			$length[$numTree][$courant] = $l[2]; # lgth of the branch above this node
			# ATTENTION : soucis quand c'est la racine - je patche provisoirement en adaptant ToNewick pour la racine !!!
		}
#	verbose "reste -$chaine-\n";
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
sub rmvTaxFromTree { # !!! has to be done while preserving order or remaining nodes (for calling scripts purposes)
    my $t = $_[0]; my $tree = $_[1];
    verbose	 "\nrmvTaxFromTree $t from tree $tree\n";
    $nds = $tax[$tree]{$t}; chop $nds ; # rmv trailing spc if still there
	verbose "nds is -$nds-\n";
	@NdsTax = split / /,$nds;
	# the taxa name can be present at several nodes in case of MulTrees.
	foreach $ndTax (@NdsTax) {
		verbose "I have to rmv leaf $ndTax\n";
		$p = $pere[$tree]{$ndTax};
		@frer = split / /, $fils[$tree]{$p};
		verbose "$t is node $ndTax and his father is $p\n";
		if ($#frer>1) { # case where rmving node is enough : no branch merge, no replacing.
			verbose "father has more than two children\n";
			$chfrer = "";
			foreach $f (@frer) {$chfrer .= "$f " unless ($f eq $ndTax) ; }
			chop $chfrer;
			# verbose "new children of $p are -$chfrer-\n";
			$fils[$tree]{$p} = $chfrer;
		}
		else { # rmv father node, ie merges two branches 
			if ($frer[0] eq $ndTax) {$sibl = $frer[1];} else {$sibl=$frer[0];}
			verbose "just one sibling = $sibl \n";
			
			# sibling's support get max of his current support and that of $p (father of node to remove)
			
			@descendingOrder = sort {$b <=> $a} ($support[$tree][$sibl] , $support[$tree][$p]) ;			
			verbose "order of supports = "; verbose join ("," , @descendingOrder); verbose "\n";
			$support[$tree][$sibl]= $descendingOrder[0];
			verbose "\nsupport given to sibl is $support[$tree][$sibl]\n";
			
			# adding lengths of the branches to be merged (ok for nbs in scientif. notations)
			$length[$tree][$sibl] += $length[$tree][$p] ;
			
			if ($p != 0) { #  y a un grand-pere
				$gp = $pere[$tree]{$p};
				verbose "grandfather is $gp its children being $fils[$tree]{$gp}\n";
				# rmv the father (ie, $p) from the child list of grandfather (ie, $gp)
				$fgp = " ".$fils[$tree]{$gp}." ";
				# Previously done:	$fgp =~ s/ $p / /; chop $fgp; 
				#				    $rev = reverse $fgp; chop $rev ; $rev = reverse $rev;
				#				    $fils[$tree]{$gp} = $rev; 
				#				    # $sibl est l'unique frere de $ndTax
				#			     	$fils[$tree]{$gp} .= " $sibl"; # unique frere devient fils du g-p.
				$fgp =~  s/ $p / $sibl /; chop $fgp ; $fgp = reverse $fgp; chop $fgp ;
				$fils[$tree]{$gp} = reverse $fgp;			
				#verbose "les fils du grand-pere sont now $fils[$tree]{$gp} : \n";#exit;				
				$pere[$tree]{$sibl} = $gp;
			}
			else { # $p is the root -> $sibl becomes the new root (with node number 0)
				 verbose "supprime un de deux fils de la racine\n";
				# $sibl devient donc racine et prend donc le num de noeud 0
				if (! (exists $fils[$tree]{$sibl})) {die " Problem: only one taxa remaining in a restricted tree!\n";}
				$length[$tree][0] = $length[$tree][$sibl]; # stores length of sibl at root
				$support[$tree][0] = $support[$tree][$sibl]; # stores length of sibl at root
				
				@filsuf = split / /, $fils[$tree]{$sibl};
				foreach $f (@filsuf) {$pere[$tree]{$f} = 0;} # fils de uf on pere = 0
				$fils[$tree]{0} = $fils[$tree]{$sibl}; # racine (nd = 0) a les fils de uf
				# NOTE: nothing to do with support and branches here
				
			} # case where new root node
		} # rmv father
	}	# foreach leafnode named after the taxa to remove
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

# MODIF nov 2013:
	    #if ($pere[$n] == -1) {return $ch.")"} # root node -> no length nor support above
		#else { 
		return $ch.")".$support[$tree][$n].":".$length[$tree][$n]; #}

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
##################################################
sub CheckAllSupport {
  my $tree = $_[0];
  chop $tree; # rmv last parenthesis not followed by a support sign
  #print "Tree is $tree\n";exit;
  #if ($tree =~ /(\)[^0-9])/) {print "SOUCIS = $1\n";}
  die "Error: tree must contain all or no branch support" if ($tree =~ /\)[^0-9]/);
}
##################################################
sub CheckAllLgth {
 verbose "Check all lengths\n";
  my $tree = $_[0];
#  print "$tree\n";exit;
  # Checks if taxa have lengths
  while ($tree =~ /[\(,]\w/) {
  	$tree =~ s/([\(,])\w[^:,\)]*(.)/$1#$2/;
#  	verbose "REGEXP : ---$1--- ---$2---\n";
    #verbose "$tree\n";
  	die "Error: tree must contain all or no branch length" unless ($2 eq ':');
  }
  # Checks if clades have lengths
  	# 1 - rmv supports
  while ($tree =~ /\)[0-9]/) { #print "$tree\n";
  	$tree =~ s/\)[0-9\.eE\+\-]+([:,\)])/\)$1/;
  }
  verbose "Tree without supports :\n$tree\n";
    # 2 - check if closing bracket has no length behind
  die "Error: tree must contain all or no branch length" if ($tree =~ /\)[\),]/);
  verbose "c'est ok\n";
}
########################################################################################
########################################################################################
###############											################################
###############				 MAIN				 		################################
###############											################################
########################################################################################
########################################################################################
# params = names of files containing trees
die "\n\tUsage: ./restrict2commons.pl inputFile_1 .... inputFile_k > outputFile\n\n" if ($#ARGV<0);

#$#nomtax = 4999; # array initialized with 5000 cells; # needed ???
#for ($i=0;$i<4999;$i++) {$tab = $nomtax[$i]; $#$tab = 4999;}

my $fic; my $inTree=0;
foreach $fic (@ARGV) {
#    $fic = shift @ARGV;
    open F,$fic or die "Cannot open $fic\n";
    verbose "Reading file $fic\n";
    my $arbre;
    while (<F>) {$arbre .= $_ ;} 
    close F;
    $arbre =~ s/[^-+\w\.;:\,\(\)]//g; # vire tout ce qui n'est pas attendu (dont ^M, etc)
    $arbre =~ s/\n//g; chop $arbre; # enleve le ; final sinon arb fantome
    
    # Check that tree has all branch lengths and supports (at root also)
    # !!! the order of these tests is important !!!
    die "Abort due to a missing branch support (maybe at root)!\n" if ($arbre =~ /\)[^+-\d]/) ;
    die "Abort due to a missing branch support (maybe at root)!\n" if ($arbre =~ /\)[+-][^\d]/) ;
    die "Abort due to a missing branch length for a taxon!\n" if ($arbre =~ /[\(,][^:,\)]+[,\)]/) ;
    die "Abort due to a missing branch length at root!\n" if ($arbre =~ /\)[^:\)]+$/) ;
    die "Abort due to a missing branch length at root!\n" if ($arbre !~ /:[+-]*\d[\d\+-eE]*$/) ;
    
    my @AllTrees = split /;/, $arbre;
    verbose "This file contains ", 1+$#AllTrees," trees to process\n";
    for ($a=0;$a<=$#AllTrees;$a++) {
       verbose "tree $a\n";
       $AllTrees[$a] =~ s/\s+//g;    # vire espaces de la chaine si y en a
       # Check if correct numbers of opening and closing brackets
       my $tmp = $AllTrees[$a];
       $openNb = ($tmp =~ tr/(//); $closeNb = ($tmp =~ tr/)//);
       die "Error: unmatching numbers of brackets in Newick format of tree\n" unless ($openNb == $closeNb);
	   $inTree++;
    }
    verbose $#AllTrees+1," trees in file $fic\n";
    # ajoute les arb lus dans ce fichier aux arbres a traiter
    for ($a=0;$a<=$#AllTrees;$a++) { push @arb, $AllTrees[$a]; verbose "$AllTrees[$a]\n";}  
}
verbose "In total  ",$#arb+1," trees to be processed\n\n\n";


# Transforme chaque arbre en dico de noeuds 
$numarb = 0;
for $a (@arb) {
         &Pointerise($numarb);
#        for $c (sort keys %{ $tax[$numarb] }) {verbose "$c => $tax[$numarb]{$c}\n";}
         verbose "taxons de l'arbre $numarb: ";
         $mestax = $nomtax[$numarb];
         foreach (@$mestax) {verbose "-$_-" if (!($_ eq "")) ;}
         verbose "\n";
         $numarb++;
    }
#}


# ENLEVE LES TAXONS NON PRESENTS DANS TOUS LES ARBRES
verbose "taxons : ";
$nbarbres = 1+$#arb;
foreach $t (sort keys %nbocc)  {  #verbose "$t-> $nbocc{$t} fois\n";
    if ($nbocc{$t}<$nbarbres) { # taxon to be removed
       for ($a=0;$a<=$#arb;$a++) {
         if (exists $tax[$a]{$t}) { # this taxon appears in this tree
           rmvTaxFromTree ($t,$a);
           # print "Modif tree is : ".ToNewick(0,$a),";\n";
         }
       }
    }
}

# Imprime les arbres au format Newick
#for ($a=0;$a<=$#arb;$a++) {
#    verbose "imprime arbre numero $a\n";
#    #PtrToScreen(0,$a),";\n";
#    verbose ToNewick(0,$a),";\n";
#}

# Imprime les arbres resultant au format Newick a l'ecran
#open OUT,">u.out" or die "impossible de creer le fich de sortie\n";
for ($a=0;$a<=$#arb;$a++) {
    #verbose "imprime arbre numero $a\n";
    $ch = ToNewick(0,$a);
    verbose "\n\n\t$ch\n\n";
    print $ch,";\n";
}



