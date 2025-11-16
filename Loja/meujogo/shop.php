<?php
/*
 * Servidor da Loja para o Jogo
 * Autor: Guilherme Pereira Da Costa
 * * VERSÃO 2.0 - Suporta ações "details" e "buy"
 */

// --- Configuração ---
header('Content-Type: application/json');

// Carrega o catálogo de itens do arquivo JSON
$json_data = file_get_contents('items.json');
$items_catalog = json_decode($json_data, true); // true = como array

// --- Funções de Resposta ---
function send_json($data, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode($data);
    exit;
}

// --- Lógica Principal ---

// 1. Verificar os parâmetros obrigatórios
if (!isset($_GET['action']) || !isset($_GET['item_code'])) {
    send_json(['success' => false, 'message' => "Parâmetros 'action' e 'item_code' são obrigatórios."], 400);
}

$action = $_GET['action'];
$item_code = $_GET['item_code'];

// 2. Verificar se o item existe
if (!array_key_exists($item_code, $items_catalog)) {
    send_json(['success' => false, 'message' => 'Item não encontrado.'], 404);
}

$item = $items_catalog[$item_code];

// 3. Executar a Ação Correta

switch ($action) {
    case 'details':
        // Ação: details
        // O Unity só quer saber os dados do item (nome, preço, etc.)
        // Não precisamos de 'player_coins' aqui.
        send_json($item); // Envia diretamente os dados do item
        break;

    case 'buy':
        // Ação: buy
        // O Unity quer comprar o item. Precisamos de 'player_coins'.
        if (!isset($_GET['player_coins'])) {
            send_json(['success' => false, 'message' => "Parâmetro 'player_coins' é obrigatório para comprar."], 400);
        }
        
        $player_coins = (int)$_GET['player_coins'];
        $item_price = $item['price'];

        // 4. A lógica de compra (igual à anterior)
        if ($player_coins >= $item_price) {
            // Sim! O jogador pode comprar.
            send_json([
                'success' => true,
                'item' => $item // Envia o item comprado de volta
            ]);
        } else {
            // Não! O jogador não tem moedas suficientes.
            send_json([
                'success' => false,
                'message' => 'Não há moedas suficientes.'
            ], 400); // 400 = Bad Request
        }
        break;

    default:
        // Ação desconhecida
        send_json(['success' => false, 'message' => "Ação desconhecida. Use 'details' ou 'buy'."], 400);
        break;
}

?>