<?php 
	trait TraitManager{
		protected $trait_bdd;
		
		public function taille(Array $tableau){
			$taille = 0;
			foreach($tableau as $element){
				$taille++;
			}
			return $taille;
		}

		public function existe(Array $tableau, $nom_table){
			$i = 0;
			$content = $this->createQuery($tableau, true);
			if(!empty($content)){
				$reponse = $this->trait_bdd->prepare("SELECT * FROM ".$nom_table." WHERE ".$content." LIMIT 1");
				$reponse->execute($tableau);
			}
			while ($donnees = $reponse->fetch())
				$i++;
			$reponse->closeCursor();
			return $i == 1;
		}


		public function ajouter(Array $donnee, $nom_table){
			$content = $this->createQuery($donnee);
			$contenu = "INSERT INTO ".$nom_table." SET ".$content;
			//echo $contenu;
			$reponse = $this->trait_bdd->prepare($contenu);
			$bool = $reponse->execute($donnee) != 0;
			return $bool;
		}

		public function modifier(Array $donnee, $nom_table, Array $index){
			$bool = false;
			//$index = ""; considerer comme identite ou cle primaire par defaut 'id'
			$content = $this->createQuery($donnee);
			if(!empty($content)){
				$contenu = "UPDATE ".$nom_table." SET ".$content." WHERE ".$this->createQueryWithValue($index, true);
				$reponse = $this->trait_bdd->prepare($contenu);
				//echo $contenu;
				$bool = $reponse->execute($donnee) != 0;
			}
			return $bool;
		}

		public function supprimer($nom_table, Array $index){
			//On supprime completement le rang
			return $this->trait_bdd->exec("DELETE FROM ".$nom_table." WHERE ".$this->createQueryWithValue($index, true)) != 0;
			//echo "DELETE FROM ".$nom_table." WHERE ".$this->createQueryWithValue($index, true);
		}

		public function dernier($nom_table, $library_object = null, $index = 'id'){
			//l'index doit etre du type entier ou un numerique
			$contenu = "SELECT * FROM ".$nom_table." ORDER BY ".$index." DESC LIMIT 1";
			echo $contenu;
			$query = $this->trait_bdd->query($contenu);
			if(is_null($library_object))
				$query->setFetchMode(PDO::FETCH_ASSOC);
			else
				$query->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $library_object);
			return $query->fetch();
		}

		public function lister($nom_table, $library_object = null, $limit = null, Array $filtre = null, Array $where = null){
			//Ici filtre permet de de prendre un certain nombre de colonne ex: array('nom', 'prenom') ce dernier prend les colonnes nom et prenom
			//Limit est le nombre de ligne a selectionner
			//Where c'est pour selectionner selon un critere
			$texte = is_null($limit) ? "" : "LIMIT ".$limit;
			$filtre = is_null($filtre) ? "*" : $this->createFiltre($filtre);
			$critere = is_null($where) ? "" : " WHERE ".$this->createQueryWithValue($where, true);
		

			$contenu = "SELECT ".$filtre." FROM ".$nom_table." ".$texte.$critere;
			$query = $this->trait_bdd->query($contenu);

			//echo $taille;
			if(is_null($library_object)){
				$query->setFetchMode(PDO::FETCH_ASSOC);
				return $query->fetchAll();
			}
			else{
				$query->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $library_object);
				$liste_objet = $query->fetchAll();
				/*$liste_objet = array();
				while ($element = $query->fetch()) {
					# code...
					$liste_objet[] = new $library_object($element);
				}
				$query->closeCursor();*/
				
				return $liste_objet;
			}
				
		}

		public function prendre($nom_table, Array $critere, $library_object = null){
			//permet de prendre un seul enregistrement selon un critere 
			$content = $this->createQueryWithValue($critere, true);
			$contenu = "SELECT * FROM ".$nom_table." WHERE ".$content." LIMIT 1";
			$query = $this->trait_bdd->query($contenu);
			$query->setFetchMode(PDO::FETCH_ASSOC);
			return is_null($library_object) ? $query->fetch() : new $library_object($query->fetch());
		}

		public function execute($requette){
			$query = $this->trait_bdd->query($requette);
			return $query->fetch(PDO::FETCH_ASSOC);
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
					if($compteur == $taille - 1)
						$virgule = "";
					$content .= $cle." = :".$cle.$virgule." ";
					$compteur++;
				}
			}
			return $content; 
		}

		private function createFiltre(Array $tableau = null){
			$taille = $tableau != null ? $this->taille($tableau) : 0; 
			$compteur = 0;
			$content = "";
			if($taille != 0){
				$virgule = ",";
				foreach($tableau as $element){
					if($compteur == $taille - 1)
						$virgule = "";
					$content .= $element.$virgule." ";
					$compteur++;
				}
			}
			return $content; 
		}

		//Cette methode permet de complete la requette en ajoutant des element apres la clause 'WHERE'
		private function createQueryWithValue(Array $tableau = null, $update = false){
			$taille = $tableau != null ? $this->taille($tableau) : 0; 
			$compteur = 0;
			$content = "";
			if($taille != 0){
				$virgule = ",";
				if($update)
					$virgule = " AND ";
				foreach($tableau as $cle => $element){
					if($compteur == $taille - 1)
						$virgule = "";
					if(!is_int($element))
						$element = "'".$element."'";
					$content .= $cle." = ".$element.$virgule." ";
					$compteur++;
				}
			}
			return $content; 
		}
	}
?>
