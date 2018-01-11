<?php function taille(Array $liste){$taille=0;foreach($liste as $element){$taille++;}return$taille;}function existe($data_base,$nom_table,Array $colonne_critere=null){$i=0;$content=createQuery($colonne_critere,true);if(!empty($content)){$requette="SELECT COUNT(*) as nb FROM ".$nom_table." WHERE ".$content." LIMIT 1";$reponse=$data_base->prepare($requette);try{$reponse->execute($colonne_critere);}catch(PDOException $e){printStackTrace($e,$nom_table);}$i=$reponse->fetch()['nb'];}return$i==1;}function ajouter($data_base,$nom_table,Array $colonne_valeur=null){$content=createQuery($colonne_valeur);if(!empty($content)){$requette="INSERT INTO ".$nom_table." SET ".$content;$reponse=$data_base->prepare($requette);try{return$reponse->execute($colonne_valeur)!=0;}catch(PDOException $e){printStackTrace($e,$nom_table);}}return false;}function inserer($data_base,$nom_table,Array $colonne_valeur=null){$content=createQuery($colonne_valeur);if(!empty($content)){$requette="INSERT INTO ".$nom_table." SET ".$content;$reponse=$data_base->prepare($requette);try{return$reponse->execute($colonne_valeur)!=0;}catch(PDOException $e){printStackTrace($e,$nom_table);}}return false;}function modifier($data_base,$nom_table,Array $colonne_valeur=null,Array $colonne_critere=null){$content=createQuery($colonne_valeur);if(!empty($content)&&sizeof($colonne_critere)>0){if(existe($data_base,$nom_table,$colonne_critere)){$contenu="UPDATE ".$nom_table." SET ".$content." WHERE ".createQueryWithValue($colonne_critere,true);$reponse=$data_base->prepare($contenu);try{return$reponse->execute($colonne_valeur)!=0;}catch(PDOException $e){printStackTrace($e,$nom_table);}}}return false;}function supprimer($data_base,$nom_table,Array $colonne_critere=null){if(existe($data_base,$nom_table,$colonne_critere)){$requette="DELETE FROM ".$nom_table." WHERE ".createQueryWithValue($colonne_critere,true);try{return$data_base->exec($requette)!=0;}catch(PDOException $e){printStackTrace($e,$nom_table);}}return false;}function lister($data_base,$nom_table,Array $projection=null,Array $colonne_critere=null,$selon_ordre=null,$limit=null){$limit=is_null($limit)?"":"LIMIT ".$limit;$selon_ordre=is_null($selon_ordre)?"":" ORDER BY ".$selon_ordre;$projection=is_null($projection)?"*":createFiltre($projection);$critere=is_null($colonne_critere)||empty($colonne_critere)?"":" WHERE ".createQueryWithValue($colonne_critere,true);$requette="SELECT ".$projection." FROM ".$nom_table." ".$critere.$selon_ordre;$query=$data_base->prepare($requette);try{$query->execute();}catch(PDOException $e){printStackTrace($e, $nom_table);}return$query->fetchAll();}function prendre($data_base,$nom_table,Array $projection=null,Array $colonne_critere=null){$projection=is_null($projection)||empty($projection)?"*":createFiltre($projection);$critere=is_null($colonne_critere)||empty($colonne_critere)?"":" WHERE ".createQueryWithValue($colonne_critere,true);$requette="SELECT ".$projection." FROM ".$nom_table." ".$critere;$query=$data_base->prepare($requette);try{$query->execute();}catch(PDOException $e){printStackTrace($e,$nom_table);}return$query->fetch();}function execute($data_base,$requette){$query=$data_base->query($requette);try{return$query->fetch(PDO::FETCH_ASSOC);}catch(PDOException $e){printStackTrace($e,$nom_table);}}function createQuery(Array $projection=null,$update=false){$taille=$projection!=null?taille($projection):0;$content="";if($taille!=0){$virgule=",";if($update)$virgule=" AND ";$compteur=0;foreach($projection as $cle=>$element){if($compteur==$taille-1)$virgule="";$content .=$cle." = :".$cle.$virgule." ";$compteur++;}}return$content;}function createFiltre(Array $projection=null){$taille=$projection!=null?taille($projection):0;$content="";if($taille!=0){$virgule=",";$compteur=0;foreach($projection as $element){if($compteur==($taille-1))$virgule="";$content .=$element.$virgule." ";$compteur++;}}return$content;}function createQueryWithValue(Array $projection=null,$update=false){$taille=$projection!=null?taille($projection):0;$content="";if($taille!=0){$virgule=",";if($update)$virgule=" AND ";$compteur=0;foreach($projection as $cle=>$element){if($compteur==$taille-1)$virgule="";if(!is_int($element))$element="'".$element."'";$content .=$cle." = ".$element.$virgule." ";$compteur++;}}return$content;}function valeur_entre($char,$chaine){$taille=strlen($chaine);$position="";for($i=0;$i<$taille;$i++){if($chaine[$i]==$char){if($i<($taille-1))$position .=$i.',';else$position .=$i;}}$tab=explode(',',$position);return substr($chaine,$tab[0]+1,($tab[1]-1)-$tab[0]);}function printStackTrace(Exception $e,$nom_table){$indice=sizeof($e->getTrace())-1;$trace=$e->getTrace()[$indice];$file=$trace['file'];$function=$trace['function'];$line=$trace['line'];$tab_params=$trace['args'];$erreur=$e->errorInfo[2];$tab=valeur_entre("'",$erreur);$data_not_exist='<strong style="color:red;font-family:monospace;font-size:14px;">\''.$tab.($e->errorInfo[1]==1064?'\' précisement => \''.$tab_params[4]:"" ).'\'</strong>';if($e->errorInfo[1]==1054)$erreur="La colonne ".$data_not_exist." passée n'existe pas dans la table <strong>".$nom_table."</strong>";elseif($e->errorInfo[1]==1146)$erreur="La table ".$data_not_exist." passée n'existe pas";else$erreur="Erreur de syntax SQL, vérifier le manuel correspondant à votre SGBD ".$data_not_exist;die('Erreur PDO dans <strong>'.$file.'</strong><br/>Ligne : <strong>'.$line.'</strong><br/>Verifier les '.utf8_decode('paramètres passés à ').'la fonction : <strong>'.$function.'(...)</strong> <br/>'.utf8_decode($erreur).'<br/>');}?>