<?php
    include_once 'modele/BD.php';

    abstract class ModeleBD extends Modele
    {
        abstract public function table() : string;

        protected $_id;
        public function id() : ?int { return $this->_id; }

        protected function __construct(?int $id = null, ?BD &$bd = null)
        {
            $this->_id = $id;
            if ($id === null || $bd === null) return;
            if (!$this->recevoir($bd))
                throw new Exception('Echec lors de l\'initialisation du modèle depuis la base de données.');
        }
        
        // SQL
        private function _formater_informations(string $sep, string $format, ?array &$infos = null, string $indicateur = '%i') : string
        {
            if ($infos == null) $infos = $this->informations();
            $valeurs = [];
            foreach ($infos as $info)
                $valeur[] = str_replace($indicateur, $info, $format);
            return implode($sep, $valeur);
        }
        private function _liste_parametres(?array $infos = null) : array
        {
            if ($infos === null) $infos = $this->informations();
            $params = [];
            foreach ($infos as $info)
                $params[':' . $info] = $this->{$info}();
            return $params;
        }

        public function existe(BD &$bd) : bool                                       // Vérifie si l'objet existe dans la BD
        { 
            $sql = "SELECT * FROM " . $this->table() . " WHERE id = :id";
            $params = [':id' => $this->_id];
            return count($bd->executer($sql, $params)) == 1;
        }
        public function envoyer(BD &$bd) : bool                                      // Envoie les informations du modèle vers la BD
        {
            $infos = $this->informations();
            $params = $this->_liste_parametres($infos);
            if ($this->existe($bd))
            {
                $sql = "UPDATE " . $this->table() . " SET ";
                $sql .= $this->_formater_informations(', ', '%i = :%i', $infos);
                $sql .= " WHERE id = :id";
            }
            else
            {
                $sql = "INSERT INTO " . $this->table() . " (" . implode(',', $infos) . ") VALUES (";
                $sql .= $this->_formater_informations(', ', ':%i', $infos);
            }
            return (bool)$bd->executer($sql, $params);
        }
        public function recevoir(BD &$bd) : bool                                     // Charge les informations du modèle depuis la BD
        { 
            $liste_infos = $this->_formater_informations(', ', '%i');
            $sql = "SELECT " . $liste_infos . " FROM " . $this->table() . " WHERE id = :id";
            $params = [':id' => $this->_id];
            return (bool)$bd->executer($sql, $params);
        }
        public function supprimer(BD $bd, bool $effacer_local = false) : bool        // Supprime l'equivalent du modèle dans la BD
        {
            $sql = "DELETE FROM " . $this->table() . " WHERE id = :id";
            $params = [':id' => $this->_id];
            $resultat = (bool)$bd->executer($sql, $params);
            if ($effacer_local) $this->vider();
            return $resultat;
        }
    }

?>