<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\User\UserFilterRequest;
use App\Services\Panel\User\UserService;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(UserFilterRequest $request): Response
    {
        $users = $this->userService->getPaginated(
            $request->validated(),
            $request->integer('per_page', 15)
        );

        $statistics = $this->userService->getStatistics();

        return Inertia::render('panel/Users/Index', [
            'users' => $users,
            'statistics' => $statistics,
            'filters' => $request->validated(),
            'statuses' => collect(UserStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'userTypes' => collect(UserType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }
}
