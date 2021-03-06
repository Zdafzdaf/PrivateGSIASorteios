<?php

  class Cadastros {

    private $tabela = 'gsia_accounts';
    private $tabela_id = 'act_accounts_id';
    private $tabela_data_cadastro = 'act_account_data_cadastro';
    private $tabela_senha = 'act_account_senha';
    private $tabela_cpf = 'act_account_cpf';
    
    private $tabela_credit = 'gsia_accounts_info';

    function addRegistro($data)
    {
      $querys = new Querys;

      // Data cadastro atual
      $data[$this->tabela_data_cadastro] = date('Y-m-d H:i:s');

      // Transforma senha em MD5
      $data[$this->tabela_senha] = $querys->escape(md5($data[$this->tabela_senha]));

      // CPF sem ponto e traço
      $valor = $data[$this->tabela_cpf];

      $valor = str_replace(".", "", $valor);
      $valor = str_replace(",", "", $valor);
      $valor = str_replace("-", "", $valor);
      $valor = str_replace("/", "", $valor);

      $data[$this->tabela_cpf] = $valor;

      // Retorna todos os campos da tabela
      $result = $this->getCampos();
      
      // Monta sql
      $sql = "INSERT INTO " . $this->tabela . " SET ";

      foreach ($result['nome'] as $key => $campo) {
        if ((isset($data[$result['nome'][$key]])) && (!empty($data[$result['nome'][$key]]))) {
          if ($result['tipo'][$key] == 'int') {
            $sql .= $result['nome'][$key] . " = '" . (int)$data[$result['nome'][$key]] . "', ";
          } else if ($result['tipo'][$key] == 'real') {
            $sql .= $result['nome'][$key] . " = " . floatval($data[$result['nome'][$key]]) . ", ";
          } else {
            $sql .= $result['nome'][$key] . " = '" . $querys->escape($data[$result['nome'][$key]]) . "', ";
          }
        }
      }

      $sql = substr(trim($sql), 0, -1);

      $result = $querys->query($sql);

      return $result;
    }   

    /**
     * Metodo editRegistro
     *
     * @access public
     * @param integer $registro_id
     * @param array $data
     * @return void
     */
    public function editRegistro($registro_id, $data)
    {
      $querys = new Querys;

      // Transforma senha em MD5
      $data[$this->tabela_senha] = $querys->escape(md5($data[$this->tabela_senha]));

      // CPF sem ponto e traço
      $valor = $data[$this->tabela_cpf];

      $valor = str_replace(".", "", $valor);
      $valor = str_replace(",", "", $valor);
      $valor = str_replace("-", "", $valor);
      $valor = str_replace("/", "", $valor);

      $data[$this->tabela_cpf] = $valor;

      // Retorna todos os campos da tabela
      $result = $this->getCampos();

      // Monta sql
      $sql = "UPDATE " . $this->tabela . " SET ";

      foreach ($result['nome'] as $key => $campo) {
        if ((isset($data[$result['nome'][$key]])) && (!empty($data[$result['nome'][$key]]))) {
          if ($result['tipo'][$key] == 'int') {
            $sql .= $result['nome'][$key] . " = '" . (int)$data[$result['nome'][$key]] . "', ";
          } else if ($result['tipo'][$key] == 'real') {
            $sql .= $result['nome'][$key] . " = " . floatval($data[$result['nome'][$key]]) . ", ";
          } else {
            $sql .= $result['nome'][$key] . " = '" . $querys->escape($data[$result['nome'][$key]]) . "', ";
          }
        }
      }

      $sql = substr(trim($sql), 0, -1);

      $sql .= " WHERE " . $this->tabela_id . " = " . (int)$registro_id;

      $result = $querys->query($sql);

      return $result;
    }

    /**
     * Metodo getRegistros
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function getRegistros($data = array()) 
    {
      $session = new Session;
      $session->StartSession();
      $user = new User;
      $user->CheckLogin($session->data);
      $querys = new Querys;

      $sql = "SELECT 
                  *
                FROM  
                  " . $this->tabela . "
                WHERE 
                  act_accounts_id = '" . $user->getId() . "'";
      
      $query = $querys->query($sql);

      return $query->rows;
    }

    /**
     * Metodo CheckCPFExists
     *
     * @access public
     * @param string $cpf
     * @return boolean
     */
    public function CheckCPFExists($cpf) 
    {
      $session = new Session;
      $session->StartSession();
      $user = new User;
      $user->CheckLogin($session->data);
      $querys = new Querys;

      $sql = "SELECT 
                  *
                FROM  
                  " . $this->tabela . "
                WHERE 
                  act_account_cpf = '" . $cpf . "'";
      
      $query = $querys->query($sql);

      if ($query->num_rows == 0) {
        return false;
      } else {
        return true;
      }
    }

    /**
     * Metodo GetCreditCount
     *
     * @access public
     * @param string $act_accounts_id
     * @return int
     */
    public function GetCreditCount($act_accounts_id) 
    {
      $session = new Session;
      $session->StartSession();
      $user = new User;
      $querys = new Querys;

      $sql = "SELECT 
                  *
                FROM  
                  " . $this->tabela_credit . "
                WHERE 
                  act_accounts_id = '" . $act_accounts_id . "'";
      
      $query = $querys->query($sql);

      if ($query->num_rows == 0) {
        return '0';
      } else {
        return $query->row['act_account_credit'];
      }
    }

    /**
     * Metodo CheckCreditExists
     *
     * @access public
     * @param int $act_accounts_id
     * @return boolean
     */
    public function CheckCreditExists($act_accounts_id) 
    {
      $session = new Session;
      $session->StartSession();
      $user = new User;
      $user->CheckLogin($session->data);
      $querys = new Querys;

      $sql = "SELECT 
                  *
                FROM  
                  " . $this->tabela_credit . "
                WHERE 
                  act_accounts_id = '" . $act_accounts_id . "'";
      
      $query = $querys->query($sql);

      if ($query->num_rows == 0) {
        return false;
      } else {
        return true;
      }
    }

    /**
     * Metodo AddCredit
     *
     * @access public
     * @param int $act_accounts_id
     * @param int $cnt_credit
     * @return array
     */
    public function AddCredit($act_accounts_id, $cnt_credit) 
    {
      $session = new Session;
      $session->StartSession();
      $querys = new Querys;

      $sql = "INSERT INTO 
                  " . $this->tabela_credit . " 
                SET 
                  act_account_credit = '" . (int)$cnt_credit . "',
                  act_accounts_id = '" . (int)$act_accounts_id . "'";
      
      $query = $querys->query($sql);

      return $query;
    }

    /**
     * Metodo UpdateCredit
     *
     * @access public
     * @param int $act_accounts_id
     * @param int $cnt_credit
     * @return array
     */
    public function UpdateCredit($act_accounts_id, $cnt_credit) 
    {
      $session = new Session;
      $session->StartSession();
      $querys = new Querys;

      $sql = "UPDATE 
                  " . $this->tabela_credit . " 
                SET 
                  act_account_credit = '" . (int)$cnt_credit . "'
                WHERE 
                  act_accounts_id = '" . (int)$act_accounts_id . "'";
      
      $query = $querys->query($sql);

      return $query;
    }

    /**
     * Metodo getCampos
     *
     * @access public
     * @return array
     */
    public function getCampos()
    {
      $querys = new Querys;

      $sql = "SELECT * FROM " . $this->tabela;

      $result = $querys->query($sql);

      return $result->fields;
    }
  } 
?>