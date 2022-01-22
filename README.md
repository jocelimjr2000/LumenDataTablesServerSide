# Lumen - DataTables.js ServerSide

## Register

```php
# Open file bootstrap/app.php

$app->register(JocelimJr\LumenDTSS\Providers\LumenDTSSServiceProvider::class);
```

## Basic usage

```php
<?php

namespace App\Http\Controllers;

use JocelimJr\LumenDTSS\Interfaces\DTSSRepositoryInterface;
use App\Models\User;

class UserController extends Controller
{
    private DTSSRepositoryInterface $dtssRepository;
    
    public function __construct(DTSSRepositoryInterface $dtssRepository)
    {
        $this->dtssRepository = $dtssRepository;
    }

    public function findAll(Request $request)
    {
        $columns = [
            0 => [
                'name' => 'id',
                'searchable' => false
            ],
            1 => [
                'name' => 'firstname',
                'searchable' => true
            ],
            2 => [
                'name' => 'lastname',
                'searchable' => true
            ],
        ];

        $json_data = $this->dtssRepository->simple($request, User::class, $columns);

        return response()->json($json_data, 200);
    }
}
```