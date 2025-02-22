<?php

use Flarum\Extend;
use Neiderruiz\SimpleShareSession\AuthMiddleware;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'), 
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    (new Extend\Middleware('forum'))
        ->add(AuthMiddleware::class)
];