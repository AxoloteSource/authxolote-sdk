<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Throwable;

class ReorderMenu extends AuthxoloteBase
{
    /**
     * @param  array<int, array{id: string, parent_id: string|null, sort_order: int}>  $items
     */
    public function __construct(
        protected string $menuId,
        protected array $items
    ) {
        parent::__construct('/api/v1/menu-items/reorder');
    }

    /**
     * Persiste el nuevo orden de los ítems del menú.
     *
     * @return bool True cuando el servicio guardó el orden correctamente.
     */
    public function run(): bool
    {
        if (Authxolote::isFake()) {
            $response = Authxolote::getFakeResponse(self::class, $this->fakeResponse());

            if (data_get($response, 'status') === 'error') {
                $this->error = [
                    'status' => 'error',
                    'data' => $response['data'] ?? [],
                    'message' => $response['message'] ?? __('Error reordering the menu'),
                ];

                return false;
            }

            return true;
        }

        try {
            $response = $this->put([
                'menu_id' => $this->menuId,
                'items' => $this->items,
            ]);
        } catch (Throwable) {
            return false;
        }

        return $response->successful();
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'reordered' => count($this->items),
            ],
        ];
    }
}
