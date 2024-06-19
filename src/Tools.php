<?php
namespace NFHub\CoraBoleto;

use Exception;
use NFHub\Common\Tools as ToolsBase;
use CURLFile;

/**
 * Classe Tools
 *
 * Classe responsável pela implementação com a API de boletos do Banco Cora
 *
 * @category  NFHub
 * @package   NFHub\CoraBoleto\Tools
 * @author    Call Seven <callseven at gmail dot com>
 * @copyright 2024 Fuganholi Sistemas - NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools extends ToolsBase
{
    /**
     * Busca os tokens da conta que foi dada a autorização através da API Cora de uma empresa no NFHub
     * @param int $account_id ID da conta
     * @param string $code Código de autorização
     * @param int $company_id ID da empresa no nfhub
     * @param array $params Parametros da requisição
     */
    public function getTokens(int $account_id, int $nfhub_account_id,string $code, int $company_id, array $params = []): array
    {
        try {
            $params = array_filter($params, function($item) {
                return $item['name'] !== 'code';
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($account_id)) {
                $params[] = [
                    'name' => 'account_id',
                    'value' => $account_id
                ];
            }
            if (!empty($nfhub_account_id)) {
                $params[] = [
                    'name' => 'nfhub_account_id',
                    'value' => $nfhub_account_id
                ];
            }
            if (!empty($code)) {
                $params[] = [
                    'name' => 'code',
                    'value' => $code
                ];
            }
            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            $dados = $this->get('coraboleto/tokens', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Visualize os dados da conta que foi dada a autorização através da API Cora de uma empresa no NFHub
     * @param string $company_id ID da empresa no nfhub
     * @param array $params Parametros da requisição
     */
    function buscaDadosConta(string $company_id = '', array $params = []) :array
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

            $dados = $this->get('coraboleto/accounts', $params);

            if (!isset($dados['body']->message)) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Emite boleto da conta que foi dada a autorização através da API Cora de uma empresa no NFHub
     *
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

            $dados = $this->post('coraboleto/invoices', $dados, $params);

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
     * Consulta as informações de um boleto específico no NFHub para o banco Cora
     */
    public function consultaBoletoNfhub(int $id, int $company_id, array $params = []): array
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

            $dados = $this->get('installments/'.$id, $params);

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
     * Consulta boletos de um período específico do banco Cora
     */
    public function consultaBoletosCora(int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível consultar os boletos sem o ID da empresa", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível consultar um boleto sem nenhuma informação", 1);
        }

        $params = array_filter($params, function($item) {
            return $item['name'] !== 'company_id';
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($company_id)) {
            $params[] = [
                'name' => 'company_id',
                'value' => $company_id
            ];
        }

        $params = array_merge($params, $dados);

        try {
            $dados = $this->get('coraboleto/invoices/', $params);

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
     * Consulta as informações de um boleto específico no NFHub para o banco Cora
     * @param int $id ID do boleto no nfhub
     * @param int $company_id ID da empresa no nfhub
     */
    public function consultaBoletoCora(int $id, int $company_id, array $params = []): array
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

            $dados = $this->get('coraboleto/invoices/'.$id, $params);

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
     * Consulta saldo de uma conta no Banco Cora
     *
     * @param int $company_id ID da empresa no nfhub
     */
    public function consultaSaldo(int $company_id, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível consultar saldo sem o ID da empresa", 1);
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

            $dados = $this->get('coraboleto/balance', $params);

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
     * Atualiza um boleto específico no NFHub para o banco Cora
     * Se houver boleto será deletado e criado um novo (não exite atualização)
     * @param int $id ID do boleto no nfhub
     * @param int $company_id ID da empresa no nfhub
     */
    public function atualizaCobranca(int $id, int $company_id, array $dados, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível emitir um boleto sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível emitir um boleto sem o ID do boleto", 1);
        }

        if (empty($dados)) {
            throw new Exception("Não é possível emitir um boleto sem nenhuma informação", 1);
        }

        $dados['company_id'] = $company_id;
        $dados['installment_hub_id'] = $id;

        try {

            $dados = $this->put('coraboleto', $dados, $params);

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
     * Deleta um boleto específico no NFHub para o banco Cora
     * @param int $id ID do boleto no nfhub
     * @param int $company_id ID da empresa no nfhub
     *
     */
    public function cancelaBoleto(int $id, int $company_id, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível descartar um boleto sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível realizar o descarte de boletos sem o id de pelo menos um boleto", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return !in_array($item['name'], ['company_id']);
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($company_id)) {
                $params[] = [
                    'name' => 'company_id',
                    'value' => $company_id
                ];
            }

            if (!empty($id)) {
                $params[] = [
                    'name' => 'installment_hub_id',
                    'value' => $id
                ];
            }

            $dados = $this->delete('coraboleto', $params);

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
     * Retorna o extrato  do Banco Cora
     *
     */
    public function buscaExtrato(int $company_id, array $params = [])
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível buscar o extrato sem o ID da empresa", 1);
        }
        $params = array_filter($params, function($item) {
            return !in_array($item['name'], ['company_id']);
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($company_id)) {
            $params[] = [
                'name' => 'company_id',
                'value' => $company_id
            ];
        }

        try {
            $dados = $this->get('coraboleto/extract', $params);

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
     * Paga um boleto específico em stage (somente para testes) no NFHub para o banco Cora
     * @param int $id ID do boleto no nfhub
     * @param int $company_id ID da empresa no nfhub
     * @param string $idempotencyKey Chave de identificação
     *
     */
    public function pagarBoletoStage(int $id, int $company_id, int $nfhub_account_id, string $idempotencyKey, array $params = []): array
    {
        if (empty($company_id)) {
            throw new Exception("Não é possível pagar um boleto sem o ID da empresa", 1);
        }

        if (empty($id)) {
            throw new Exception("Não é possível pagar um boleto sem o id de pelo menos um boleto", 1);
        }

        try {
            $params = array_filter($params, function($item) {
                return !in_array($item['name'], ['company_id']);
            }, ARRAY_FILTER_USE_BOTH);

            $dados['company_id'] = $company_id;
            $dados['installment_hub_id'] = $id;
            $dados['nfhub_account_id'] = $nfhub_account_id;
            $dados['idempotencyKey'] = $idempotencyKey;

            $dados = $this->post('coraboleto/pay', $dados, $params);

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
     * Edição de notificações de um boleto
     */
    public function editaNotificacoesBoleto(int $company_id, array $ids = [], array $params = []): array
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

            $dados = $this->put('coraboleto/notifications', $params);

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
