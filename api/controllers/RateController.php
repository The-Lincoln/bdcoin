<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;

class RateController {
    public function index(Request $request, Response $response): void {
        global $crypto_assets;

        $assets = [];
        foreach ($crypto_assets as $key => $asset) {
            $assets[] = [
                'symbol' => $asset['symbol'],
                'name' => $asset['name'],
                'icon' => $asset['icon'],
                'color' => $asset['color'],
                'rate_usd' => $asset['rate'],
                'rate_formatted' => '$' . number_format($asset['rate'], 2),
            ];
        }

        $response->success($assets);
    }

    public function show(Request $request, Response $response): void {
        global $crypto_assets;
        $symbol = strtolower($request->param('symbol'));

        if (!isset($crypto_assets[$symbol])) {
            $response->error("Rate not found for symbol: $symbol", 404);
        }

        $asset = $crypto_assets[$symbol];
        $response->success([
            'symbol' => $asset['symbol'],
            'name' => $asset['name'],
            'rate_usd' => $asset['rate'],
            'rate_formatted' => '$' . number_format($asset['rate'], 2),
            'updated_at' => date('c'),
        ]);
    }
}
