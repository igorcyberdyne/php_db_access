<?php 
	namespace library;
	trait TraitManager{
		protected $trait_bdd;
		
		public function taille(Array $tableau){
			$taille = 0;
			foreach($tableau as $element){
				$taille++;
			}
			return $taille;
		}
		
		public function isExixte(Array $tableau, $nom_table){
			$i = 0;
			$content = $this->createQuery($tableau, true);//true est considere pour la selection
			if(!empty($content)){
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content);
				$reponse->execute($tableau);
				//print_r($tableau);
				//echo "SELECT * FROM ".$nom_table." WHERE ".$content;
				//$this->updateWhere($nom_table, $tableau, false);
			}
			while ($donnees = $reponse->fetch()){
				$i++;
			}
			$reponse->closeCursor();
			
			return $i == 1;
		}

		public function isExiste(Array $tableau, $nom_table){
			$i = 0;
			$content = $this->createQuery($tableau, true);//true est considere pour la selection
			if(!empty($content)){
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content);
				$reponse->execute($tableau);
				//print_r($tableau);
				//echo "SELECT * FROM ".$nom_table." WHERE ".$content;
				//$this->updateWhere($nom_table, $tableau, false);
			}
			while ($donnees = $reponse->fetch()){
				$i++;
			}
			$reponse->closeCursor();
			
			return $i > 0;
		}

		public function isDelete(Array $tableau, $nom_table){
			$i = 0;
			$content = $this->createQuery($tableau, true);//true est considere pour la selection
			if(!empty($content)){
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content." AND delete_on IS NOT NULL");
				$reponse->execute($tableau);
			}
			while ($donnees = $reponse->fetch()){
				$i++;
			}
			$reponse->closeCursor();
			
			return $i == 1;
		}

		
		public function insert($nom_table, Array $donnee = null){
			return $this->updateWhere($nom_table, $donnee, false);
		}
		
		public function selectAll($nom_table, $library_object, $parent = false, array $donnee_parent = array(), array $donnee_index = array(), $library_parent = '\app\modeles\\'){
			$query = $this->trait_bdd->query("SELECT * FROM ".$nom_table." WHERE delete_on IS NULL");
			$query->setFetchMode(\PDO::FETCH_ASSOC);

			$liste_objet = array();

			if($parent){
				$i = 0;
				$liste = $query->fetchAll();
				foreach ($liste as $key => $value) {
					# code...
					$objet = new $library_object($value);

					foreach ($value as $cle => $valeur) {
						if(array_key_exists($cle, $donnee_parent)){
							$set_methode = 'set'.ucfirst($cle); 
							$get_methode = 'get'.ucfirst($cle);
							// $donnee_parent[$cle] correspond au nom de la table et $donnee_index[$i] correspond a toutes les tables dont la relation existe avec celui-ci
							$objet->$set_methode($this->selectWhere(array($donnee_index[$i] => $objet->$get_methode()), $donnee_parent[$cle], $library_parent.$donnee_parent[$cle]));
							
							if($this->taille($donnee_index) > 1)
								$i++;
						}
					}
					$liste_objet[] = $objet;
				}
			}
			else{
				while ($element = $query->fetch()) {
					# code...
					$liste_objet[] = new $library_object($element);
				}
				$query->closeCursor();
			}
			return $liste_objet;
		}


		public function selectLast($index, $nom_table, $library_object){
			//l'index doit etre du type entier ou un numerique
			$query = $this->trait_bdd->query("SELECT * FROM ".$nom_table.' WHERE delete_on IS NULL ORDER BY '.$index.' DESC LIMIT 1');
			$query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $library_object);
			return $query->fetch();
		}

		
		public function sudWhere($id, $nom_table, $type, Array $valeur = null){
			
		}

		//Pour un enregistrement
		public function selectWhere(Array $index, $nom_table, $library_object, $parent = false, array $donnee_parent = array(), array $donnee_index = array(), $library_parent = '\personnel\modeles\\'){
			$content = $this->createQuery($index, true);
			$objet = '';
			if(!empty($content)){
				//$reponse->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $library_object);
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content." AND delete_on IS NULL");
				$reponse->execute($index);
				//print_r($index);
				//echo "SELECT * FROM ".$nom_table." WHERE ".$content;
				$resultat = $reponse->fetch(\PDO::FETCH_ASSOC);
				$objet = new $library_object($resultat);

				if($parent){
					$i = 0;
					$taille = $this->taille($donnee_index);
					foreach ($resultat as $cle => $value) {
						//$lib_parent = '\personnel\modeles\\';
						$lib_parent = $library_parent;
						if(array_key_exists($cle, $donnee_parent)){
							$set_methode = 'set'.ucfirst($cle);
							$get_methode = 'get'.ucfirst($cle);

							$lib_parent .= $donnee_parent[$cle];

							if(!is_int($value))
								$value = "'".$value."'";
							$reponse = $this->trait_bdd->query("SELECT * FROM ".$donnee_parent[$cle]." WHERE ".$donnee_index[$i]." = ".$value." AND delete_on IS NULL");
							$resultat = $reponse->fetch(\PDO::FETCH_ASSOC);
							$objet->$set_methode(new $lib_parent($resultat));

							if($taille > 1)
								$i++;
						}
					}
				}
			}
			return $objet;
		}


		public function selectAllWhere(Array $index, $nom_table, $library_object){
			$content = $this->createQuery($index, true);
			$objet = '';
			if(!empty($content)){
				//$reponse->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $library_object);
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content." AND delete_on IS NULL");
				$reponse->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $library_object);
				$reponse->execute($index);
				//print_r($index);
				//echo "SELECT * FROM ".$nom_table." WHERE ".$content;
				$resultat = $reponse->fetchAll();
			}
			return $resultat;
		}


		
		
		public function deleteWhere($index, $identite, $nom_table){
			return $this->trait_bdd->exec("DELETE FROM ".$nom_table." WHERE ".$index." = ".$identite) != 0;
		}
		
		public function updateWhere($nom_table, Array $tableau = null, $update = false, $index = 'id', $identite = 0){
			$bool = false;
			//$index = ""; considerer comme identite ou cle primaire
			if(!is_int($identite))
				$identite = "'".$identite."'";
			$content = $this->createQuery($tableau);
			if(!empty($content)){
				if($update)
					$contenu = "UPDATE ".$nom_table." SET ".$content." WHERE ".$index." = ".$identite;
				else
					$contenu = "INSERT INTO ".$nom_table." SET ".$content;
				//$bool = $this->trait_bdd->exec($contenu) != 0;
				$reponse = $this->trait_bdd->prepare($contenu);
				$bool = $reponse->execute($tableau) != 0;
			}//echo $contenu;
			return $bool;
		}
		
		//Cette methode permet de complete la requette en ajoutant des element apres la clause 'WHERE'
		private function createQuery(Array $tableau = null, $update = false){
			$taille = $tableau != null ? $this->taille($tableau) : 0; 
			$compteur = 0;
			$content = "";
			if($taille != 0){
				$virgule = ",";
				if($update)
					$virgule = " AND ";
				foreach($tableau as $cle => $element){
					if($compteur == $taille -1)
						$virgule = "";
					$content .= $cle." = :".$cle.$virgule." ";
					$compteur++;
				}
			}
			return $content; 
		}


		public function delete($id, $nom_table, $index = "id"){
			$contenu = "UPDATE ".$nom_table." SET delete_on = NOW() WHERE ".$index." = ".$id;
			return $this->trait_bdd->exec($contenu) != 0;
		}


		public function execute_query($query){
			return $this->trait_bdd->exec($query) != 0;
		}
	}
?>