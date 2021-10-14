<?php
    include_once 'modele/Modele.php';

    class Routeur extends Modele
    {
        public function informations(): array
        { return ['index', 'strict', 'ierr']; }
        private const INI = 'ini/routeur.ini';

        public function __construct(?string $fichier_ini = null)
        {
            if ($fichier_ini === null) $fichier_ini = self::INI;             // Initialisation des attributs depuis un fichier INI
            $this->depuis_ini($fichier_ini);
        }
        
        protected $_index;                          // Nom de la vue par défaut
        public function index() : string { return $this->_index; }
        protected $_strict;                          // Si vrai, seule les requêtes de la forme */<vue>/ seront acceptées
        public function strict() : bool { return $this->_strict; }
        protected function modifier_strict(bool $valeur) { $this->_strict = $valeur; }
        protected $_ierr;                           // Clé d'indication du code erreur
        public function ierr() : string { return $this->_ierr; }
     
        public function definir_vue(string $uri) : string
        {
            $uri = substr($uri, 1);                 // On retire le premier '/'
            if ($uri[0] == '?' 
            && (strpos($uri, '+', 1) < strpos($uri, '=', 1))
            && $this->_strict)
                $uri = $this->_ierr . '=404';           // 404
            if (strlen($uri) === 0)                 // La requête est vide
                return $this->_index;                  // On chargera la vue par défaut
            if ($uri[-1] == '/')             // Adaptation des requêtes de la forme */<vue>/
                $uri = substr($uri, 0, -1);             // On retire le dernier '/'
            $err = explode('=', $uri, 2);
            if ($err[0] === $this->_ierr)   // Spécification d'une erreur
                return implode('/', $err);
            return $uri;
        }
    }

?>