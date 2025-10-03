<?php

namespace App\Services\Admin;

use App\Models\PaymentGateway;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;

class PaymentGatewayService
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    public function create(array $attributes): PaymentGateway
    {
        return $this->connection->transaction(function () use ($attributes) {
            $configs = Arr::pull($attributes, 'configs', []);

            $gateway = PaymentGateway::query()->create([
                'name' => Arr::get($attributes, 'name'),
                'code' => Arr::get($attributes, 'code'),
                'description' => Arr::get($attributes, 'description'),
                'is_active' => (bool) Arr::get($attributes, 'is_active', false),
            ]);

            $this->syncConfigs($gateway, $configs);

            return $gateway->load('configs');
        });
    }

    public function update(PaymentGateway $gateway, array $attributes): PaymentGateway
    {
        return $this->connection->transaction(function () use ($gateway, $attributes) {
            $configs = Arr::pull($attributes, 'configs', []);

            $gateway->update([
                'name' => Arr::get($attributes, 'name', $gateway->name),
                'code' => Arr::get($attributes, 'code', $gateway->code),
                'description' => Arr::get($attributes, 'description'),
                'is_active' => (bool) Arr::get($attributes, 'is_active', false),
            ]);

            $this->syncConfigs($gateway, $configs);

            return $gateway->load('configs');
        });
    }

    public function syncConfigs(PaymentGateway $gateway, array $configPayload): void
    {
        $idsToKeep = [];

        foreach ($configPayload as $payload) {
            $keyName = Arr::get($payload, 'key_name');
            $environment = Arr::get($payload, 'environment');

            if ($keyName === null || $environment === null) {
                continue;
            }

            $data = [
                'key_name' => $keyName,
                'key_value' => Arr::get($payload, 'key_value'),
                'environment' => $environment,
                'is_encrypted' => (bool) Arr::get($payload, 'is_encrypted', false),
            ];

            $configId = Arr::get($payload, 'id');

            if ($configId) {
                $config = $gateway->configs()->whereKey($configId)->first();

                if ($config) {
                    $config->fill($data);
                    $config->save();
                    $idsToKeep[] = $config->id;
                    continue;
                }
            }

            $config = $gateway->configs()->create($data);
            $idsToKeep[] = $config->id;
        }

        if ($idsToKeep === []) {
            $gateway->configs()->delete();

            return;
        }

        $gateway->configs()->whereNotIn('id', $idsToKeep)->delete();
    }
}
