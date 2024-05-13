<?php
namespace NFHub\PlugBoleto;

use Exception;
use NFHub\Common\Tools as ToolsBase;
use CURLFile;

/**
 * Classe Tools
 *
 * Classe responsável pela implementação com a API de boletos do NFHub
 *
 * @category  NFHub
 * @package   NFHub\PlugBoleto\Tools
 * @author    Jefferson Moreira <jeematheus at gmail dot com>
 * @copyright 2020 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools extends ToolsBase
{

    /**
     * Busca as contas Tecnospeed de uma empresa no NFHub
     */
    function buscaContas(string $company_id = '', array $params = []) :array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/accounts', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra uma nova conta no NFHub
     */
    public function cadastraConta(int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível cadastrar uma conta sem o ID da empresa", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível cadastrar uma conta sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {
            $dados = $this->post('accounts', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Atualiza uma conta existente no NFHub
     */
    public function atualizaConta(int $id, int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível atualizar uma conta sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível atualizar uma conta sem o ID da conta", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível atualizar uma conta sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {
            $dados = $this->put('accounts/'.$id, $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Atualiza uma conta existente no NFHub
     */
    public function consultaConta(int $id, int $company_id, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível consultar uma conta sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível consultar uma conta sem o ID da conta", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('accounts/'.$id, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Deleta uma conta existente no NFHub
     */
    public function deletaConta(int $id, int $company_id, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível deletar uma conta sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível deletar uma conta sem o ID da conta", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->delete('accounts/'.$id, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Busca todos os convênios
     */
    public function buscaConvenios(string $company_id = '', int $account_id = 0, array $params = []): array
    {
        if (empty($account_id)) {
            throw new Exception("Não é possível buscar os convênios sem o ID da conta", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/accounts/'.$account_id.'/convenants', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cadastra um novo convênio no NFHub
     */
    public function cadastraConvenio(int $company_id, int $account_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível cadastrar o convênio sem o ID da empresa", 1);
        }

        if (empty($account_id)) {
            throw new Exception("Não é possível cadastrar o convênio sem o ID da conta", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível cadastrar o convênio sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {

            $dados = $this->post('/accounts/'.$account_id.'/convenants', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Atualiza um convênio existente no NFHub
     */
    public function atualizaConvenio(int $id, int $company_id, int $account_id, array $dados, array $params = []): array
    {
        if (empty($id)) {
            throw new Exception("Não é possível atualizar o convênio sem o ID do convênio", 1);
        }

        if (empty($company_id)) {
            throw new Exception("Não é possível atualizar o convênio sem o ID da empresa", 1);
        }

        if (empty($account_id)) {
            throw new Exception("Não é possível atualizar o convênio sem o ID da conta", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível atualizar o convênio sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {

            $dados = $this->put('/accounts/'.$account_id.'/convenants/'.$id, $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Consulta um convênio existente no NFHub
     */
    public function consultaConvenio(int $id, int $company_id, int $account_id, array $params = []): array
    {
        if (empty($id)) {
            throw new Exception("Não é possível consultar o convênio sem o ID do convênio", 1);
        }

        if (empty($company_id)) {
            throw new Exception("Não é possível consultar o convênio sem o ID da empresa", 1);
        }

        if (empty($account_id)) {
            throw new Exception("Não é possível consultar o convênio sem o ID da conta", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/accounts/'.$account_id.'/convenants', $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Deletaq um convênio existente no NFHub
     */
    public function deletaConvenio(int $id, int $company_id, int $account_id, array $params = []): array
    {
        if (empty($id)) {
            throw new Exception("Não é possível deletar o convênio sem o ID do convênio", 1);
        }

        if (empty($company_id)) {
            throw new Exception("Não é possível deletar o convênio sem o ID da empresa", 1);
        }

        if (empty($account_id)) {
            throw new Exception("Não é possível deletar o convênio sem o ID da conta", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->delete('/accounts/'.$account_id.'/convenants', $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Emite um ou mais boletos em lote
     */
    public function emiteBoletos(int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível emitir um boleto sem o ID da empresa", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível emitir um boleto sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {

            $dados = $this->post('plugboleto', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Consulta as informações de um boleto específico no NFHub
     */
    public function consultaBoleto(int $id, int $company_id, array $params = []): array
    {
        if (empty($id)) {
            throw new Exception("Não é possível consultar um boleto sem seu ID", 1);
        }

        if (empty($company_id)) {
            throw new Exception("Não é possível consultar um boleto sem o ID da empresa", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'company_id';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('/installments/'.$id, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Atualiza um ou mais boletos existentes em lote
     */
    public function atualizaBoletos(int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível emitir um boleto sem o ID da empresa", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível emitir um boleto sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;

        try {

            $dados = $this->put('plugboleto', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Gera o PDF de um ou mais boletos existentes em lote
     */
    public function pdfBoletos(int $company_id, array $ids = [], array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível gera o PDF de um boleto sem o ID da empresa", 1);
        }

        if (empty($ids)) {
            throw new Exception("Não é possível gera o PDF de boletos sem o id de pelo menos um boleto", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return !in_array($item['name'], ['company_id', 'installments']);
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            if (!empty($ids)) {
                $params[] = [
                    'name' => 'installments',
                    'value' => implode(',', $ids)
                ];
            }

            $this->setDecode(false);
            $dados = $this->get('plugboleto/print', $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Descarta um ou mais boletos existentes em lote
     */
    public function descartaBoletos(int $company_id, array $ids = [], array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível descartar um boleto sem o ID da empresa", 1);
        }

        if (empty($ids)) {
            throw new Exception("Não é possível realizar o descarte de boletos sem o id de pelo menos um boleto", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return !in_array($item['name'], ['company_id', 'installments']);
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            if (!empty($ids)) {
                $params[] = [
                    'name' => 'installments',
                    'value' => implode(',', $ids)
                ];
            }

            $dados = $this->delete('plugboleto', $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Gera arquivo remessa
     */
    public function geraRemessa(int $company_id, array $dados, array $params = [])
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível gerar o arquivo remessa sem o ID da empresa", 1);
        }

        if (!isset($dados['installments']) || empty($dados['installments'])) {
            throw new Exception("Não é possível gerar o arquivo remessa sem o id de pelo menos um boleto", 1);
        }

        $dados['company_id'] = $company_id;

        try {
            $dados = $this->post('plugboleto/remittance', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Processa arquivo retorno
     */
    public function processaRetorno(int $company_id, array $dados, array $file, array $params = [])
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível processar o arquivo retorno sem o ID da empresa", 1);
        }

        if (!isset($file['path']) || empty($file['path']) || !isset($file['type']) || empty($file['type']) || !isset($file['name']) || empty($file['name'])) {
            throw new Exception("Não é possível processar o arquivo retorno sem o caminho, tipo ou nome do mesmo", 1);
        }

        $dados['company_id'] = $company_id;

        try {
            $cfile = new CURLFile($file['path'], $file['type'], $file['name']);
            $dados['return'] = $cfile;

            $this->setUpload(true);
            $dados = $this->post('plugboleto/return', $dados, $params);

            if ($dados['httpCode'] == 200) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            foreach ($dados['body']->errors as $key => $error) {
                if (strpos($key, 'position') !== false) {
                    $errors[] = implode('; ', $error);
                } else {
                    $errors[] = $error;
                }
            }

            throw new Exception("\r\n".implode("\r\n", $errors), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }
}
