# Lumen - DataTables.js ServerSide

## Register

```php
# Open file bootstrap/app.php

$app->register(JocelimJr\LumenDTSS\Providers\LumenDTSSServiceProvider::class);
```

## Basic usage (using Model::Class)

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


## Query Builder

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

        $user = User::join('phones', 'user.id', '=', 'phones.userId')
                        ->where('active', true);

        $json_data = $this->dtssRepository->simple($request, $user, $columns);

        return response()->json($json_data, 200);
    }
}
```